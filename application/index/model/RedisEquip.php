<?php
namespace app\index\model;

use app\admin\model\Base;

class RedisEquip extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*获取设备信息
 * @param string equip_code 设备号
 * @param object $redis redis对象
 * @return mix $result  返回设备的信息
 */

    public function get_equip_msg($equip_code,$redis=""){
        $redis =empty($redis)?initRedis():$redis;
        $result =$redis->get($equip_code.'_detail');
        return empty($result)?[]:json_decode($result,true);
    }
    
/*新增/修改设备信息
 * @param string equip_code 设备号
 * @param array $msg_arr  设备详细信息
 [
    'name'=>'设备名',
    'user_name'=>'用户名称',
    'warm_phone'=>'报警电话',
    'spare_phone'=>'备用电话',
    'jpush_id'=>'推送id',
    'end_time'=>'到期时间',
 ];
 * 
 * @return mix $result  返回设备的信息
 */    
    public function set_equip_msg($equip_code,$msg_arr,$redis=""){
        if(empty($msg_arr)){
            return ;
        }
        
        $redis =empty($redis)?initRedis():$redis;
        $result =$this->get_equip_msg($equip_code,$redis);
        if(empty($result)){
            $name =(isset($msg_arr['name']) && !empty($msg_arr['name']))?$msg_arr['name']:"";
            $warm_phone =(isset($msg_arr['warm_phone']) && !empty($msg_arr['warm_phone']))?$msg_arr['warm_phone']:"";
            $spare_phone =(isset($msg_arr['spare_phone']) && !empty($msg_arr['spare_phone']))?$msg_arr['spare_phone']:"";
            $jpush_id =(isset($msg_arr['jpush_id']) && !empty($msg_arr['jpush_id']))?$msg_arr['jpush_id']:"";
            $end_time =(isset($msg_arr['end_time']) && !empty($msg_arr['end_time']))?$msg_arr['end_time']:"";
        }else{
            $spare_phone =isset($result['spare_phone'])?$result['spare_phone']:"";
            $name =(isset($msg_arr['name']) && !empty($msg_arr['name']))?$msg_arr['name']:$result['name'];
         //   print_r($result);print_r($msg_arr);exit;
            $warm_phone =(isset($msg_arr['warm_phone']) && !empty($msg_arr['warm_phone']))?$msg_arr['warm_phone']:$result['warm_phone'];
            $spare_phone =(isset($msg_arr['spare_phone']) && !empty($msg_arr['spare_phone']))?$msg_arr['spare_phone']:$spare_phone;
            $jpush_id =(isset($msg_arr['jpush_id']) && !empty($msg_arr['jpush_id']))?$msg_arr['jpush_id']:$result['jpush_id'];
            $end_time =(isset($msg_arr['end_time']) && !empty($msg_arr['end_time']))?$msg_arr['end_time']:$result['end_time'];
        }
        $save_arr=[
          'name'=>$name,  
          'user_name'=>'用户',  
          'warm_phone'=>$warm_phone,  
          'spare_phone'=>$spare_phone,  
          'jpush_id'=>$jpush_id,  
          'end_time'=>$end_time,  
        ];
        $redis->set($equip_code.'_detail',json_encode($save_arr));
        return true;
    }
    
/*清除redis
 * 
 */    
    public function clear_msg($equip_code,$redis=""){
        $redis =empty($redis)?initRedis():$redis;
        $redis->delete($equip_code.'_detail');
        return true;
    }
 
    
    
    
    
}