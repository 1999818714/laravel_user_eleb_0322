<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shops extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'shop_category_id',
        'shop_name',
        'shop_img',
        'shop_rating',
        'brand',
        'on_time',
        'fengniao',
        'bao',
        'piao',
        'zhun',
        'start_send',
        'send_cost',
        'notice',
        'discount',
        'status'
    ];

//    //建立和商家分类的关系 一对多（反向）   一（多）对一   articles.author_id ---> students.id
//    public function getShopCategoey()
//    {
//        return $this->belongsTo(ShopCategory::class,'shop_category_id','id');
//    }
}
