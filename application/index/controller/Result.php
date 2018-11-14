<?php
namespace app\index\controller;
use think\Db;
use think\Config;
use think\Request;


class Result extends ApiBase
{

    
    
    public function bindPipCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParams('equip_id',$this->input);
        extract($this->input);
        
        $type="00000100";
        
        $check_result =$this->commonCheck($equip_id, $type,$member_id);
        
        
        //成功才删除
        if($check_result['results']==1){
            $nums =isset($this->input['nums'])?$this->input['nums']:'';
            
            $target_nums =  empty($nums)?[]:explode(',', $nums);
          //  print_r($target_nums);
            //找出已有的nums
            $model_memberaera =model('MemberAera');
            $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);
            //获取要新增得
            $insert_array =array_diff($target_nums,$save_nums);
            $delete_array =array_diff($save_nums,$target_nums);
            $result =model('MemberAera')->openPip($member_id,$equip_id,$insert_array);
            $result1 =model('MemberAera')->deletePip($member_id,$equip_id,$delete_array);
           // echo "#";var_dump($result);echo "   ,";var_dump($result1);
            
        }
        if($check_result['results']<=0){
            $this->returnJson(0, '未做回应');
        }
        $redis= initRedis();
        $redis->delete($check_result['names']);
        $msg =($check_result['results']==1)?"硬件操作成功":"硬件操作失败";
            

        //记录操作
        model('EquipOpe')->addOpe($member_id,$equip_id,1,'添加设备通道，当前打开通道号为：'.implode(',', $insert_array));
        
        
        $this->returnJson($check_result['results'],$msg);
        
    }
   
    
/*修改通道的轮询地址
 * 
 */    
    public function changePipCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id,num,name',$this->input);
        extract($this->input);
        $data['num'] =$num;
        $data['id'] =$id;
        $type="00000100";
        
        $model_memberaera =model('MemberAera');

        $result =db('MemberAera')->find($id);

        $equip_id =$result['equip_id'];
        
         $check_result =$this->commonCheck($equip_id, $type,$member_id);
         
         if($check_result['results']==1){
             $save_result =$model_memberaera->__msave($data);
         }
        if($check_result['results']<=0){
            $this->returnJson(0, '未做回应');
        }
        $redis= initRedis();
        $redis->delete($check_result['names']);
        $msg =($check_result['results']==1)?"硬件操作成功":"硬件操作失败";
        
            
        //记录操作
        model('EquipOpe')->addOpe($member_id,$equip_id,2,'修改通道号，从'.$result['num']."号变成".$data['num']);
        
        
        $this->returnJson($check_result['results'],$msg);
    }
    
/*打开/关闭通道
 * 
 */    
    public function openClosePipCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id,type',$this->input);
        extract($this->input);
        $prex =config('database.prefix');
        $thedb =db('MemberAera');
        $result =$thedb->alias('a')
               ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
               ->field('a.id,a.status,a.equip_id,b.equip_code,a.num,a.member_id')
                ->where('a.id ='.$id)->find();
        $request_type="00000010";
        $equip_id =$result['equip_id'];
        
         $check_result =$this->commonCheck($equip_id, $request_type,$member_id);
         
         if($check_result['results']==1){
             $status =($type==1)?1:0;
             $save_result =model('MemberAera')->changePipStatus($id,$status);
         }
         
        if($check_result['results']<=0){
            $this->returnJson(0, '未做回应');
        }
        $redis= initRedis();
        $redis->delete($check_result['names']);
        $msg =($check_result['results']==1)?"硬件操作成功":"硬件操作失败";
        
        
        $thetype =$type==1?3:4;
        $content=$type==1?'打开通道号,通道号为:'.$result['num']."号":'关闭通道号,通道号为:'.$result['num']."号";
        //记录操作
        model('EquipOpe')->addOpe($member_id,$equip_id,$thetype,$content);
        
        
        $this->returnJson($check_result['results'],$msg);
        
    }
    
    
