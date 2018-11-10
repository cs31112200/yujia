<?php
namespace app\admin\model;

use app\admin\model\Base;

class EquipCollector extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

    public function getSelect($param=null) {
        $map =[];
       
        $map['member_id']=$param['member_id'];
        $map['equip_id']=$param['equip_id'];
         return $map;
    }
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['account']) && !empty($param['account'])){
            $map['account']=$param['account'];
        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
        $prex =config('database.prefix');
//
        $sql =$this->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->join($prex.'member c','a.member_id=c.id','LEFT')
                ->field('a.name,a.end_time,a.init_count,a.annual_fee,b.fee,c.name as member_name,b.product_id')
                ->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();

//        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
            $list[$k]['name']=model('CollectorType')->getFieldsValue(['id'=>$v['type_id']],'name');
            
            }
        }
        return $list;
    }
    
    
    public function get_last_number($member_id,$equip_id){
        
        $data['member_id']=$member_id;
        $data['equip_id']=$equip_id;
        
        $result =db($this->getTheTable())->where($data)->order('number desc')->find();
        return empty($result)?1:($result['number']+1);
    }
    
    public function getMemberCollector($member_id,$equip_id){
        $data=[
            'a.member_id'=>$member_id,
            'a.equip_id'=>$equip_id
        ];
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
                 ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->field('a.other_name,a.id,a.type_id,a.number,a.status,a.equip_id,b.equip_code')
                ->where($data)->select();
        
        if(!empty($result)){
            foreach($result as $k=>$v){
                $the_collect_data =$this->get_collect_data($v['equip_code'], $v['number']);
                $result[$k] = array_merge($v,$the_collect_data);
            }
        }
        
        
        return empty($result)?null:$result;
    }
    
    public function get_collect_data($equip_code,$num){
        $redis =initRedis();
        $eq_name =$equip_code."_sensor_data_history";
        $result =$redis->lrange($eq_name,0,-1);
        $back=[
                'time'=>'',
                'ph'=>0,
                'temperature'=>0,
                'oxygen'=>0
            ];
        
        if(!empty($result)){
            $results= $result[count($result)-1];
            $results =json_decode($results,true);
            foreach($results as $k=>$v){
                if($v['number']==$num){
                    $back['time']=date('m-d H:i',$v['time']);
                    $back['ph']=sprintf("%.1f",$v['PH']);
                    $back['temperature']=sprintf("%.1f",$v['waterTemperature']);
                    $back['oxygen']=sprintf("%.2f",$v['dissolvedOxygen']);
                }
            }
        }
        return $back;
        
    }

/*
 * @param type 1：15分钟2，30分钟，3，1小时 
 */    
    public function query_today_collector($equip_code,$num,$type=1){
        $redis = initRedis();
      //  $eq_name =$num.'_equip_collect_'.$equip_code;
        $eq_name =$equip_code."_sensor_data_history";
        $list =$redis->lrange($eq_name,0,-1);
     //   print_r($list);exit;
        $return1=$return2=$return3 =[];
        if(!empty($list)){
            foreach($list as $k=>$v){
                $v = json_decode($v,true);
                $the_value =$v['value'];
                $date =date('Y-m-d',$v['hour']);
                $now =date('Y-m-d',time());
                $h =date('H',$v['hour']);
                $i =date('i',$v['hour']);
                if($now ==$date){
                    switch($type){
                        case 1:
                            $fit =intval($i%15);
                            if($fit==0){
                                $return1[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return1[$h."_".$i]['value']=sprintf("%.2f",$the_value['temperature']);
                                $return2[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return2[$h."_".$i]['value']=sprintf("%.2f",$the_value['ph']);
                                $return3[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return3[$h."_".$i]['value']=sprintf("%.2f",$the_value['oxygen']);
                            }
                            break;
                        case 2:
                            $fit =intval($i%30);
                            if($fit==0){
                                $return1[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return1[$h."_".$i]['value']=sprintf("%.2f",$the_value['temperature']);
                                $return2[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return2[$h."_".$i]['value']=sprintf("%.2f",$the_value['ph']);
                                $return3[$h."_".$i]['time']=date('H:i',$v['hour']);
                                $return3[$h."_".$i]['value']=sprintf("%.2f",$the_value['oxygen']);
                            }
                            break;
                        case 3:
                            $return1[$h]['time']=date('H',$v['hour']);
                            $return1[$h]['value']=sprintf("%.2f",$the_value['temperature']);
                            $return2[$h]['time']=date('H',$v['hour']);
                            $return2[$h]['value']=sprintf("%.2f",$the_value['ph']);
                            $return3[$h]['time']=date('H',$v['hour']);
                            $return3[$h]['value']=sprintf("%.2f",$the_value['oxygen']);
                            break;
                        default:
                            break;
                    }
                }
         
            }
            $return1= !empty($return1)?array_values($return1):[];
            $return2= !empty($return2)?array_values($return2):[];
            $return3= !empty($return3)?array_values($return3):[];

        }
        return ['temperature'=>$return1,'ph'=>$return2,'oxygen'=>$return3];
    }
    
    
}