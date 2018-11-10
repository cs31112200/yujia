<?php
namespace app\admin\model;

use app\admin\model\Base;

class Aerator extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        return $map;
    }


    public function __formatList($list = null) {

        return $list;
    }
    public function __formatEdit($data = null) {
        return $data;
    }
    
    
/*批量新增增氧机
 * 
 */    
   public function BatchaddAreator($equip_id,$num){
       
   } 
    
    
/*获取最后一条插入id +1
 * 
 */    
    public function getTheLastIds(){
        $id =cache('aerator_insert_id');
        
        if(empty($id)){
            $result =db($this->getTheTable())->where(1)->order('id desc')->find();
            $id =empty($result)?0:$result['id'];
        }
        return $id+1;
    }
}