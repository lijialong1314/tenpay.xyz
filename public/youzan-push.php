<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Service/YouZan/TradeService.php';
$youzan_config = include(__DIR__ .'/../src/Config/youzan_config.php');
$system_config = include(__DIR__ .'/../src/Config/system_config.php');

$client_id = $youzan_config["youzan"]["clientId"];
$client_secret = $youzan_config["youzan"]["clientSecret"];

$request = file_get_contents('php://input');
$jsonArr = json_decode($request, true);

//判断是否是test请求
if($jsonArr['test'] == true){
    echo json_encode(['code' => 0, 'msg' => 'success']);
    exit();
}

// 判断消息是否合法，若合法则返回成功标识
$msg = $jsonArr['msg'];
$sign_string = $client_id."".$msg."".$client_secret;
$sign = md5($sign_string);
if($sign != $jsonArr['sign']){
    exit("非法请求");
}else{
    echo json_encode(['code' => 0, 'msg' => 'success']);
}

//保存request内容
$date = date("Y_m_d");
file_put_contents("request_IJkdHNs_$date.log", $request . PHP_EOL, FILE_APPEND);

//根据回调过来的信息中的订单编号，去获取对应的qr_id
$QRId = (new Service\YouZan\TradeService)->getQRId($jsonArr['id']);
if (empty($QRId)) return "send QRId is empty";

//发给内部的text协议，根据qr_id查询状态，并发送给客户端
$client = stream_socket_client($system_config["youzan_inner_tcpserver"]);
$data = array('qrId' => $QRId, 'msg' => $jsonArr['status']);
fwrite($client, json_encode($data) . "\n");
$res = fread($client, 8192);

file_put_contents('pay_dhYhdfmn.log', 'QRID:' . (string)$QRId . '--' . 'pushRes:' . $res . PHP_EOL, FILE_APPEND);