/*布/撤防
 * 
 */    
    public function setWarnCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,type',$this->input);
        extract($this->input);
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;
        
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->field('a.id,a.warn_status,a.equip_id,b.equip_code,a.member_id')->where($data)->find();
        $request_type="00100000";
       // $equip_id =$result['equip_id'];
        
         $check_result =$this->commonCheck($equip_id, $request_type,$member_id);
         
         
         $types =$result['warn_status']>2?($type+2):$type;
         
         if($check_result['results']==1){
            $data1['warn_status']=$types;
            $data2['member_id']=$member_id;
            $data2['equip_id']=$equip_id;
            $results =model('MemberEquip')->where($data2)->update($data1);
         }
        if($check_result['results']<=0){
            $this->returnJson(0, '未做回应');
        }
        $redis= initRedis();
        $redis->delete($check_result['names']);
        $msg =($check_result['results']==1)?"硬件操作成功":"硬件操作失败";
        
        $setss=1;
        switch($types){
            case 1:
                $thetype=5;
                $content='设置设备号：'.$result['equip_code']."自动布防";
                break;
            case 2:
                $thetype=6;
                $content='设置设备号：'.$result['equip_code']."自动撤防";
                break;
            case 3:
                $thetype=7;
                 $content='设置设备号：'.$result['equip_code']."手动布防";
                break;
            case 4:
                $thetype=8;
                 $content='设置设备号：'.$result['equip_code']."手动撤防";
                break;
            default:
                $setss=0;
               break;
        }
        if($setss){
            model('EquipOpe')->addOpe($member_id,$equip_id,$thetype,$content);
        }
        
        $this->returnJson($check_result['results'],$msg);
    }
    public function setElecKzCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,elec_kz',$this->input);
        extract($this->input);

        $request_type="00010000";

         $check_result =$this->commonCheck($equip_id, $request_type,$member_id);
         
         
         
         if($check_result['results']==1){
            $data1['elec_kz']=$elec_kz;
            $data2['member_id']=$member_id;
            $data2['equip_id']=$equip_id;
            $results =model('MemberEquip')->where($data2)->update($data1);
         }
        if($check_result['results']<=0){
            $this->returnJson(0, '未做回应');
        }
        $redis= initRedis();
        $redis->delete($check_result['names']);
        $msg =($check_result['results']==1)?"硬件操作成功":"硬件操作失败";

        
        $this->returnJson($check_result['results'],$msg);
    }
    
/*
 * 
 */    
    
    
    
/*同步
 * 
 */   
    public function sameToHardCheck(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id',$this->input);
        extract($this->input);
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->field('a.id,a.warn_status,a.equip_id,b.equip_code,a.member_id')->where($data)->find();
        if(empty($result)){
            $this->returnJson(0,'设备码有误');
        }
        
        $equip_code=$result['equip_code'];
        $redis = initRedis();
        
        $str_name =$equip_code."_msg";
        $last_arr =$redis->get($str_name);
        $last_arr =json_decode($last_arr,true);
        if(empty($last_arr)){
           $this->returnJson(0,'同步失败1');
        }
        $data5['member_id']=$member_id;
        $data5['equip_id']=$equip_id;
        
        //获取间隔、阔值、布撤防、设备状态
        $save_data['jg_time']=$last_arr['interval'];
        if($last_arr['install']=="1"){
            $thetype=1;
        }
        if($last_arr['install']=="0"){
            $thetype=2;
        }
        if($last_arr['install']=="2"){
            $thetype=4;
        }
        if($last_arr['install']=="3"){
            $thetype=3;
        }
        $save_data['warn_status']=$thetype;
        $save_data['equip_status'] = ($last_arr['deviceStatus']=="0")?1:2;
        $save_data['elec_kz']=$last_arr['electricT'];
        $save_result =model('MemberEquip')->where($data5)->update($save_data);
//        if(!$save_result){
//            $this->returnJson(0, '同步失败2');
//        }
        
        
        //设备管理
        $target_nums =$last_arr['deviceManagement']['used'];
        $target_nums= explode(',', $target_nums);
        $model_memberaera =model('MemberAera');
        $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);

     //   print_r($target_nums);
      //  print_r($save_nums);exit;

        //获取要新增得
        $insert_array =array_diff($target_nums,$save_nums);
        $delete_array =array_diff($save_nums,$target_nums);

        //同步设备管理
        $result1 =model('MemberAera')->openPip($member_id,$equip_id,$insert_array);
        $result2 =model('MemberAera')->deletePip($member_id,$equip_id,$delete_array);
