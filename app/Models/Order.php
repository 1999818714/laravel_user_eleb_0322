<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //过滤，只有这里的才能修改
    protected $fillable = [
      'user_id',
      'shop_id',
      'sn',
      'province',
      'city',
      'county',
      'address',
      'tel',
      'name',
      'total' ,
      'status',
      'out_trade_no',
    ];

    //建立和订单商品的关系 一对多（反向）   一（多）对一   articles.author_id ---> students.id
    public function getOrderGoods()
    {
        return $this->belongsTo(OrderGoods::class,'id','order_id');
    }

}
