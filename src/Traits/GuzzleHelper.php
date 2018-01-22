<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;

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
        try {
            $response = $client->request($method, $url, ['json' => $param, 'verify' => false]);
        } catch (RequestException $exception) {
            return ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        }

        // 返回数据
        return json_decode((string)$response->getBody(), true);
    }
}
