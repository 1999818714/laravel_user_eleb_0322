<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    //去除验证
    protected $except = [
        //
        'api/register',//注册
        'api/login',//登录
        'api/editAddress',//修改地址
        'api/addAddress',//添加地址
        'api/addCart',//添加购物车
        'api/addOrder',//添加订单
        'api/forgetPassword',//忘记密码接口
        'api/changePassword',//修改密码接口
    ];
}
