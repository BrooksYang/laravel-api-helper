<?php

Route::group(['prefix' => 'api', 'middleware' => ['web'], 'namespace' => 'BrooksYang\LaravelApiHelper\Controllers'], function () {

    // api主页
    Route::get('docs/{module?}', 'DocController@index');

    // api详情
    Route::get('docs/{module}/{api}', 'DocController@show');

    // api请求测试
    Route::post('send', 'DocController@send');
});
