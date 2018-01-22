<?php

namespace BrooksYang\LaravelApiHelper\Traits;

trait ResponseHelper
{
    /**
     * 接口返回数据格式
     *
     * @param array  $data
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse
     */
    public function jsonResponse($data = null, $msg = '')
    {
        return response()->json([
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 接口错误信息返回
     *
     * @param array $error
     * @param string $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($error = [], $data = null)
    {
        $error['data'] = $data;

        return response()->json($error);
    }

    /**
     * 接口返回数据格式
     *
     * @param string $msg
     * @param array  $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function msgResponse($msg = '', $data = null)
    {
        return response()->json([
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
        ]);
    }
}
