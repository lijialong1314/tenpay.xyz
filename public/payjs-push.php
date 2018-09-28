<?php
require_once __DIR__ .'/../vendor/autoload.php';
$payjs_config = include(__DIR__ .'/../src/Config/payjs_config.php');
$system_config = include(__DIR__ .'/../src/Config/system_config.php');

use Xhat\Payjs\Payjs;

$payjs = new Payjs($payjs_config["payjs"]);
//检验签名
$data = $payjs->notify();
if($data === "验签失败"){
    echo "error";
    exit();
}

if($data['return_code'] == 1){
    $client = stream_socket_client($system_config["payjs_inner_tcpserver"]);
    $data = [
            'payjs_order_id' => $data["payjs_order_id"], 
            'total_fee' => $data['total_fee'],
            'user_id'=>$data['attach']
    ];
    fwrite($client, json_encode($data) . "\n");
    fread($client, 8192);
    fclose($client);
    // 4.返回 success 字符串（http状态码为200）
    return 'success';
}