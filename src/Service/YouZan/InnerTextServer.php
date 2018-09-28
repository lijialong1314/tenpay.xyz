<?php

namespace Service\YouZan;

class InnerTextServer
{
    /**
     * @param \Workerman\Connection\TcpConnection $connection
     * @param string $message
     */
    public static function onMessage($connection, $message)
    {
        global $redis;
        global $worker;
        
        $data = json_decode($message, true);
        $status = $data["msg"];
        
        $order = $redis->get("youzan:order:".$data['qrId']);
        if($order){
            $json = json_encode(['code' => 200, 'msg' => 'success', 'event' => 'pay', 'data' => $data['msg']]);
            $order = json_decode($order,true);
            $userId = $order["user_id"];
            
            $userConnection = $worker->userConnections[$userId];
            $userConnection->send($json);//给前端用户发送数据
            $connection->send('有赞推送处理成功');//给内部tcp协议返回数据
            if($status === "TRADE_SUCCESS" || $status === "PAID"){
                $redis->del("youzan:order:".$data['qrId']);
            }
        }else{
            $connection->send('订单已过期');
        }
    }
}
