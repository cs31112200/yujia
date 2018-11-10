<?php
namespace app\admin\model;

use app\admin\model\Base;

class PlatformFlow extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*插入流水
 * 
 */
    public function addFlow($object_id,$total_fee,$content,$type,$ope_type){
        $time =time();
        $data=[
            'object_id'=>$object_id,
            'total_fee'=>$total_fee,
            'content'=>$content,
            'type'=>$type,
            'op_type'=>$ope_type,
            'flow_date'=>date('Y-m-d',$time),
            'op_year'=>date('Y',$time),
            'op_month'=>date('m',$time),
            'create_time'=>$time,
        ];
        return $this->insert($data);
    }
    
    
    
}