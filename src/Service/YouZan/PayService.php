<?php
namespace Service\YouZan;
require_once "YouzanYunService.php";

use Service\YouZan\YouzanYunService;
use Exception;
use Youzan\Open\Client;

class PayService extends YouzanYunService
{

    public function create($connection, $price, $description)
    {
        global $worker;
        global $redis;
        
        $price = floatval($price) != 0 ? floatval($price) : 0.01;
        $price2 = $price * 100;
        $description = $description ? $description : '测试';

        try {
            $response = $this->createPayQRCode($this->getAccessToken(), $price2, $description);
            
            $connection->send(json_encode(['code' => 200, 'msg' => 'success', 'event' => 'create', 'data' => ['qr' => $response['qr_code']]]));
            
            //暂存redis
            $response["user_id"] = $connection->userId;
            $redis->set("youzan:order:".$response['qr_id'],json_encode($response),300);

            
        } catch (Exception $e) {
            $connection->send(json_encode(['code' => 1000, 'msg' => $e->getMessage(), 'event' => 'create', 'data' => []]));
        }
    }


    private function createPayQRCode($accessToken, $price = 1, $description = '')
    {
        $apiVersion = $this->config['api']['version'];
        $createPayQRCode = $this->config['api']['createPayQRCode'];

        $params = [
            'qr_price' => $price,
            'qr_name' => $description,
            'qr_type' => 'QR_TYPE_DYNAMIC',
        ];

        $response = (new Client($accessToken))->get($createPayQRCode, $apiVersion, $params);
        if (!isset($response['response']['qr_code'])) throw new Exception('wrong create pay qrcode');
        return $response['response'];
    }
}
