<?php
require_once __DIR__ . "/../vendor/autoload.php";
use Workerman\Worker;
use Xhat\Payjs\Payjs;

//加载配置文件
$config = [];
$configExtraPath = __DIR__ ."/Config/";
$configdir = scandir($configExtraPath);
foreach($configdir as $value){
    if($value == "." || $value == "..") continue;
    $config = array_merge($config,include $configExtraPath.$value);
}

Worker::$daemonize = false;
$worker = new Worker($config["main_server"], $config["context_option"]);
$worker->count =1;
$worker->name="PayServer";
$worker->transport = "ssl";
$worker->userConnections = [];
$worker->userQRs = [];//有赞使用的

$worker->onWorkerStart = function () use ($worker) {
    global $config;
    //定义有赞内部text通讯协议
    $textWorker = new Worker($config["youzan_inner_server"]);
    $textWorker->onMessage = "Service\\YouZan\\InnerTextServer::onMessage";
    $textWorker->listen();
    
    //定义payjs内部text通讯协议
    $textWorker2 = new Worker($config["payjs_inner_server"]);
    $textWorker2->onMessage = "Service\\PayJS\\InnerTextServer::onMessage";
    $textWorker2->listen();
    
    //初始化Payjs对象
    global $payjs;
    $payjs = new Payjs($config["payjs"]);
    
    //初始化redis
    global $redis;
    $redis = new Service\Common\RedisService($config["redis"]["host"],$config["redis"]["port"],$config["redis"]["password"],$config["redis"]["db"]);
    
};
$worker->onMessage = function ($connection, $message) use ($worker) {
     global $config;
    $data = json_decode($message,true);
    if(!isset($data["ticket"]) || !isset($data["randstr"])){
        $connection->send(json_encode(["code" => 500, "msg" => "Error Param", "event" => "check", "data" => []]));
        return;
    }
    //腾讯007验证码-服务器端校验
    $param007=[
        "aid"=>$config["007"]["appid"],
        "AppSecretKey"=>$config["007"]["appkey"],
        "Ticket"=>$data["ticket"],
        "Randstr"=>$data["randstr"],
        "UserIP"=>"127.0.0.1"
    ];
    $url = "https://ssl.captcha.qq.com/ticket/verify?".http_build_query($param007);
    $result = file_get_contents($url);
    $resultArr = json_decode($result,true);
    if($resultArr["response"] != 1){
        $connection->send(json_encode(["code" => 500, "msg" => $resultArr["err_msg"], "event" => "check", "data" => []]));
        return;
    }
    
    //保存用户id和connection之间的关系
    if (!isset($connection->userId)) {
        $connection->userId = $data["userid"];
        $worker->userConnections[$connection->userId] = $connection;
    }
    //支付商类型
    $type = $data["service"];
    if($type=="youzan"){
        (new Service\YouZan\PayService)->create($connection, $data["price"], $data["desc"]);
    }else if($type=="payjs"){
        (new Service\PayJS\PayService)->create($connection, $data["price"], $data["desc"]);
    }else{
        $connection->send(json_encode(["code" => 500, "msg" =>"Error Pay Type", "event" => "Create", "data" => []]));
    }
};

Worker::runAll();
