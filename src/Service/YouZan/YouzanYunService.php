<?php
namespace Service\YouZan;
require_once __DIR__ . "/../Common/RedisService.php";
use Exception;
use Youzan\Open\Token;
use Service\Common\RedisService;
/**
    有赞云 获取Token主程序
**/
class YouzanYunService
{
    protected $config = [];

    public function __construct()
    {
        $youzan_config = include(__DIR__ ."/../../Config/youzan_config.php");
        $this->config = [
            'type' => 'self',
            'kdtId' =>  $youzan_config["youzan"]["kdtId"],
            'clientId' => $youzan_config["youzan"]["clientId"],
            'clientSecret' => $youzan_config["youzan"]["clientSecret"],
            'api' => [
                'version' => '3.0.0',
                'getTrade' => 'youzan.trade.get',
                'createPayQRCode' => 'youzan.pay.qrcode.create',
            ],
        ];
    }

    public function getAccessToken()
    {
        global $redis;
        if($redis){
                $token = $redis->get("yztoken");
        }else{
            //针对回调通知的请求，创建redis链接
            $redis_config = include(__DIR__ ."/../../Config/redis_config.php");
            $redis = new RedisService($redis_config["redis"]["host"],$redis_config["redis"]["port"],$redis_config["redis"]["password"],$redis_config["redis"]["db"]);
            $token = $redis->get("yztoken");
        }

        if($token){
            return $token;
        }else{
            $clientId = $this->config['clientId'];
            $clientSecret = $this->config['clientSecret'];
            $type = $this->config['type'];
            $keys = [
                'kdt_id' => $this->config['kdtId']
            ];

            $accessToken = (new Token($clientId, $clientSecret))->getToken($type, $keys);
            if (!isset($accessToken['access_token'])) throw new Exception('wrong server pay config');
            //保存redis
            $redis->set("yztoken",$accessToken['access_token'],$accessToken['expires_in']);
            return $accessToken['access_token'];
        }
    }
}
