<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'goods_name',
        'rating',
        'shop_id',
        'category_id',
        'goods_price',
        'description',
        'month_sales',
        'rating_count',
        'tips',
        'satisfy_count',
        'satisfy_rate',
        'goods_img',
        'status',
    ];





}
