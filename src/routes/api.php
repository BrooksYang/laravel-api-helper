<?php

/*
|--------------------------------------------------------------------------
| ApiDocGenerator Routes
|--------------------------------------------------------------------------
|
| Here is the ApiDocGenerator routes for your application.
|
*/

Route::group(['prefix' => 'api', 'middleware' => ['web'], 'namespace' => 'BrooksYang\LaravelApiHelper\Controllers'], function () {

    // api主页
    Route::get('docs/{group?}/{module?}', 'Doc\DocController@index');

    // api详情
    Route::get('docs/{group}/{module}/{api}', 'Doc\DocController@show');

    // api请求测试
    Route::post('send', 'Doc\DocController@send');

    // request counter
    Route::get('widgets/request-counter', 'BuiltIn\Widgets\WidgetController@counter');
});
