<?php
namespace Service\Common;

class RedisService{
        private $redisModel = null;
		
		function __construct($host,$port,$passwd,$dbnum=0) {
			$redis = new \Redis();
			$redis->connect($host, $port);
			$redis->auth($passwd);
			$redis->select($dbnum);
			$this->redisModel = $redis;
		}
		
		function get($key){
			return $this->redisModel->get($key);
		}
		
		function keys($pattern){
			return $this->redisModel->keys($pattern);
		}
		
		function set($key,$value,$expire = 0){
			if($expire >0)
				$this->redisModel->set($key,$value,$expire);
			else
				$this->redisModel->set($key,$value);
		}
		
		function del($key){
			return $this->redisModel->delete($key);
		}
		
		function clean(){
			$this->redisModel->flushDb();
		}
    
}