<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;

trait GuzzleHelper
{
    /**
     * Guzzle 请求
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
            return ['error' => 'GUZZLE_EXCEPTION', 'msg' => $exception->getMessage()];
        }

        // 返回数据
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Guzzle Multipart 请求
     *
     * @param       $method
     * @param       $url
     * @param array $param
     * @return mixed
     */
    public function sendMultipartRequest($method, $url, $param = [])
    {
        // 获取token
        $token = session('tokenForApiDoc');

        $multipart = [];
        foreach ($param as $key => $item) {
            $data = ['name' => $key, 'contents' => $item];
            if (is_file($item)) {
                $data = ['name' => $key, 'contents' => fopen($item, 'r')];
            }
            array_push($multipart, $data);
        }

        // 发送请求
        $client = new Client(['headers' => ['Authorization' => "Bearer $token"]]);
        try {
            $response = $client->request($method, $url, ['multipart' => $multipart]);
        } catch (RequestException $exception) {
            return ['error' => 'GUZZLE_EXCEPTION', 'msg' => $exception->getMessage()];
        }

        // 返回数据
        return json_decode((string)$response->getBody(), true);
    }
}
