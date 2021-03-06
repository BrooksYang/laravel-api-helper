<?php

namespace BrooksYang\LaravelApiHelper\Controllers\Doc;

use BrooksYang\LaravelApiHelper\Facades\Doc;
use BrooksYang\LaravelApiHelper\Traits\DocHelper;
use BrooksYang\LaravelApiHelper\Traits\RequestHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Arr;

class DocController extends Controller
{
    use DocHelper, RequestHelper;

    /**
     * api 列表
     *
     * @param $group
     * @param $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($group = '', $module = '')
    {
        $items = Doc::api($group, $module);

        // 获取上次压测结果
        $prefix = config('api-helper.cache_tag_prefix');
        foreach ($items as &$item) {
            $key = $item['method'] . '_' . str_replace('/', '_', $item['uri']);
            $item['last_server_test_result'] = Cache::tags($prefix . '_server_test')->get($key);
        }

        return view('api_helper::index', compact('items'));
    }

    /**
     * 获取api详情
     *
     * @param $group
     * @param $module
     * @param $api
     * @return mixed
     */
    public function show($group, $module, $api)
    {
        $api = json_decode(base64_decode($api));

        $routes = $this->getRoutes();
        $route = Arr::first($routes, function ($item) use ($api) {
            return in_array("$api->controller@$api->action", explode(':', $item));
        });

        $info = $this->getApiInfo($route);
        $params = $this->getApiParams($api->controller, $api->action);

        // 是否开启压力测试
        $pressureTest = config('api-helper.api_pressure_test', false) === true;

        // 获取上次压测结果
        $prefix = config('api-helper.cache_tag_prefix');
        $key = $info['method'] . '_' . str_replace('/', '_', $info['uri']);
        $lastServerTestResult = $pressureTest ? Cache::tags($prefix . '_server_test')->get($key) : null;

        return view('api_helper::show', compact('info', 'params', 'group', 'module', 'pressureTest', 'lastServerTestResult'));
    }

    /**
     * 发送请求
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function send(Request $request)
    {
        $method = $request->input('methodForApiDoc');
        $baseUrl = trim(config('api-helper.api_base_url'), '/') ?: url('/');

        $uri = $request->input('uriForApiDoc');
        $uri = $this->handleUri($request, $uri);
        $url = "{$baseUrl}/{$uri}";

        $params = $request->except('_token', 'tokenForApiDoc', 'methodForApiDoc', 'uriForApiDoc', 'token', 'total_requests', 'concurrency');
        $token = $request->input('tokenForApiDoc');
        $request->session()->put('tokenForApiDoc', $token);

        // 发送请求
        $response = $this->sendRequest($method, $url, $params);

        // 压力测试
        $pressureTest = $this->serverTest($request, $params, $method, $url, $token);

        return back()
            ->with('api_helper.response', $response)
            ->with('api_helper.pressure_test', $pressureTest)
            ->withInput();
    }

    /**
     * Handle Uri for Rest Api
     *
     * @param Request $request
     * @param         $uri
     * @return string
     */
    private function handleUri(Request $request, $uri)
    {
        if (strpos($uri, '{') === false || strpos($uri, '}') === false) {
            return $uri;
        }

        $items = explode('/', $uri);

        foreach ($items as &$item) {
            if (strpos($item, '{') !== false && strpos($item, '}') !== false) {
                $item = $request->input($item);
            }
        }

        $uri = implode('/', $items);

        return $uri;
    }

    /**
     * 服务器压力测试
     *
     * @param Request $request
     * @param         $params
     * @param         $method
     * @param         $url
     * @param         $token
     * @return array
     */
    private function serverTest(Request $request, $params, $method, $url, $token)
    {
        // 判断是否填写了压力测试参数
        if (!$request->has('total_requests') || !$request->has('concurrency')) {
            $command = '';
            return compact('command');
        }

        $totalRequests = $request->input('total_requests') ?: 100;
        $concurrency = $request->input('concurrency') ?: 10;
        $token = $token ? "-H 'Authorization:Bearer $token' " : '';

        // GET请求url
        $url = $this->getFullUrl($url, $method, $params);

        // POST请求参数
        $postParam = $this->getPostParam($method, $params);

        // 执行压力测试
        $command = "ab -n $totalRequests -c $concurrency {$postParam}{$token}$url";

        // 判断命令是否合法
        if (escapeshellcmd($token) != $token) {
            $report = '存在非法字符，已中断测试！';
            return compact('command', 'report', 'postParam');
        }

        exec($command, $report);

        // 缓存测试结果
        if (!empty($report)) {
            $this->cacheReport($report, $method);
            $report = implode("\n", $report);
        }

        // 返回测试结果
        $errorMessage = "压力测试模块（ApacheBench）未安装，请执行以下命令安装：\n\napt-get install apache2-utils";
        $report = $report ?: $errorMessage;

        return compact('command', 'report', 'postParam');
    }

    /**
     * 缓存压测结果
     *
     * @param         $report
     * @param         $method
     */
    private function cacheReport($report, $method)
    {
        $prefix = config('api-helper.cache_tag_prefix');
        $key = $method . '_' . str_replace('/', '_', Input::get('uriForApiDoc'));

        // 提取吞吐率信息所在行数
        $line = 0;
        foreach ($report as $k => $item) {
            $arr = explode(':', $item);
            if (@$arr[0] == 'Requests per second') $line = $k;
        }

        // 判断是否提取到吞吐率信息所在行数
        if (!$line) return;

        // 提取关键信息
        preg_match('/(\d+)\.(\d+)/', @$report[$line], $requestsPerSecond);
        preg_match('/(\d+)\.(\d+)/', @$report[$line + 1], $timePerRequest);
        preg_match('/(\d+)\.(\d+)/', @$report[$line + 2], $timePerRequestConcurrent);

        $result = [
            'requests_per_second'         => @$requestsPerSecond[0],
            'time_per_request'            => @$timePerRequest[0],
            'time_per_request_concurrent' => @$timePerRequestConcurrent[0],
        ];

        Cache::tags($prefix . '_server_test')->forever($key, $result);
    }

    /**
     * 拼接GET方式url
     *
     * @param $url
     * @param $method
     * @param $params
     * @return string
     */
    private function getFullUrl($url, $method, $params)
    {
        if ($method == 'GET') {
            // 拼接参数
            $getParam = '';
            foreach ($params as $key => $param) {
                $getParam .= "$key=$param&";
            }
            $url = $getParam ? $url . '?' . rtrim($getParam, '&') : $url;
        }

        return $url;
    }

    /**
     * 获取POST请求参数
     *
     * @param $method
     * @param $params
     * @return string
     */
    private function getPostParam($method, $params)
    {
        $postParam = '';
        if ($method == 'POST') {
            unset($params['tokenForApiDoc']);
            $jsonParam = json_encode($params);
            $filename = storage_path('post_data.txt');
            File::put($filename, $jsonParam);
            $postParam = "-p $filename -T 'application/json' ";
        }

        return $postParam;
    }
}
