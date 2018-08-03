<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//添加接口分组
Route::prefix('api')->group(function (){
    Route::get('/shops','JsonsController@shops');//添加商家接口分组
    Route::get('/business','JsonsController@business');//获得指定商家接口
    Route::get('/sms','JsonsController@sms');//短信验证码接口
    Route::post('/register','JsonsController@register');//注册接口
    Route::any('/login','JsonsController@login');//登录验证接口
    Route::get('/addressList','JsonsController@addressList');//地址列表接口
    Route::get('/address','JsonsController@address');//指定地址接口,回显
    Route::post('/editAddress','JsonsController@editAddress');//保存修改地址接口,修改功能
    Route::post('/addAddress','JsonsController@addAddress');//保存新增地址接口,添加功能
    Route::post('/addCart','JsonsController@addCart');//保存购物车接口
    Route::get('/cart','JsonsController@cart');//获取购物车数据接口
    Route::get('/orderList','JsonsController@orderList');//获得指定订单接口
    Route::post('/addOrder','JsonsController@addOrder');//获取购物车数据接口
    Route::get('/order','JsonsController@order');//获得指定订单接口
    Route::post('/forgetPassword','JsonsController@forgetPassword');//忘记密码接口
    Route::post('/changePassword','JsonsController@changePassword');//修改密码接口
});









