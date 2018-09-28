<?php
namespace Service\PayJS;

use Exception;

class PayService
{
    public function create($connection, $price, $description)
    {
        global $worker;
        global $payjs;
        global $redis;
        global $config;
       
        $price = floatval($price) != 0 ? floatval($price) : 0.01;
        $price2 = $price * 100;//单位为分
        $description = $description ? $description : '测试';
        
        $ordernum = date("Ymd").$this->getMillisecond();//订单号
        $noticeurl = $config["payjs"]["notity"];
    
           $data = [
                'body' =>$description,                        // 订单标题
                'total_fee' =>$price2,                           // 订单标题
                'out_trade_no' =>$ordernum,                   // 订单号
                'attach' => $connection->userId,            // 订单附加信息(可选参数)，这里使用userid
                'notify_url' => $noticeurl,    // 异步通知地址(可选参数)
           ];
           $response = $payjs->native($data);
           if($response["return_code"] == 1){
               //发送给前端
                $connection->send(json_encode(['code' => 200, 'msg' => 'success', 'event' => 'create', 'data' => ['qr' => $response['qrcode'],"qr_url"=>$response["code_url"]]]));
                //redis里暂存订单信息
                $response["user_id"] = $connection->userId;
                $redis->set("payjs:order:".$response['payjs_order_id'],json_encode($response),300);

           }else{
               $connection->send(json_encode(['code' => 500, 'msg' => $response["msg"], 'event' => 'create', 'data' => []]));
           }
    }

    private function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
   
}
