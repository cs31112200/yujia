<?php
namespace app\index\model;

use app\admin\model\Base;

class MemberEquip extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

   
/*关联设备
 * 
 */
    public function saveMemberEquip($member_id,$equip_id,$name,$pool_type,$water_name,$warm_phone,$spare_phone,$equip_img,$init_count,$annual_fee,$end_time,$lng='',$lat='',$gd_id='',$id=''){
        $data=[
            'member_id'=>$member_id,
            'equip_id'=>$equip_id,
            'water_name'=>$water_name,
            'name'=>$name,
            'pool_type'=>$pool_type,
            'init_count'=>$init_count,
            'annual_fee'=>$annual_fee,
            'warm_phone'=>$warm_phone,
            'end_time'=>$end_time,
            'spare_phone'=>empty($spare_phone)?"":$spare_phone,
            'create_time'=>time()
        ];
        
       
        
        if(!empty($equip_img)){
            $data['equip_img']=$equip_img;
        }
        if(!empty($lat)){
            $data['lat']=$lat;
        }
        
        if(!empty($lng)){
            $data['lng']=$lng;
        }
        
        if(!empty($gd_id)){
            $data['gd_id']=$gd_id;
        }
        
        if(!empty($id)){
            unset($data['member_id']);
            unset($data['create_time']);
            unset($data['end_time']);
            $data['id']=$id;
            $data['update_time']=time();
        }
        $result =$this->__msave($data,'MemberEquip');
        if($result['code']==0){
            $this->setError($result['msg']);
            return false;
        }else{
            cache('equip_count_'.$member_id,null);
            $return=[
                'id'=>$result['id'],
                'equip_id'=>$equip_id,
                ];
            return $return;
        }
    }
    
/*获取所有的设备列表
 * 
 */    
    public function getAllEquipList(){
        
    }
    
   
    
    
/*获取我的设备列表
 * 
 */    
    public function getMyEquipList($member_id,$equip_id=0){
        
        $data=[
            'member_id'=>$member_id
        ];
        if($equip_id>0){
            $data['equip_id']=$equip_id;
        };
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
               ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
               ->join($prex.'pool_type c','a.pool_type=c.id','LEFT')
               ->field('a.gd_id,a.equip_status,a.init_count,a.annual_fee,a.equip_img,a.elec_kz,a.warm_phone,a.id,a.spare_phone,a.water_name,a.equip_id,a.pool_type,c.type_name,a.name,b.connect_status,b.equip_code,a.warn_status,a.is_connect')->where($data)->select();
        
        if(!empty($result)){
            $redis = initRedis();
            $model_memberaera=model('index/MemberAera');
            foreach($result as $k=>$v){
                
                
                $result[$k]['pip_count']=empty($pip_list)?0:count($pip_list);
                $result[$k]['equip_img']= generalQnyImg($v['equip_img']);
                $elec =$this->getLastElec($v['equip_code']);
                $result[$k]['electric']=$elec['electric'];
                $result[$k]['elec_time']=$elec['elec_time'];
                $result[$k]['collector_list']=model('admin/EquipCollector')->getMemberCollector($member_id,$v['equip_id']);
                
                //获取我的管道列表
                $pip_list =$model_memberaera->getMyEquipPip($member_id,$v['equip_id']);
                //获取通道的状态
                $eq_name ='equip_slave_'.$v['equip_code'];
                $slave_json =$redis->get($eq_name);
                $slave_arr =json_decode($slave_json,true);
                if(!empty($slave_arr)){
                   
                    foreach($pip_list as $k1=>$v1){
                        if(!empty($slave_arr) && in_array($v1['num'],$slave_arr)){
                            $pip_list[$k1]['is_normal']=0;
                        }else{
                            $pip_list[$k1]['is_normal']=1;
                        }

                    }
                }
                $result[$k]['pip_list']=$pip_list;
                
            }
        }
        return $result;
    }
    
//    
//    public function getListDetail($member_id,$equip_id=0){
//        $result =$this->getMyEquipList($member_id,$equip_id);
//        
//    }
    
/*删除我的设备
 * 
 */
    public function deleteMyEquip($member_id,$equip_id){
        $data=[
            'member_id'=>$member_id,
            'equip_id'=>$equip_id
        ];
         $this->where($data)->delete();
        cache('equip_count_'.$member_id,null);
        return true;
    }
    
