<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'name',
        'type_accumulation',
        'shop_id',
        'description',
        'is_selected',
    ];
}
