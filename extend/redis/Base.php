<?php
/**
 * This is a Redis class 单台服务器
 */

class MRedis
{
	public static $instance = NULL;
        public STATIC $link ="";
	//construct:connect redis
        protected function __construct($conf){
            $this->initRedis($conf);
        }


	/**
	 * Get a instance of MyRedisClient
	 *
	 * @param string $key
	 * @return object
	 */
    static function getInstance($configs)
    {
		if (!self::$instance instanceof MRedis) {
			self::$instance = new MRedis($configs);
		}
		return self::$instance;
    }
    
    /**
     * 初始化Redis
     */
    private function initRedis($conf){
        $obj = new Redis();
        if($obj->connect($conf['host'],$conf['port'])){
                $obj->auth($conf['auth']);
                self::$link = $obj;
        }else{
            echo "redis connect fail!";exit();
        }

    }
	
/**
 * 关闭连接
 * pconnect 连接是无法关闭的
 *  
 * @param int $flag 关闭选择 0:关闭 Master 1:关闭 Slave 2:关闭所有
 * @return boolean
 */
    public function close(){
              self::$link->close(); 
    }

/*获得Redis
 * 
 */    
    public function GetRedis(){
        return self::$link;
    }
    
/*是否存在key
 * @param string $key key_name
 */
    public function rd_exists($key){
        return $this->GetRedis()->exists($key);
    }
    
/*删除key
 * @param mix $key $key
 */    
    public function rd_del($key){
        return $this->GetRedis()->del($key);
    }
    
/*查找key
 * 
 */    
    public function rd_keys($key){
        return $this->GetRedis()->keys($key);
    }
	
/*随机返回key
 * 
 */    
    public function rd_randomkey(){
        return $this->GetRedis()->randomkey();
    }
/*取得key的生存时间
 * 
 */
    public function rd_ttl($key){
        return $this->GetRedis()->ttl($key);
    }
    
/*移动key 把当前key位置移动到制定数据库位置
 * 
 */    
    public function rd_move($key,$db){
        return $this->GetRedis()->move($key,$db);
    }
    
/*重命名
  RENAME key newkey
将key改名为newkey。
当key和newkey相同或者key不存在时，返回一个错误。
当newkey已经存在时，RENAME命令将覆盖旧值。
 */ 
    public function rd_rename($key,$newkey){
        return $this->GetRedis()->RENAME($key,$newkey);
    }
    
 /*查找key所存储的类型
none(key不存在) int(0)
string(字符串) int(1)
list(列表) int(3)
set(集合) int(2)
zset(有序集) int(4)
hash(哈希表) int(5)
  */   
    public function rd_type($key){
        return $this->GetRedis()->type($key);
    }
    
 /*给指定key定生存时间
  * @param int $time
  */
    public function rd_expire($key,$time){
        return $this->GetRedis()->expire($key,$time);
    }
    
 /*给指定key定生存时间
  * @param unix $time
  */
    public function rd_expireat($key,$time){
        return $this->GetRedis()->expireat($key,$time);
    }  
    
/*移除key的生存时间
 * 
 */    
    public function rd_persist($key){
        return $this->GetRedis()->PERSIST($key);
    }  
 
}
?>
