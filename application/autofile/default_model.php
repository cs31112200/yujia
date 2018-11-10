<?php
namespace app\[module]\model;

use app\[module]\model\Base;

class [model_name] extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        return $map;
    }



    public function __formatEdit($data = null) {
        return $data;
    }
    
}