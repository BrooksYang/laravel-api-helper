<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use Illuminate\Http\Request;

trait RequestHelper
{
    /**
     * Send Request.
     *
     * @param $method
     * @param $url
     * @param array $params
     *
     * @throws \Exception
     *
     * @return array
     */
    public function sendRequest($method, $url, $params = [])
    {
        // 获取token
        $token = session('tokenForApiDoc');

        // 判断是否包含文件
        $files = [];
        foreach ($params as $key => $item) {
            if (is_file($item)) {
                $files[$key] = $item;
                unset($params[$key]);
            }
        }

        $request = Request::create($url, $method, $params, [], $files);
        $request->headers->set('Authorization', "Bearer $token");
        $request->headers->set('Accept', 'application/json');
        $response = app()->handle($request);

        $status = $response->getStatusCode();
        $content = $response->getContent();

        return compact('status', 'content');
    }
}