//        if(!$result1  || !$result2){
//            $this->returnJson(0, '同步失败3');
//        }
        
        //设备操作
        $target_open_nums =$last_arr['deviceOperation']['open'];
        $target_open_nums = explode(',', $target_open_nums);
       // print_r( $target_open_nums);echo "   ";

        $datat['member_id']=$member_id;
        $datat['equip_id']=$equip_id;
        //同步设备操作
        $result3=$result4=1;
        if(!empty($target_open_nums)){

            $datat['num']=['in',$target_open_nums];
            $data1['status']=1;
            $result3=model('MemberAera')->where($datat)->update($data1);
            $datat['num']=['not in',$target_open_nums];
            $data1['status']=2;
            $result4=model('MemberAera')->where($datat)->update($data1);
        }else{
            $data1['status']=2;
            $result4=model('MemberAera')->where($datat)->update($data1);
        }
//        if(!$result4  || !$result3){
//            $this->returnJson(0, '同步失败4');
//        }
//        
        $returndata  =model('MemberEquip')->getMyEquipList($member_id,$equip_id);
        $this->returnJsonData(1, '同步成功',$returndata);
//        if(isset($last_arr['slaveManagement']['slave']) && !empty($last_arr['slaveManagement']['slave'])){
//            $master =$last_arr['slaveManagement']['master'];
//            foreach($returndata as $k=>$v){
//                
//            }
//        }
//        
//        
//        
//        
//        
//        
//        $eq_name ='equip_same_'.$equip_code;
//        
//        $message=$redis->get($eq_name);
//        
//        //表示同步过来了
//        if(strlen($message)>1){
//            
//            
//            $data5['member_id']=$member_id;
//            $data5['equip_id']=$equip_id;
//            //解析
//            $msg_result =decode_message($message);
//               
//            //thetime 硬件请求间隔
//            
//            
//            //电流阔值
//            
//            
//            if($msg_result['bcf']=="01"){
//                $thetype=1;
//            }
//            
//            if($msg_result['bcf']=="00"){
//                $thetype=2;
//            }
//                
//            
//            if($msg_result['bcf']=="02"){
//                $thetype=4;
//            }
//                
//            if($msg_result['bcf']=="03"){
//                $thetype=3;
//            }
//
//                        
//                        
//                        
//            //bcf布撤防  与设备状态 设备阔值
//            $save_data['warn_status'] = $thetype;
//            $save_data['equip_status'] = ($msg_result['equip_status']=="00")?1:2;
//            $save_data['elec_kz']=$msg_result['elec_setting']/10;
//            
//            
//            model('MemberEquip')->where($data5)->update($save_data);
//            
//            
//            //设备管理
//            $target_nums =decode_equip_manage($msg_result['equip_manage']);
//            
//            $model_memberaera =model('MemberAera');
//            $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);
//            
//           // print_r($target_nums);
//          //  print_r($save_nums);exit;
//
//            //获取要新增得
//            $insert_array =array_diff($target_nums,$save_nums);
//            $delete_array =array_diff($save_nums,$target_nums);
//            
//            //同步设备管理
//            $result =model('MemberAera')->openPip($member_id,$equip_id,$insert_array);
//            $result =model('MemberAera')->deletePip($member_id,$equip_id,$delete_array);
//            
//            
//            //设备操作
//            $target_open_nums =decode_equip_manage($msg_result['equip_ope']);
//            
//       //     print_r( $target_open_nums);echo "   ";
//           
//            $datat['member_id']=$member_id;
//            $datat['equip_id']=$equip_id;
//            //同步设备操作
//            if(!empty($target_open_nums)){
//               
//                $datat['num']=['in',$target_open_nums];
//                $data1['status']=1;
//                model('MemberAera')->where($datat)->update($data1);
//                $datat['num']=['not in',$target_open_nums];
//                $data1['status']=2;
//                model('MemberAera')->where($datat)->update($data1);
//            }else{
//                $data1['status']=2;
//                model('MemberAera')->where($datat)->update($data1);
//            }
//            
//            
//            
//             $redis->delete($eq_name);
//            $returndata  =model('MemberEquip')->getMyEquipList($member_id,$equip_id);
//            
//            
//            
//            //通道管理
//            $eq_name ='equip_slave_'.$equip_code;
//            $slave_json =$redis->get($eq_name);
//            
//            $slave_arr =json_decode($slave_json,true);
//            
//            if(!empty($slave_arr)){
//                $pip =$returndata[0]['pip_list'];
//                foreach($pip as $k=>$v){
//                    if(!empty($slave_arr) && in_array($v['num'],$slave_arr)){
//                        $pip[$k]['is_normal']=0;
//                    }else{
//                        $pip[$k]['is_normal']=1;
//                    }
//                    
//                }
//                 $returndata[0]['pip_list']=$pip;
//            }
//           
//            
//            //数据管理
//            
//            
//            
//            
//            
//            $this->returnJsonData(1, '同步成功',$returndata);
//            
//        }else{
//            $this->returnJson(0, '未做回应');
//        }
        
    }
    
