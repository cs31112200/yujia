<?php
namespace app\admin\model;

use app\admin\model\Base;

class UpRecord extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


    public function add_record($member_id,$equip_id,$primary_id,$up_id,$up_fee,$install_fee){
        $data['member_id']=$member_id;
        $data['equip_id']=$equip_id;
        $data['primary_id']=$primary_id;
        $data['up_id']=$up_id;
        $data['up_fee']=$up_fee;
        $data['install_fee']=$install_fee;
        $data['create_time']=time();
        $this->insert($data);
        return true;
    }
}