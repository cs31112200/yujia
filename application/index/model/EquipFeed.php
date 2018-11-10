<?php
namespace app\index\model;

use app\admin\model\Base;

class EquipFeed extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*
 * 
 */
    public function __my_before_insert(&$data) {
        

        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data) {
        
        

        $data['update_time']=time();
        
        return true;
    }
    

    
}