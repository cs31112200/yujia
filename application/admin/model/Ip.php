<?php
namespace app\admin\model;

use app\admin\model\Base;

class Ip extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*ip
 * 
 */
    
    public function addIp(){
        $ip['ip_char']= get_client_ip();
        $ip['ip_int']= get_client_ip(1);
        $ip['url']='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $ip['create_time']=time();
        return $this->insert($ip);
    }
    
    public function checkIp(){
        $data['ip_int']=  get_client_ip(1);
        $today =strtotime(date('Y-m-d',time()));
        $data['create_time']=['egt',$today];
        $count =db($this->getTheTable())->where($data)->count();
     //   echo $count;exit;
        if($count>=5){
            return false;
        }else{
            return true;
        }
    }
}