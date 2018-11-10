<?php
namespace app\index\model;

use app\admin\model\Base;

class MemberAera extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*获取我的设备的通道
 * 
 */
    public function getMyEquipPip($member_id,$equip_id){
        $data=[
            'member_id'=>$member_id,
            'equip_id'=>$equip_id
        ];
        $result =db($this->getTheTable())->where($data)->field('id,num,status,name,remark')->order('num asc')->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $result[$k]['clock_list']=model('index/Clock')->getMemberClock($v['id']);
                $result[$k]['is_normal']=1;
            }
        }
        
        
        return empty($result)?null:$result;
    }
    
/*获取通道号
 * 
 */    
    public function getPipNormal($member_id,$equip_id){
        $result =$this->getMyEquipPip($member_id, $equip_id);
        $save_nums=[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $save_nums[]=$v['num'];
            }
        };
        return $save_nums;
    }

/*检测通道是否站通用
 * 
 */    
    public function isPipUse($member_id,$equip_id,$pip_id,$num){
        $result =$this->getMyEquipPip($member_id, $equip_id);
        $use=0;
        if(!empty($result)){
            foreach($result as $k=>$v){
                if($v['num']==$num && $pip_id!=$v['id']){
                    $use=1;
                }
            }
        }
        return $use;
    }

/*所有通道
 * 
 */    
    public function allPip(){
        $result =[];
        for($i=0;$i<24;$i++){
            $result[]=$i+1;
        }
        return $result;
    }
/*开通设备
 * 
 */    
    public function openPip($member_id,$equip_id,$opens){
        if(empty($opens)){
            return ;
        }
        $data =[];
        if(is_array($opens)){
            
            foreach($opens as $k=>$v){
                $data[$k]['member_id']=$member_id;
                $data[$k]['equip_id']=$equip_id;
                $data[$k]['num']=$v;
              //  $data[$k]['status']=0;
                $data[$k]['create_time']=time();
            }
            $result =$this->insertAll($data);
        }else{
                $data['member_id']=$member_id;
                $data['equip_id']=$equip_id;
                $data['num']=$opens;
             //   $data['status']=1;
                $data['create_time']=time();
            $result =$this->insert($data);
        }
        
        return $result;
    }
    
    public function deletePip($member_id,$equip_id,$opens){
        //echo 111;
        if(empty($opens)){
           
            return ;
        }
      //  echo 222;
        $data['member_id']=$member_id;
        $data['equip_id']=$equip_id;
        $data['num'] =['in',$opens]; 
        $result =$this->where($data)->delete();
        return ;
      
    }

    
/*操作硬件
 * @param int type 类型 1添加2删除3打开4关闭
 */
    public function oprateHardPip($equip_code,$num,$type){
        $data['equip_code']=$equip_code;
        $data['num']=$num;
        $tcp_result =tcpLongConnect($data,$type);
        return $tcp_result;
    }
    
/*添加 删除设备
 * 
 */    
    public function addDelHardPip($equip_code,$nums){
        $data['equip_code']=$equip_code;
        $data['nums']=$nums;
        $tcp_result =tcpLongConnect($data,31);
        return $tcp_result;
    }
  
/*打开关闭通道0
 * 
 */
    public function open_close_pip($equip_code,$num,$type){
        $type=$type+1;
        $data['equip_code']=$equip_detail['equip_code'];
        $data['num']=$num;
        $tcp_result =tcpLongConnect($data,$type);
        return $tcp_result;
    }
    
    public function changePipStatus($id,$status){
        $data['id']=$id;
        $data1['status']=$status;
        $data1['update_time']=time();
        $result= $this->where($data)->update($data1);
     //   echo $this->getLastSql();
        return $result;
    }
}