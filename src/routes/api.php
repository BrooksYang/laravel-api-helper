<?php

/*
|--------------------------------------------------------------------------
| ApiDocGenerator Routes
|--------------------------------------------------------------------------
|
| Here is the ApiDocGenerator routes for your application.
|
*/

Route::group(['prefix' => 'api', 'middleware' => ['web'], 'namespace' => 'BrooksYang\LaravelApiHelper\Controllers\Doc'], function () {

    // api主页
    Route::get('docs/{group?}/{module?}', 'DocController@index');

    // api详情
    Route::get('docs/{group}/{module}/{api}', 'DocController@show');

    // api请求测试
    Route::post('send', 'DocController@send');
});
