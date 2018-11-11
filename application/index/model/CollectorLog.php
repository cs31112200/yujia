<?php
namespace app\index\model;

use app\admin\model\Base;

class CollectorLog extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

/*
 * @pram type 1：15分钟2，30分钟,3,小时 4天（即按月）5月
 */
    public function query_log($equip_code,$num,$member_id,$type,$the_time=""){
        $data['member_id']=$member_id;
        $data['equip_code']=$equip_code;
        $data['num']=$num;
        switch($type){
            case 1:
                $data['the_day']=!empty($the_time)?$the_time:date('Y-m-d',time());
                break;
            case 2:
                 $data['the_day']=!empty($the_time)?$the_time:date('Y-m-d',time());
                break;
            case 3:
                if(!checkTimeFormat($the_time, 'Y-m-d')){
                    $this->setError('您的参数有误');
                    return false;
                }
                $data['the_day']=$the_time;
                break;
            case 4:
                if(!checkTimeFormat($the_time, 'Y-m')){
                    $this->setError('您的参数有误');
                    return false;
                }
                $data['month']=$the_time;
                break;
            case 5:
                if(!checkTimeFormat($the_time, 'Y')){
                    $this->setError('您的参数有误');
                    return false;
                }
                $data['year']=$the_time;
                break;
            default:
                $this->setError('您的参数有误');
                return false;
                break;
        }

        $result =db($this->getTheTable())->where($data)->select();
        $return1=$return2=$return3=[];$i=0;
        if(!empty($result)){
            $have1=$hav2=[];
            foreach($result as $k=>$v){
                if($type==1){
                    $i =$v['mini'];
                    $h =$v['hour'];
                    $tims =$h.":".$i;
                    $fit =intval($i/15);
                    $return1[$h."_".$fit]['time']=$tims;
                    $return1[$h."_".$fit]['value']=sprintf("%.2f",$v['temperature']);
                    $return2[$h."_".$fit]['time']=$tims;
                    $return2[$h."_".$fit]['value']=sprintf("%.2f",$v['ph']);
                    $return3[$h."_".$fit]['time']=$tims;
                    $return3[$h."_".$fit]['value']=sprintf("%.2f",$v['oxygen']);
                }else if($type==2){
                    $i =$v['mini'];
                    $h =$v['hour'];
                    $tims =$h.":".$i;
                    $fit =intval($i/30);
                    $return1[$h."_".$fit]['time']=$tims;
                    $return1[$h."_".$fit]['value']=sprintf("%.2f",$v['temperature']);
                    $return2[$h."_".$fit]['time']=$tims;
                    $return2[$h."_".$fit]['value']=sprintf("%.2f",$v['ph']);
                    $return3[$h."_".$fit]['time']=$tims;
                    $return3[$h."_".$fit]['value']=sprintf("%.2f",$v['oxygen']);
                }else if($type==3){
                     $h =$v['hour'];
                    $return1[$h]['time']=$v['hour'];
                    $return1[$h]['value']=sprintf("%.2f",$v['temperature']);
                    $return2[$h]['time']=$v['hour'];
                    $return2[$h]['value']=sprintf("%.2f",$v['ph']);
                    $return3[$h]['time']=$v['hour'];
                    $return3[$h]['value']=sprintf("%.2f",$v['oxygen']);
                    $i++;
                }else if($type==4){
                    $h =date('d',strtotime($v['the_day']));
                    $return1[$v['the_day']]['time']=$h;
                    $return1[$v['the_day']]['value']=sprintf("%.2f",$v['temperature']);
                    $return2[$v['the_day']]['time']=$h;
                    $return2[$v['the_day']]['value']=sprintf("%.2f",$v['ph']);
                    $return3[$v['the_day']]['time']=$h;
                    $return3[$v['the_day']]['value']=sprintf("%.2f",$v['oxygen']);
                }else if($type==5){
                    $h =date('m',strtotime($v['the_day']));
                    $return1[$v['month']]['time']=$h;
                    $return1[$v['month']]['value']=sprintf("%.2f",$v['temperature']);
                    $return2[$v['month']]['time']=$h;
                    $return2[$v['month']]['value']=sprintf("%.2f",$v['ph']);
                    $return3[$v['month']]['time']=$h;
                    $return3[$v['month']]['value']=sprintf("%.2f",$v['oxygen']);
                }
            }
            $return1= !empty($return1)?array_values($return1):[];
            $return2= !empty($return2)?array_values($return2):[];
            $return3= !empty($return3)?array_values($return3):[];
        }
        return ['temperature'=>$return1,'ph'=>$return2,'oxygen'=>$return3];
    }
}