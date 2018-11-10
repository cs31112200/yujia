<?php
namespace app\index\model;

use app\admin\model\Base;

class Clock extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*
 * 
 */
    public function __my_before_insert(&$data) {
        
        
        if($data['is_open']!=1 && $data['is_open']!=2){
            $this->setError('操作标识有误');
            return false;
        }
        
        $area_result =db('MemberAera')->find($data['area_id']);

        if(empty($area_result)){
            $this->setError('增氧机id有误');
            return false;
        }
        if($area_result['member_id']!=$data['member_id']){
            $this->setError('非法操作');
            return false;
        }
 
        //判断时间
        $thetime =strtotime($data['open_time']);
        $now =time();
        if($thetime-600<=$now){
            $this->setError('操作时间必须大于当前时间10分钟以上');
            return false;
        }
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data) {
        
        
        
        if($data['is_open']!=1 && $data['is_open']!=2){
            $this->setError('操作标识有误');
            return false;
        }
        
        $area_result =db('MemberAera')->find($data['area_id']);

        if(empty($area_result)){
            $this->setError('增氧机id有误');
            return false;
        }
        if($area_result['member_id']!=$data['member_id']){
            $this->setError('非法操作');
            return false;
        }
 
        //判断时间
        $thetime =strtotime($data['open_time']);
        $now =time();
        if($thetime-600<=$now){
            $this->setError('操作时间必须大于当前时间10分钟以上');
            return false;
        }
        $data['create_time']=time();
        
        return true;
    }
    
/*获取用户clock
 * 
 */    
    public function getMemberClock($area_id){
        $data['area_id']=$area_id;
        $data['status']=1;
        $result =db($this->getTheTable())->where($data)->select();
        return empty($result)?null:$result;
    }
    
}