/*常规检验
 * 
 */  
    public function commonCheck($equip_id,$type,$member_id){
        
        //检验设备
        $model_memberequip=model('MemberEquip');
        $result =$model_memberequip->getDetail($member_id,$equip_id);
        
        if(empty($result)){

            $this->returnJson(-1,'找不到该设备');
        }
        $equip_code =$result['equip_code'];
        
        $result_name ='equip_cr_'.$type.'_'.$equip_code;
        
        $redis = initRedis();
        $check_result =$redis->zScore('equip_cr_zlist',$result_name);
    //    $check_result =$redis->get($result_name);
        $check_result =empty($check_result)?0:$check_result;
        $check_result =(empty($check_result) || $check_result<2)?0:1;
        $back['names']=$result_name;
        $back['results']=$check_result;
        
        return $back;
    }
    
/*开通、删除通道
 * @param string ope 操作码
 * @param string sig 签名
 * 
 * 
 */    
    public function reflash_operation(){
        $app_key =config('APP_KEY');
        $sig=input('sig','','trim');
        $code_str =input('ope','','trim');
        $insert['content']="sig:$sig,ope:$code_str";
        $insert['type']=15;
        $insert['create_time']=time();
        db('testt')->insert($insert);
        if(empty($sig) || empty($code_str)){
            $insert['content']='没有sig或者ope参数';
            $insert['type']=8;
            $insert['create_time']=time();
            db('testt')->insert($insert);
            echo "fail";exit;
        }
        //验证校验码
        $sig_check = md5($code_str.$app_key);
        if($sig_check!=$sig){
            $insert['content']='校验码错误';
            $insert['type']=13;
            $insert['create_time']=time();
            db('testt')->insert($insert);
            echo "fail";exit;
        }
        //判断长度
        if(strlen($code_str)!=46){
            $insert['content']='ope长度不等于46';
            $insert['type']=9;
            $insert['create_time']=time();
            db('testt')->insert($insert);
             echo "fail";exit;
        };

        //equip_code
        $equip_code =substr($code_str,6,12);
        //操作码
        $ope_code =substr($code_str,20,2);
        
        
        //判断设备号
        $data['equip_code']=$equip_code;
        $equip_result=db('Equipment')->where($data)->find();
        if(empty($equip_result)){
            $insert['content']='该设备号不存在';
            $insert['type']=10;
            $insert['create_time']=time();
            db('testt')->insert($insert);
             echo "fail";exit;
        }
        $search_data['equip_id']=$equip_result['id'];
        $member_equip_result =db('MemberEquip')->where($search_data)->find();
        if(empty($member_equip_result)){
            $insert['content']='用户设备表中不存在';
            $insert['type']=11;
            $insert['create_time']=time();
            db('testt')->insert($insert);
            echo "fail";exit;
        }
        $member_id =$member_equip_result['member_id'];
        //间隔
      //  $intervals =substr($code_str,22,2);
        //布撤防
        $bcf =substr($code_str,24,2);
        //电流阔值
        $elect_range=substr($code_str,26,4);
        //设备管理
        $device_management=substr($code_str,30,6);
        //设备操作
        $device_operator=substr($code_str,36,6);

        //解开操作码
        $ope_code_binary =trans16to2($ope_code,8);

        $ready_arr=[];
        for($i=0;$i<8;$i++){
            if($ope_code_binary[$i]==1 && $i!=7){
            $ready_arr[]=$i;
            }
        }
        if(empty($ready_arr)){
            $insert['content']='ready_arr为空';
            $insert['type']=12;
            $insert['create_time']=time();
            db('testt')->insert($insert);
             echo "fail";exit;
        }
        foreach($ready_arr as $k=>$v){

            switch($v){
                //同步间隔
                case 1:

                    $insert['content']="case 1";
                    $insert['type']=21;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);


                    $intervals =substr($code_str,22,2);
                    $data11['equip_id']=$equip_result['id'];
                    $data12['jg_time']=intval($intervals);
                    db('MemberEquip')->where($data11)->update($data12);
                    
                    model('EquipOpe')->addOpe($member_id,$equip_result['id'],$thetype,$content);

                    $insert['content']='同步间隔';
                    $insert['type']=1;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);
                    break;

                //布撤防
                case 2:
                    $insert['content']="case 2";
                    $insert['type']=21;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);


                    $bcf =substr($code_str,24,2);
                    $bcf =intval($bcf);
                    if($bcf==0){
                        $data22['warn_status']=2;
                        $thetype=6;
                        $content='设置设备号：'.$equip_result['equip_code']."自动撤防";
                    }else if($bcf==1){
                        $data22['warn_status']=1;
                        $thetype=5;
                        $content='设置设备号：'.$equip_result['equip_code']."自动布防";
                        
                    }else if($bcf==2){
                        $data22['warn_status']=4;
                        $thetype=8;
                        $content='设置设备号：'.$equip_result['equip_code']."手动撤防";
                    }else{
                        $data22['warn_status']=3;
                        $thetype=7;
                        $content='设置设备号：'.$equip_result['equip_code']."手动布防";
                    }
                     $data21['equip_id']=$equip_result['id'];
                     db('MemberEquip')->where($data21)->update($data22);
                     
                      model('EquipOpe')->addOpe($member_id,$equip_result['id'],$thetype,$content);


                    $insert['content']='布撤防';
                    $insert['type']=2;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);
                    break;
                    
                //电流阀值
                case 3:
                    $insert['content']="case 3";
                    $insert['type']=21;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);


                    $elect_range=substr($code_str,26,4);
                    $elect_range =intval($elect_range)/10;
                    $data31['equip_id']=$equip_result['id'];
                    $data32['elec_kz']=$elect_range;
                    db('MemberEquip')->where($data31)->update($data32);
                    model('EquipOpe')->addOpe($member_id,$equip_result['id'],9,'设置电流阀值');

                    $insert['content']='电流阀值';
                    $insert['type']=3;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);
                    break;
                //设备管理
                case 5:
                    $insert['content']="case 5";
                    $insert['type']=21;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);


                    $search['equip_id']=$equip_result['id'];
                    $member_equip_result =db('MemberEquip')->where($search)->find();
                    if(empty($member_equip_result)){
                        $insert['content']='member_equip_result为空';
                        $insert['type']=14;
                        $insert['create_time']=time();
                        db('testt')->insert($insert);
                         echo "fail";exit;
                    }
                    $member_id =$member_equip_result['member_id'];
                    $equip_id =$search['equip_id'];
                    
                    $device_management=substr($code_str,30,6);
                    $target_nums =decode_equip_manage($device_management);

                    $model_memberaera =model('MemberAera');
                    $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);

                   // print_r($target_nums);
                  //  print_r($save_nums);exit;

                    //获取要新增得
                    $insert_array =array_diff($target_nums,$save_nums);
                    $delete_array =array_diff($save_nums,$target_nums);


                    $insert_implode=implode(',',$insert_array);
                    $delete_implode=implode(',',$delete_array);


                    $insert['content']="新增通道$insert_implode";
                    $insert['type']=19;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);

                    $insert['content']="减少通道$delete_implode";
                    $insert['type']=20;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);

                    //同步设备管理
                    $result =model('MemberAera')->openPip($member_id,$equip_id,$insert_array);
                    $result =model('MemberAera')->deletePip($member_id,$equip_id,$delete_array);
                    
                    $content ="当前新增的通道号:".$insert_implode."减少的通道号:".$delete_implode;
                    //记录操作
                    model('EquipOpe')->addOpe($member_id,$equip_result['id'],1,$content);

                    $insert['content']='设备管理';
                    $insert['type']=5;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);
                    break;
                //设备操作    
                case 6:
                    $insert['content']="case6";
                    $insert['type']=21;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);


                    $search['equip_id']=$equip_result['id'];
                    $member_equip_result =db('MemberEquip')->where($search)->find();
                    if(empty($member_equip_result)){
                        $insert['content']='$member_equip_result为空';
                        $insert['type']=15;
                        $insert['create_time']=time();
                        db('testt')->insert($insert);
                         echo "fail";exit;
                    }
                    $member_id =$member_equip_result['member_id'];
                    $equip_id =$search['equip_id'];
                    
                    $device_operator=substr($code_str,36,6);
                    $target_open_nums =decode_equip_manage($device_operator);
                    $target_open_nums=implode(',',$target_open_nums);
                    $insert['content']="打开的通道$target_open_nums";
                    $insert['type']=18;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);

               //     print_r( $target_open_nums);echo "   ";

                    $datat['member_id']=$member_id;
                    $datat['equip_id']=$equip_id;
                    //同步设备操作
                    if(!empty($target_open_nums)){

                        $datat['num']=['in',$target_open_nums];
                        $data1['status']=1;
                        model('MemberAera')->where($datat)->update($data1);
                        $datat['num']=['not in',$target_open_nums];
                        $data1['status']=2;
                        model('MemberAera')->where($datat)->update($data1);
                    }else{
                        $data1['status']=2;
                        model('MemberAera')->where($datat)->update($data1);
                    }
                    $content ="当前打开的通道号:".implode(',', $target_open_nums);
                    //记录操作
                    model('EquipOpe')->addOpe($member_id,$equip_result['id'],3,$content);


                    $insert['content']='设备操作';
                    $insert['type']=6;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);

                    break;
                default:
                    $insert['content']='default';
                    $insert['type']=7;
                    $insert['create_time']=time();
                    db('testt')->insert($insert);
                    break;
            }
        }
        echo "success";
    }
    
}
