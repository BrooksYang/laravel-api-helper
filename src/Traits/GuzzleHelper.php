<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

trait GuzzleHelper
{
    /**
     * Guzzle 请求
     *
     * @param       $method
     * @param       $url
     * @param array $param
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest($method, $url, $param = [])
    {
        // 获取token
        $token = session('tokenForApiDoc');

        // 发送请求
        $client = new Client(['headers' => ['Authorization' => "Bearer $token"]]);

        $status = 200;
        $message = 'Success';

        try {
            $response = $client->request($method, $url, ['json' => $param, 'verify' => false]);
        } catch (RequestException $exception) {
            return [
                'status'  => $exception->getCode(),
                'message' => $exception->getResponse()->getBody()->getContents(),
                'data'    => null,
            ];
        }

        // 返回数据
        $data = (string) $response->getBody();

        return compact('status', 'message', 'data');
    }

    /**
     * Guzzle Multipart 请求
     *
     * @param       $method
     * @param       $url
     * @param array $param
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
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

        $status = 200;
        $message = 'Success';

        try {
            $response = $client->request($method, $url, ['multipart' => $multipart]);
        } catch (RequestException $exception) {
            return [
                'status'  => $exception->getCode(),
                'message' => $exception->getResponse()->getBody()->getContents(),
                'data'    => null,
            ];
        }

        // 返回数据
        $data = (string)$response->getBody();

        return compact('status', 'message', 'data');
    }
}
