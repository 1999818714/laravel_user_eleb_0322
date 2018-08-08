<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Member;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\Shops;
use App\Models\Users;
use App\SignatureHelper;
use App\SphinxClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mockery\Exception;

class JsonsController extends Controller
{
    //获得商家列表接口
    //businessList:'/businessList.php',
    /**
     * "id": "s10001",
     * "shop_name": "上沙麦当劳",
     * "shop_img": "http://www.homework.com/images/shop-logo.png",
     * "shop_rating": 4.7,评分
     * "brand": true,是否是品牌
     * "on_time": true,是否准时送达
     * "fengniao": true,是否蜂鸟配送
     * "bao": true,是否保标记
     * "piao": true,是否票标记
     * "zhun": true,是否准标记
     * "start_send": 20,起送金额
     * "send_cost": 5,配送费
     * "distance": 637,距离
     * "estimate_time": 30,预计时间
     * "notice": "新店开张，优惠大酬宾！",店公告
     * "discount": "新用户有巨额优惠！"优惠信息
     */

    //商家列表接口
    public function shops(Request $request)
    {
//        $businessList = Shops::select('id', 'shop_name', 'shop_img', 'shop_rating', 'brand', 'on_time', 'fengniao', 'bao', 'piao', 'zhun', 'start_send', 'send_cost', 'notice', 'discount')->get();
        $keyword = '';
        if($request->keyword){
//            dd(1);
            $keyword = $request->keyword;
            $businessList = Shops::where('shop_name','like','%'.$keyword.'%')->get();//包含功能分页搜索
//            dd($businessList);
        }else{
            $businessList = Shops::get();//包含功能分页
        }
        foreach ($businessList as $val) {
            $val['distance'] = rand(2, 20);
            $val['estimate_time'] = 10;
        }
        return json_encode($businessList);//返回json字符串
    }


