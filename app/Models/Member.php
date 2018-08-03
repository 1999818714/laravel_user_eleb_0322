<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
class Member extends Authenticatable
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'username',
        'password',
        'tel',
    ];
}
