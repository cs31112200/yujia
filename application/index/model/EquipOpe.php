<?php
namespace app\index\model;

use app\admin\model\Base;

class EquipOpe extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

/* *添加设备操作记录
 *
 *  param $type string 1添加通道2修改通道3打开4关闭通道5布防6撤防7硬件端操作
 *  
 */
    public function addOpe($member_id,$equip_id,$type,$content){
        
        $data['member_id']=$member_id;
        $data['equip_id']=$equip_id;
        $data['type']=$type;
       // $data['status']=$status;
        $data['content']=$content;
        $data['create_time']=time();
        $data['create_date']=date('Y-m-d',time());
        return $this->insert($data);
        
    }
    
    public function get_type_name(){
        return [
            '1'=>'添加/修改通道',
            '3'=>'打开/关闭通道',
            '5'=>'设备自动布防',
            '6'=>'设备自动撤防',
            '7'=>'设备手动布防',
            '8'=>'设备手动撤防',
            '9'=>'设置电流阀值',
        ];
    }
    
}