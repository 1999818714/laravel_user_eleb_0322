<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
        'user_id',
        'goods_id',
        'amount',
    ];



    //建立和商品的关系 一对多（反向）   一（多）对一   articles.author_id ---> students.id
    public function getMenu()
    {
        return $this->belongsTo(Menu::class,'goods_id','id');
    }

}
