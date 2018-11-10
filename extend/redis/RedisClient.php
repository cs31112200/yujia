<?php
/**
 * This is a Redis class 单台服务器
 */
include_once "Base.php";

class RedisClient extends MRedis
{
    protected  $redis;
    
/*初始化
 * 
 */
    public function __construct($conf){
            $this->redis=MRedis::getInstance($conf);
        }

/*返回redis对象
 * 
 */        
    public function GetSet(){
        return $this->redis->GetRedis();
    }
    
    public  function __call($name, $args){
            $cache = $this->GetSet();
            try {
                $ret = call_user_func_array(array($cache, $name), $args);
            }catch(\Exception $e){
                echo $e->getMessage();exit;
            }
            return $ret;
    }
    
    
}
?>
