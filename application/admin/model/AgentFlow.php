<?php
namespace app\admin\model;

use app\admin\model\Base;

class AgentFlow extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*插入流水
 * 
 */
    public function addFlow($agent_id,$object_id,$total_fee,$content,$type){
        $time =time();
        $data=[
            'agent_id'=>$agent_id,
            'total_fee'=>$total_fee,
            'object_id'=>$object_id,
            'content'=>$content,
            'type'=>$type,
            'flow_date'=>date('Y-m-d',$time),
            'op_year'=>date('Y',$time),
            'op_month'=>date('m',$time),
            'create_time'=>time(),
        ];
        return $this->insert($data);
    }
    
    
    
}