/*获取设备数量
 * 
 */    
    public function getEquipCount($member_id){
        
        $equip_count =cache('equip_count_'.$member_id);
       // var_dump($equip_count);
        if(($equip_count)===false || $equip_count===null ){
        
            $result =$this->getMyEquipList($member_id);
            $equip_count=count($result);
            cache('equip_count_'.$member_id,$equip_count);
        }
        return $equip_count;
    }
    
/*获取详情
 * 
 */
    public function getDetail($member_id,$equip_id){
        $data=[
            'member_id'=>$member_id,
            'equip_id'=>$equip_id
        ];
        
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
               ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
               ->join($prex.'pool_type c','a.pool_type=c.id','LEFT')
               ->field('a.warm_phone,a.id,a.spare_phone,a.water_name,a.equip_id,a.name,b.equip_code,c.type_name,a.pool_type,a.init_count,a.annual_fee,b.agent_id')
               ->where($data)->find();
        return $result;
        //$result =db($this->getTheTable())->where($data)->find(); 
    }
    
/*布/撤防
 * @param int $type 1布2撤
 */    
    public function setWarn($equip_code,$type){
        $data['equip_code']=$equip_code;
       // $data['nums']=$nums;
        $tcp_result =tcpLongConnect($data,($type+3));
        return $tcp_result;
    }
    
/*获取最后的电流
 * 
 */    
    public function getLastElec($equip_code){
        $redis =initRedis();
        $len =$redis->llen($equip_code."_electric_history");
        $result=($redis->lrange($equip_code."_electric_history",0,-1));
        
        if(!empty($result)){
            $result= $result[$len-1];
            $result =json_decode($result,true);
            $results['elec_time']=date('m-d H:i',$result['time']);
            $results['electric']=$result['electric'];
            return $results;
        }else{
            return[
                'elec_time'=>'',
                'electric'=>0
            ];
        }
    }
    

/*获取代理商旗下的用户
 * @param agent_id 代理商id
 * @param type 0全部 1正常2正要过期3已过期
 */
    public function getAgentClient($agen_id,$type=0,$content=''){
        $data['b.agent_id']=$agen_id;   
        if(!empty($content)){
            $data['c.name']=['like',"%".$content."%"];
        }
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
                ->join($prex.'equipment b','b.id=a.equip_id','left')
                ->join($prex.'member c','c.id=a.member_id','left')
                ->field('a.end_time,b.equip_code,b.type,c.avator,c.name,c.telephone')->where($data)->order('a.end_time asc')->select();
        if(!empty($result)){
            $all_type =model('admin/EquipmentType')->getAllType();
            foreach($result as $k=>$v){                
                
                $result[$k]['type']=$all_type[$v['type']]['type_name'];
                $result[$k]['avator']= generalQnyImg($v['avator']);
                $diff_date=ceil_days($v['end_time']);
                $result[$k]['diff_date']=$diff_date;
                
                if($diff_date>=0 && $diff_date<=10){
                    $result[$k]['color']='yellow';
                }else if($diff_date<0){
                     $result[$k]['color']='red';
                }else{
                    $result[$k]['color']='blue';
                }
                
                
                switch ($type){
                    case 0:
                        break;
                    case 1:
                        if($diff_date<=10){
                            unset($result[$k]);
                        }
                        
                        break;
                    case 2:
                        if($diff_date>10 || $diff_date<0){
                            unset($result[$k]);
                        }
                        break;
                    case 3:
                        if($diff_date>0){
                            unset($result[$k]);
                        }
                        break;
                    default:
                        break;
                }
                
                
            }
        }
        return $result;
    }
    
/*
 * 
 */    
    public function get_all_list(){
        $map=[];
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
                ->join($prex.'equipment b','b.id=a.equip_id','left')
                ->field('a.equip_id,a.member_id,b.equip_code')
                ->where($map)->select();
        $return =[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$v['equip_code']]=$v['member_id'];
            }
        }
        return $return;
        
    }
    
    public function set_all_detail($member_id,$jpush_id){
             $data['a.member_id']=$member_id;
             $prex =config('database.prefix');
             $list =db($this->getTheTable())->alias('a')
                     ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                     ->where($data)->field('b.equip_code')->select();
             if(!empty($list)){
                 $redis= initRedis();
                 foreach($list as $k=>$v){
                     $str_name=$v['equip_code']."_detail";
                     $redis_result =$redis->get($str_name);
                     if(!empty($redis_result)){
                         $redis_result = json_decode($redis_result,true);
                         $redis_result['jpush_id']=$jpush_id;
                         $redis->set($str_name,json_encode($redis_result));
                     }
                 }
             }
             return true;
    }
    
}