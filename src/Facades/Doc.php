<?php

namespace BrooksYang\LaravelApiHelper\Facades;

use Illuminate\Support\Facades\Facade;

class Doc extends Facade
{
    /**
     * 获取组件的注册名称。
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'doc';
    }
}
