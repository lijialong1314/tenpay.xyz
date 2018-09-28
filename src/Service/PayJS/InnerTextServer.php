<?php
namespace Service\PayJS;
class InnerTextServer
{

    public static function onMessage($connection, $message)
    {
        global $worker;
        global $redis;
        
        $data = json_decode($message, true);
        $orderid = $data["payjs_order_id"];
        
        $order = $redis->get("payjs:order:$orderid");
        if($order){
            $order = json_decode($order,true);
            
            if($order["total_fee"] != $data["total_fee"]){
                $json = json_encode(['code' => 500, 'msg' => "支付金额校验错误！", 'event' => 'pay', 'data' => []]);
            }else if($order["user_id"] != $data["user_id"]){
                $json = json_encode(['code' => 500, 'msg' => "用户信息校验错误！", 'event' => 'pay', 'data' => []]);
            }else{
                $connection->send("ok");//这里先立即返回给内部TCP请求数据，payjs要求3s内响应
                $json = json_encode(['code' => 200, 'msg' => 'success', 'event' => 'pay', 'data' => "TRADE_SUCCESS"]);
            }
            //发送给前端
            $userConnection = $worker->userConnections[$order["user_id"]];
            $userConnection->send($json);
           
           //删除redis中的订单信息
           $redis->del("payjs:order:$orderid");
        }else{
            //说明当前推送已经重复，删掉了，不做处理
            $connection->send("ok");//这里先返回给内部TCP请求数据，payjs要求3s内响应
        }
    }
}
