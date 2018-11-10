<?php
namespace app\index\model;

use app\admin\model\Base;

class EquipProject extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*
 * 
 */
    public function __my_before_insert(&$data) {
        
        if(!checkTimeFormat($data['start_time'],'Y-m-d')){
            $this->setError('起始时间不正确');
            return false;
        }
        
        $data['start_time'] =strtotime($data['start_time']);
        if(isset($data['end_time'])){
            if(!checkTimeFormat($data['end_time'],'Y-m-d')){
                $this->setError('截至时间不正确');
                return false;
            }
            $data['end_time']=strtotime($data['end_time']);
            
            if($data['end_time']<=$data['start_time']){
                $this->setError('截至时间必须大于起始时间');
                return false;
            }
            
        }
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data) {
        
        
        if(!checkTimeFormat($data['start_time'],'Y-m-d')){
            $this->setError('起始时间不正确');
            return false;
        }
       
        $data['start_time'] =strtotime($data['start_time']);
        if(isset($data['end_time'])){
            if(!checkTimeFormat($data['end_time'],'Y-m-d')){
                $this->setError('截至时间不正确');
                return false;
            }
            $data['end_time']=strtotime($data['end_time']);
            
            if($data['end_time']<=$data['start_time']){
                $this->setError('截至时间必须大于起始时间');
                return false;
            }
        }
        
        //检验该id 是否匹配
        
        $check_result =db($this->getTheTable())->find($data['id']);
        if(empty($check_result) || $check_result['member_id']!=$data['member_id'] || $check_result['equip_id']!=$data['equip_id'] ){
            $this->setError('非法操作');
            return false;
        }
        
        $data['update_time']=time();
        
        return true;
    }
    

    
}