<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use GuzzleHttp\Client;

trait GuzzleHelper
{
    /**
     * guzzle 请求
     *
     * @param       $method
     * @param       $url
     * @param array $param
     * @return mixed
     */
    public function sendRequest($method, $url, $param = [])
    {
        // 获取token
        $token = session('tokenForApiDoc');

        // 发送请求
        $client = new Client(['headers' => ['Authorization' => "Bearer $token"]]);
        $response = $client->request($method, $url, ['json' => $param, 'verify' => false]);

        // 请求状态异常处理
        $code = $response->getStatusCode();
        if ($code != 200) abort($code);

        // 返回数据
        return json_decode((string)$response->getBody(), true);
    }
}
