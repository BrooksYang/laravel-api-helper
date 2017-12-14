<?php

namespace BrooksYang\LaravelApiHelper\Controllers;

use BrooksYang\LaravelApiHelper\Facades\Doc;
use BrooksYang\LaravelApiHelper\Traits\DocHelper;
use BrooksYang\LaravelApiHelper\Traits\GuzzleHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

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
        $params = $request->except('_token', 'methodForApiDoc', 'uriForApiDoc', 'token');
        $token = $request->input('tokenForApiDoc');
        $request->session()->put('tokenForApiDoc', $token);

        $data = $this->sendRequest($method, $url, $params);

        return back()->with('params', json_encode($data))->withInput();
    }
}
