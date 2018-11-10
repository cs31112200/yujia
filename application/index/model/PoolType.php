<?php
namespace app\Index\model;

use app\admin\model\Base;

class PoolType extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



    
    
    
    public function getPooltype(){
        $result =db($this->getTheTable())->where('status=1')->select();
        return $result;
    }
    
}