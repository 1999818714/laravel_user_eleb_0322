<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'name',
        'user_id',
        'tel',
        'province',
        'city',
        'county',
        'address',
        'is_default',
    ];
}