    // 获得指定商家接口
    public function business(Request $request)
    {
        $business = Shops::where('id', $request->id)->first();//获取评价
        $business['foods_code'] = rand(1, 5);// 食物总评分
        $business['high_or_low'] = true;// 低于还是高于周边商家
        $business['h_l_percent'] = rand(1, 5);// 低于还是高于周边商家的百分比
        $business['distance'] = rand(10, 1000);
        $business['estimate_time'] = rand(1, 5);//预计时间
        $business['service_code'] = rand(2, 20);//评分
        $business['evaluate'] = [[
            "user_id" => 233,
            "username" => "刘******9",
            "user_img" => "http://admin.eleb.net/storage/img/WFbMJyzdzBT4vm5CwryucjW9H6hhg1gDYTM2pm7T.jpeg",
            "time" => "2066-6-66",
            "evaluate_code" => 1,
            "send_time" => 30,
            "evaluate_details" => "非常好吃"
        ]];
        $commodity = [];
        //获取商品分类和商品
        $menuCategory = MenuCategory::where('shop_id', $request->id)->get();
        foreach ($menuCategory as &$cate) {
            //获取指定分类和商家的所有菜品
            $menus = Menu::where([['shop_id', $request->id], ['category_id', $cate->id]])->get();
            foreach ($menus as &$menu) {
                $menu['goods_id'] = $menu['id'];
                unset($menu['id']);
            }
//            $cate['goods_list'] = $menus;
            $commodity[] = [
                "description" => $cate['description'],
                "is_selected" => $cate['is_selected'],
                "name" => $cate['name'],
                "type_accumulation" => $cate['type_accumulation'],
                "goods_list" => $menus
            ];

        }
//        dd($commodity);
        $business['commodity'] = $commodity;
        return json_encode($business);
    }

//注册接口
    public function sms(Request $request)
    {
        $tel = $request->tel;
//        dd($tel);
//        $tel = request()->input('tel',17313227001);
        $params = [];//array ()

        // *** 需用户填写部分 ***
        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIL5wjp1WnjiUc";
        $accessKeySecret = "BnM8dA7p6YeBL5TnsOzPdmctM307CQ";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "刘鹏666";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_61465004";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $num = random_int(1000, 9999);

        Redis::set('sms'.$tel, $num);//保存到rides
        Redis::expire('sms'.$tel, 3600*24);//设置保存时间
//        dd(11);
        $params['TemplateParam'] = Array(
            "code" => $num,//验证码
//        "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

//        dd(1);
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
//        return $content;
//        dd($content);
        return [
            "status"=>"true",
             "message"=>"获取短信验证码成功"
        ];
    }

    //注册接口
    public function register(Request $request)
    {
        //验证数据自动验证//手动验证
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:members',
            'password' => 'required|min:5',
            'tel' => 'required|unique:members',
        ], [
            'username.unique' => '管理员名已存在',
            'username.required' => '管理员名不能为空',
            'password.unique' => '密码最少5位',
            'password.required' => '密码不能为空',
            'tel.unique' => '该手机号已被注册',
            'tel.required' => '手机号不能为空',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        //判断验证码是否正确
        if ($request->sms != Redis::get('sms'.$request->tel)) {
            return [
                "status" => "false",
                "message" => "验证码错误"
            ];
        }
        $memeber = Member::create([
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'tel' => $request->tel,
        ]);
        return [
            "status" => "true",
            "message" => "注册成功"
        ];
    }

    //登录接口
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'password'=>'required',
        ],[
            'name.required'=>'用户名不能为空',
            'password.required'=>'密码不能为空'
        ]);
        if($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first(),
            ];
        }
        if (Auth::attempt([//认证
            'username' => $request->name,
            'password' => $request->password,
        ])
        ) {//认证通过
            return [
                "status" => "true",
                "message" => "登录成功",
                "user_id" => Auth::user()->id,
                "username" => Auth::user()->username
            ];
        } else {
            return [
                "status" => "false",
                "message" => "登录失败",
            ];
        }
    }

    //地址列表接口
    public function addressList()
    {
        //获取所有地址数据
//        $address = Address::select('id','province','city','county','address','tel','name')->get();
        $address = Address::where('user_id',Auth::user()->id)->get();
        $new_address = [];
        foreach ($address as $v){
            $new_address[] =[
                  "id"=>$v->id,
                  "provence"=> $v->province,
                  "city"=>$v->city,
                  "area"=>$v->county,
                  "detail_address"=>$v->address,
                  "name"=>$v->name,
                  "tel"=>$v->tel
            ];
        }
        return json_encode($new_address);//转换为json字符串
    }

    //指定地址接口//回显
    public function address(Request $request)
    {
        //获取指定修改的一条数据
        $address = Address::where('id',$request->id)->first();
        $edit_address =[
            "id"=>$address->id,
            "provence"=> $address->province,
            "city"=>$address->city,
            "area"=>$address->county,
            "detail_address"=>$address->address,
            "name"=>$address->name,
            "tel"=>$address->tel
        ];
        return json_encode($edit_address);
    }

    //保存修改地址接口,修改功能
    public function editAddress(Request $request)
    {
        //获取修改的记录
        $address = Address::find($request->id);
//        $address = Address::where('id',$request->id)->first();
//        验证数据，手动验证
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'tel'=>['required',Rule::unique('addresses')->ignore($address->id)],
//            'name'=>'required|unique:addresses',
//            'tel'=>'required|unique:addresses',
            'provence'=>'required',
            'city'=>'required',
            'area'=>'required',
            'detail_address'=>'required',
        ],[
            'name.required'=>'收货人不能为空',
            'tel.required'=>'联系方式不能为空',
            'tel.unique'=>'联系方式已存在',
            'provence.required'=>'省不能为空',
            'city.required'=>'市不能为空',
            'area.required'=>'区不能为空',
            'detail_address.required'=>'详细地址不能为空',
        ]);
        if($validator->fails()){
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        //保存修改数据
        $address->update([
            'name'=>$request->name,
            'tel'=>$request->tel,
            'province'=>$request->provence,
            'city'=>$request->city,
            'county'=>$request->area,
            'address'=>$request->detail_address,
        ]);
        return [
            "status"=>"true",
            "message"=>"修改成功"
        ];
    }

    //保存新增地址接口
    public function addAddress(Request $request)
    {
        //验证数据，手动验证
        $validator = Validator::make($request->all(),[
            'name'=>'required',
            'tel'=>'required|unique:addresses',
            'provence'=>'required',
            'city'=>'required',
            'area'=>'required',
            'detail_address'=>'required',
        ],[
            'name.required'=>'收货人不能为空',
            'tel.required'=>'联系方式不能为空',
            'tel.unique'=>'联系方式已存在',
            'provence.required'=>'省不能为空',
            'city.required'=>'市不能为空',
            'area.required'=>'区不能为空',
            'detail_address.required'=>'详细地址不能为空',
        ]);
        if($validator->fails()){
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        //保存
        Address::create([
            'name'=>$request->name,
            'tel'=>$request->tel,
            'province'=>$request->provence,
            'city'=>$request->city,
            'county'=>$request->area,
            'address'=>$request->detail_address,
            'user_id'=>Auth::user()->id,
            'is_default'=>0,
        ]);
        return [
            "status"=>"true",
            "message"=>"添加成功"
        ];
    }

    //保存购物车接口
    public function addCart(Request $request)
    {

        //保存
        $goodList = $request->goodsList;
        $goodsCount = $request->goodsCount;
        if (empty($goodList)){
            return [
                "status"=>"false",
                "message"=>"还未选择商品"
            ];
        }
        //保存之前清空购物车
        $del_cart = Cart::where('user_id',Auth::user()->id)->get();
        foreach ($del_cart as $del){
            $del->delete();
        }

        for ($i=0;$i<count($goodList);$i++){
            //判断购物车中是否已有该菜品
            $goods_id = $goodList[$i];
            $carts = Cart::where('goods_id',$goods_id)->first();
            if(empty($carts)){//没有就添加
                Cart::create([
                    'user_id'=>Auth::user()->id,
                    'goods_id'=>$goodList[$i],
                    'amount'=>$goodsCount[$i],
                ]);
            }else{//存在就修改
                $cart = Cart::where('goods_id',$goods_id)->first();
                $cart->update([
                    'user_id'=>Auth::user()->id,
                    'goods_id'=>$goodList[$i],
                    'amount'=>$cart['amount']+$goodsCount[$i],
                ]);
            }

        }
        return [
            "status"=>"true",
            "message"=>"添加成功"
        ];
    }

    //获取购物车数据接口
    public function cart()
    {
        //获取用户的购物车所有数据
        $carts = Cart::where('user_id',Auth::user()->id)->get();
        $goods_list = [];//购物车列表显示
        $totalCost = 0;//结算
        foreach ($carts as $cart) {
            $menu = Menu::where('id',$cart->goods_id)->first();//根据ID找到该菜品
            $goods_list[] = [
                'goods_id'=>$menu->id,
                'goods_name'=>$menu->goods_name,
                'goods_img'=>$menu->goods_img,
                'amount'=>$cart->amount,
                'goods_price'=>$menu->goods_price,
            ];
            $totalCost += $cart->amount*$menu->goods_price;
        }
        $goods['goods_list'] = $goods_list;//购物车列表显示
        $goods['totalCost'] = $totalCost;//结算
        return json_encode($goods);
    }

    //添加订单接口//确认生成订单的时候将购物车清空
    public function addOrder(Request $request)
    {
        $address_id = $request->address_id;//获取地址ID
        $address = Address::where('id',$address_id)->first();//根据地址ID获取订单地址
        $carts = Cart::where('user_id',Auth::user()->id)->get();//获取购物车的商品ID
        $shop_id = 0;
        $total = 0;
        foreach ($carts as $cart){
            $shop_id = $cart->getMenu->shop_id;
            $total += $cart->amount*$cart->getMenu->goods_price;
        }

        //开启事务
        DB::beginTransaction();
        try{
            //添加订单
    //            dd($shop_id);
            $order = Order::create([
    //            'user_id'=>Auth::user()->id,
                'user_id'=>Auth::user()->id,
                'shop_id'=>$shop_id,
                'sn'=>date('Ymd',time()).uniqid(),
                'province'=>$address->province,
                'city'=>$address->city,
                'county'=>$address->county,
                'address'=>$address->address,
                'tel'=>$address->tel,
                'name'=>$address->name,
                'total'=>$total,
                'status'=>0,
                'out_trade_no'=>uniqid(),
            ]);
            //添加订单商品表
                $Goods = [];//获得发送订单的内容
                foreach ($carts as $cart){
                    $orderGoods = OrderGoods::create([
                        'order_id'=>$order->id,
                        'goods_id'=>$cart->getMenu->id,
                        'amount'=>$cart->amount,
                        'goods_name'=>$cart->getMenu->goods_name,
                        'goods_img'=>$cart->getMenu->goods_img,
                        'goods_price'=>$cart->getMenu->goods_price,
                    ]);
                    $Goods[] = $orderGoods->goods_name;
                }
                //商品拼接
                $good = implode('、',$Goods);

            //提交事务
            DB::commit();

            $tel = $address->tel;
//        dd($tel);
//        $tel = request()->input('tel',17313227001);
            $params = [];//array ()

            // *** 需用户填写部分 ***
            // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
            $accessKeyId = "LTAIL5wjp1WnjiUc";
            $accessKeySecret = "BnM8dA7p6YeBL5TnsOzPdmctM307CQ";

            // fixme 必填: 短信接收号码
            $params["PhoneNumbers"] = $tel;

            // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
            $params["SignName"] = "刘鹏666";

            // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
            $params["TemplateCode"] = "SMS_141290057";

            // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
//        dd(11);
            $name = '商城点的:'.$good;
            $params['TemplateParam'] = Array(
                "name" => $name,//返回商品信息
//        "product" => "阿里通信"
            );


            // fixme 可选: 设置发送短信流水号
            $params['OutId'] = "12345";

            // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
            $params['SmsUpExtendCode'] = "1234567";


            // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
            if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
                $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
            }

//        dd(1);
            // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
            $helper = new SignatureHelper();

            // 此处可能会抛出异常，注意catch
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            // fixme 选填: 启用https
            // ,true
            );
//        return $content;
//        dd($content);

            //发送邮箱
            $email = Users::where('shop_id',$order->shop_id)->first();
            $_GET['email'] = $email->email;
//            return $_GET['email'];
            \Illuminate\Support\Facades\Mail::send('welcome', [], function ($message) {//welcome是视图
                $message->from('17313227001@163.com','liu');//邮箱，发送方姓名
                $message->to([$_GET['email']])->subject('你又有订单啦，快去看看吧！');});//接收方

//            $r = \Illuminate\Support\Facades\Mail::raw('你又有订单啦，快去看看吧！',function ($message){
//                $message->subject('订单出现！');//标题
//                $message->to($_GET['email']);//发给别人的邮箱
//                $message->from('17313227001@163.com','liu订单管理员');//邮箱，发送方姓名
//            });


            return [
                "status"=>"true",
                "message"=>"添加成功,正在发送订单信息",
                "order_id"=>$order->id
            ];
        }catch (Exception $ex){
            //回滚事务
            DB::rollback();
            return [
                "status"=>"false",
                "message"=>"添加失败",
            ];
        }
    }

    //获得指定订单接口
    public function order(Request $request)
    {
        //获取订单该用户的所有订单
        $orders = Order::where([['user_id',Auth::user()->id],['id',$request->id]])->first();
        //获取订单商品
        $orderGoods = OrderGoods::where('order_id',$orders->id)->get();
        $goods_list = [];
        foreach ($orderGoods as $good){
            $goods_list[] = [
                'goods_id'=>$good->goods_id,
                'goods_name'=>$good->goods_name,
                'goods_img'=>$good->goods_img,
                'amount'=>$good->amount,
                'goods_price'=>$good->goods_price
            ];
        }
        $order_status = '';
         if($orders->status == -1){
             $order_status = '已取消';
         }elseif ($orders->status == 0){
             $order_status = '待支付';
         }elseif ($orders->status == 1){
             $order_status = '待发货';
         }elseif ($orders->status == 2){
             $order_status = '待确认';
         }elseif ($orders->status == 3){
             $order_status = '完成';
         }
            $new_order = [
                'id'=>$orders->id,
                'order_code'=>$orders->sn,
                'order_birth_time'=>date('Y-m-d H:i',strtotime($orders->created_at)),
                'order_status'=>$order_status,
                'shop_id'=>$orders->shop_id,
                'shop_name'=>$orders->getOrderGoods->goods_name,
                'shop_img'=>$orders->getOrderGoods->goods_img,
                'goods_list'=>$goods_list,
                "order_price"=>$orders->total,
                "order_address"=>$orders->address
            ];
        return json_encode($new_order);

    }

    //获得订单列表接口
    public function orderList()
    {
        //获取订单该用户的所有订单
        $orders = Order::where('user_id',2)->get();
        $new_orders = [];
        foreach ($orders as $order){
            //获取订单商品
            $orderGoods = OrderGoods::where('order_id',$order->id)->get();
            $goods_list = [];
            foreach ($orderGoods as $good){
                $goods_list[] = [
                    'goods_id'=>$good->goods_id,
                    'goods_name'=>$good->goods_name,
                    'goods_img'=>$good->goods_img,
                    'amount'=>$good->amount,
                    'goods_price'=>$good->goods_price
                ];
            }
            $order_status = '';
            if($order->status == -1){
                $order_status = '已取消';
            }elseif ($order->status == 0){
                $order_status = '待支付';
            }elseif ($order->status == 1){
                $order_status = '待发货';
            }elseif ($order->status == 2){
                $order_status = '待确认';
            }elseif ($order->status == 3){
                $order_status = '完成';
            }
            $new_orders[] = [
                'id'=>$order->id,
                'order_code'=>$order->sn,
                'order_birth_time'=>date('Y-m-d',strtotime($order->created_at)),
                'order_status'=>$order_status,
                'shop_id'=>$order->shop_id,
                'shop_name'=>$order->getOrderGoods->goods_name,
                'shop_img'=>$order->getOrderGoods->goods_img,
                'goods_list'=>$goods_list,
                "order_price"=>$order->total,
                "order_address"=>$order->address
            ];
        }
        return json_encode($new_orders);
    }

    //忘记密码接口
    public function forgetPassword(Request $request)
    {
        //验证数据自动验证//手动验证
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:5',
            'tel' => 'required',
        ], [
            'password.unique' => '密码最少5位',
            'password.required' => '密码不能为空',
            'tel.required' => '手机号不能为空',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        //判断验证码是否正确
        if ($request->sms != Redis::get('sms'.$request->tel)) {
            return [
                "status" => "false",
                "message" => "验证码错误"
            ];
        }
        //判断两次密码是否一致

        //根据手机号找到该用户的数据
        $member = Member::where('tel',$request->tel)->first();
//        return $member;
        $member->update([
            'password' => bcrypt($request->password),
        ]);
        return [
            "status" => "true",
            "message" => "重置成功"
        ];

    }

    //修改密码接口
    public function changePassword(Request $request)
    {
        /**
         * oldPassword: 旧密码
         * newPassword: 新密码
         */
        //验证数据自动验证//手动验证
        $validator = Validator::make($request->all(), [
            'oldPassword' => 'required',
            'newPassword' => 'required|min:5',
        ], [
            'oldPassword.required' => '密码不能为空',
            'newPassword.required' => '新密码不能为空',
            'newPassword.min' => '新密码不能少于5位',
        ]);
        if ($validator->fails()) {
            return [
                "status" => "false",
                "message" => $validator->errors()->first(),
            ];
        }
        //根据ID找到该用户
        $member = Member::find(Auth::user()->id);
        $oldPassword = $request->oldPassword;
        $newPassword = $request->newPassword;
        if(Hash::check($oldPassword,$member->password)){//第一个不加密，第二个加密
            //验证成功，将填写密码加密放到数据库中
            $member->update([
                'password'=>bcrypt($newPassword),
            ]);
            return [
                "status"=>"true",
                "message"=>"修改成功"
            ];
        }else{
            return [
                "status"=>"false",
                "message"=>"旧密码不正确"
            ];
        }


    }









}
