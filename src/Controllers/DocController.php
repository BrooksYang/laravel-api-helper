<?php

namespace BrooksYang\LaravelApiHelper\Controllers;

use BrooksYang\LaravelApiHelper\Facades\Doc;
use BrooksYang\LaravelApiHelper\Traits\DocHelper;
use BrooksYang\LaravelApiHelper\Traits\GuzzleHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use File;

class DocController extends Controller
{
    use DocHelper, GuzzleHelper;

    /**
     * api 列表
     *
     * @param $module
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($module = '')
    {
        $items = Doc::api($module);

        return view('api_doc::index', compact('items'));
    }

    /**
     * 获取api详情
     *
     * @param $module
     * @param $api
     * @return mixed
     */
    public function show($module, $api)
    {
        $api = json_decode(base64_decode($api));

        $routes = $this->getRoutes();
        $route = array_first($routes, function ($item) use ($api) {
            return in_array("$api->controller@$api->action", explode(':', $item));
        });

        $info = $this->getApiInfo($route);
        $params = $this->getApiParams($api->controller, $api->action);

        return view('api_doc::show', compact('info', 'params', 'module'));
    }

    /**
     * 发送请求
     *
     * @param Request $request
     * @return array
     */
    public function send(Request $request)
    {
        $method = $request->input('methodForApiDoc');
        $baseUrl = config('api-helper.api_base_url') ?: str_replace($request->path(), '', $request->url());
        $url = $baseUrl . $request->input('uriForApiDoc');
        $params = $request->except('_token', 'methodForApiDoc', 'uriForApiDoc', 'token', 'total_requests', 'concurrency');
        $token = $request->input('tokenForApiDoc');
        $request->session()->put('tokenForApiDoc', $token);

        // 发送请求
        $data = $this->sendRequest($method, $url, $params);

        // 压力测试
        $response = $this->serverTest($request, $params, $method, $url, $token);

        return back()->with('params', json_encode($data))
            ->with('response', $response)
            ->withInput();
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
        if ($request->has('total_requests') && $request->has('concurrency')) {
            $totalRequests = $request->input('total_requests') ?: 100;
            $concurrency = $request->input('concurrency') ?: 10;

            $token = $token ? "-H 'Authorization:Bearer $token' " : '';

            // GET请求url
            $url = $this->getFullUrl($url, $method, $params);

            // POST请求参数
            $postParam = $this->getPostParam($method, $params);

            // 执行压力测试
            $command = "ab -n $totalRequests -c $concurrency {$postParam}{$token}$url";
            exec($command, $report);

            // 判断是否返回测试结果
            $errorMessage = "压力测试模块（ApacheBench）未安装，请执行以下命令安装：\n\napt-get install apache2-utils";
            $report = $report ? implode("\n", $report) : $errorMessage;
        }

        return compact('command', 'report', 'jsonParam');
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
            $url = $url . rtrim($getParam, '&');
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
