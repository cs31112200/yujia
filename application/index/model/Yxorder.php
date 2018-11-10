<?php
namespace app\index\model;

use app\admin\model\Base;

class Yxorder extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


    public function getDuty(){
        $duty = sysC('AGENT_DUTY');
        $duty = explode(',', $duty);
        $return=[];
        if(!empty($duty)){
            foreach($duty as $k=>$v){
                $return[$v]=$v;
            }
        }
        return $return;
    }
    
    public function getSelect($param=null) {
        $map =[];

//        if(isset($param['province'])){
//            $map['province']=$param['province'];
//        }
//        if(isset($param['city'])){
//            $map['city']=$param['city'];
//        }
//        if(isset($param['area'])){
//            $map['area']=$param['area'];
//        }
        
        if(isset($param['name'])){
            $map['name']=['like',"%".$param['name']."%"];
        }
        
        if(isset($param['contact'])){
            $map['contact']=$param['contact'];
        }
        if(isset($param['status'])){
            $map['status']=$param['status'];
        }

        return $map;
    }
    
    public function getStatus(){
        return [
            0=>'未处理',
            1=>'已处理'
        ];
    }
    
}