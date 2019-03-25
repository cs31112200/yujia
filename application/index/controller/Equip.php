<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;


class Equip extends ApiBase
{



    /*通过二维码查找设备
     * @param string qrcode 二维码
     *
     */
    public function findEquipByQrcode(){
        //       $this->returnjson(0,'xxx');
        $this->__checkParam('qrcode',$this->input);
        extract($this->input);
        $model_equip =model('Equipment');
        $result =$model_equip->findEquipByQr($qrcode);
        if($result===false){
            $this->returnjson(0,$model_equip->getError());
        }else{
            $this->returnJsonData(1, '查找成功', $result);
        }
    }

    /*绑定页面初始化
     * @param string equip_id 设备id
     */

    public function bindInit(){

        $return=[];
        //获取养殖类型
        $pool_type_list =model('PoolType')->getPooltype();
        $return['pool_type_list']=$pool_type_list;

        $equip_id =input('equip_id',0,'intval');
        $member_equip_detail=[];
        if($equip_id>0){
            $member_id =$this->verifyUser();
            $member_equip_detail  =model('MemberEquip')->getDetail($member_id,$equip_id);
        };
        $return['member_equip_detail']=$member_equip_detail;
        $this->returnJsonData(1, '请求成功', $return);
    }

    /*修改绑定 设备
     * @param string warm_phone 首选电话
     * @param string spare_phone 备选电话
     * @param string qrcode 二维码
     * @param string water_name 水池名称
     */
    public function changeEquip(){
        $member_id =$this->verifyUser();
        // print_r($this->input);
        $this->__checkParam('warm_phone,name,water_name,equip_id,pool_type',$this->input);
        extract($this->input);
        $model_memberequip=model('MemberEquip');
        $result =$model_memberequip->getDetail($member_id,$equip_id);
        if(empty($result)){
            $this->returnJson(0,'找不到该设备');
        }
        $equip_img =isset($this->input['equip_img'])?$this->input['equip_img']:"";

        $model_phone=model('PhoneVerify');

        //首选号码不一样
        if($warm_phone!=$result['warm_phone']){
            $warm_verify=$this->input['warm_verify'];
            if(empty($warm_verify)){
                $this->returnJson(0,'您的首选号码验证码未传');
            }
            if(!$model_phone->checkVerify($warm_phone,$warm_verify,3)){
                $this->returnJson(0,$model_phone->getError());
            }

        }
        //备选号码不一样
        $spare_phone =isset($this->input['spare_phone'])?$this->input['spare_phone']:'';

        if(!empty($spare_phone) && $spare_phone!=$result['spare_phone']){
            $spare_verify =isset($this->input['spare_verify'])?$this->input['spare_verify']:'';
            if(empty($spare_verify)){
                $this->returnJson(0,'您的备选号码验证码未传');
            }
            if(!$model_phone->checkVerify($spare_phone,$spare_verify,4)){
                $this->returnJson(0,$model_phone->getError());
            }
        }

        $result1 =$model_memberequip->saveMemberEquip($member_id,$equip_id,$name,$pool_type,$water_name,$warm_phone,$spare_phone,$equip_img,$result['init_count'],$result['annual_fee'],'','','','',$result['id']);

        if($result1){
            $msg_arr['name']=$name;;
            if($warm_phone!=$result['warm_phone']){
                $model_phone->changePhoneVerify($warm_phone,3);
                $msg_arr['warm_phone']=$warm_phone;
            }
            if(!empty($spare_phone) && $spare_phone!=$result['spare_phone']){
                $model_phone->changePhoneVerify($spare_phone,4);
                $msg_arr['spare_phone']=$spare_phone;
            }
            $redis = initRedis();
            //记录信息
            model('RedisEquip')->set_equip_msg($result['equip_code'],$msg_arr,$redis);

            $this->returnJsonData(1,'修改成功',$result1);
        }else{
            $this->returnJson(0,$model_memberequip->getError());
        }


    }

    public function qulickBinde(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_code',$this->input);
        extract($this->input);
        $model_equip =model('Equipment');
        $qr_result =$model_equip->findEquipByQr($equip_code);

        $equip_code =$qr_result['equip_code'];
        //检查二维码对应设备吗
        $result =model('Equipment')->getDetailByCode($equip_code);
        if(empty($result) || $result['status']!=1){
            $this->returnJson(0,'您的设备码无效,请联系管理人员');
        }
        $member_result =db('Member')->find($member_id);
        $name =substr($equip_code,strlen($equip_code)-4,4);
        $end_time =date('Y-m-d',time()+86400*365);
        $model_memberequip=model('MemberEquip');

        //获取默认第一个水池类型
        $list =model('PoolType')->getPooltype();

        $result1 =$model_memberequip->saveMemberEquip($member_id,$result['id'],$name,$list[0]['id'],$name,$member_result['account'],'','',$result['init_count'],$result['annual_fee'],$end_time);
        if($result1){
            //获取jpush_id
            $msg_arr['jpush_id']=$member_result['jpush_id'];
            $msg_arr['name']=$name;
            $msg_arr['warm_phone']=$member_result['account'];
            $msg_arr['spare_phone']="";
            $msg_arr['member_id']=$member_id;
            $msg_arr['equip_id']=$qr_result['id'];

            //到期时间
            $msg_arr['end_time']=$end_time;

            model('Equipment')->changeEquipStatus($result['id'],2);

            //记录信息
            $redis= initRedis();
            model('RedisEquip')->set_equip_msg($equip_code,$msg_arr,$redis);

            $return['id']=$result1;


            //同时绑定通道号


            $this->returnJsonData(1,'绑定成功',$result1);


        }else{
            $this->returnJson(0,$model_memberequip->getError());
        }
    }


    /*用户绑定 设备
     * @param string warm_phone 首选电话
     * @param string spare_phone 备选电话
     * @param string qrcode 二维码
     * @param string water_name 水池名称
     */
    public function bindNewEquip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('warm_phone,equip_code,name,water_name,pool_type,warm_verify',$this->input);
        extract($this->input);
        $equip_img =isset($this->input['equip_img'])?$this->input['equip_img']:"";
        // $this->returnJson(1, 'TOUXIANG:'.$equip_img);
        //验证短信 验证验证码
        $model_phone=model('PhoneVerify');
        if(!$model_phone->checkVerify($warm_phone,$warm_verify,3)){
            $this->returnJson(0,$model_phone->getError());
        }

        $spare_phone =isset($this->input['spare_phone'])?$this->input['spare_phone']:'';
        $spare_verify =isset($this->input['spare_verify'])?$this->input['spare_verify']:'';
        $lng =isset($this->input['lng'])?$this->input['lng']:'';
        $lat =isset($this->input['lat'])?$this->input['lat']:'';
        $gd_check =(!empty($lng) && !empty($lat))?1:0;


        if(!empty($spare_phone) && !empty($spare_verify)){
            if(!$model_phone->checkVerify($spare_phone,$spare_verify,4)){
                $this->returnJson(0,$model_phone->getError());
            }
        }

        //检查二维码对应设备吗
        $result =model('Equipment')->getDetailByCode($equip_code);
        if(empty($result) || $result['status']!=1){
            $this->returnJson(0,'您的设备码无效,请联系管理人员');
        }



        if($gd_check){

            //先进入经纬度
            $gd_result =setPoint($equip_code,$lng,$lat);

            //失败记录日志
            if($gd_result['status']==0){
                $gd_result['equip_code']=$equip_code;
                $gd_result['lng']=$lng;
                $gd_result['lat']=$lat;

                //记录日志
                setPointLog($gd_result);
            }
        }
        $end_time =date('Y-m-d',time()+86400*365);

//        
        //设备关联
        $model_memberequip=model('MemberEquip');
        if($gd_check && isset($gd_result['_id']) && $gd_result['_id']>0){
            $result1 =$model_memberequip->saveMemberEquip($member_id,$result['id'],$name,$pool_type,$water_name,$warm_phone,$spare_phone,$equip_img,$result['init_count'],$result['annual_fee'],$end_time,$lng,$lat,$gd_result['_id']);
        }else{
            $result1 =$model_memberequip->saveMemberEquip($member_id,$result['id'],$name,$pool_type,$water_name,$warm_phone,$spare_phone,$equip_img,$result['init_count'],$result['annual_fee'],$end_time);
        }
        if(!empty($result1)){
            //获取jpush_id
            $member_result =db('Member')->find($member_id);
            $msg_arr['jpush_id']=$member_result['jpush_id'];
            $msg_arr['member_id']=$member_id;
            $msg_arr['equip_id']=$result['id'];


            $msg_arr['name']=$name;
            $model_phone->changePhoneVerify($warm_phone,3);
            $msg_arr['warm_phone']=$warm_phone;

            if(!empty($spare_phone) && !empty($spare_verify)){
                $model_phone->changePhoneVerify($spare_phone,4);
                $msg_arr['spare_phone']=$spare_phone;
            }

            //到期时间
            $msg_arr['end_time']=$end_time;

            model('Equipment')->changeEquipStatus($result['id'],2);

            $finance['sn']=create_sn('fin');
            $finance['type']=1;
            $finance['fee']=$result['annual_fee'];
            $finance['fee_type']=4;
            $finance['status']=0;
            $finance['content']='用户年费';
            $finance['object_id']=$result1['id'];
            $finance['need_time']=strtotime($end_time);
            db('Finance')->insert($finance);

            //记录信息
            $redis= initRedis();
            model('RedisEquip')->set_equip_msg($equip_code,$msg_arr,$redis);

            $return['id']=$result1['equip_id'];


            //同时绑定通道号


            $this->returnJsonData(1,'绑定成功',$result1['equip_id']);


        }else{
            $this->returnJson(0,$model_memberequip->getError());
        }

    }

    /*我的设备列表
     *
     */
    public function myEquipList(){
        $member_id =$this->verifyUser();
        $equip_id =isset($this->input['equip_id'])?$this->input['equip_id']:0;
        $result =model('MemberEquip')->getMyEquipList($member_id,$equip_id);
        if(empty($result)){
            $this->returnJson(0,'暂无设备');
        }else{
//            $redis = initRedis();
//            //通道管理
//            
//            foreach($result as $k=>$v){
//                $eq_name ='equip_slave_'.$v['equip_code'];
//                $slave_json =$redis->get($eq_name);
//
//                $slave_arr =json_decode($slave_json,true);
//
//                if(!empty($slave_arr)){
//                    $pip =$result[$k]['pip_list'];
//                    foreach($pip as $k1=>$v1){
//                        if(!empty($slave_arr) && in_array($v1['num'],$slave_arr)){
//                            $pip[$k1]['is_normal']=0;
//                        }else{
//                            $pip[$k1]['is_normal']=1;
//                        }
//
//                    }
//                     $result[$k]['pip_list']=$pip;
//                }
//            }

            $this->returnJsonData(1, '请求成功', $result);
        }
    }

    /*解绑
     * @param string equip_id 设备id
     */
    public function unbindEquip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,telephone,verify',$this->input);
        extract($this->input);


        $model_memberequip=model('MemberEquip');
        $eq_result =$model_memberequip->getDetail($member_id,$equip_id);
        if(empty($eq_result)){
            $this->returnJson(0,'找不到该设备');
        }

        //检验是否是本人的电话
        $result =db('Member')->find($member_id);
        if($result['telephone']!=$telephone){
            $this->returnJson(0,'非法操作');
        }



        //验证短信 验证验证码
        $model_phone=model('PhoneVerify');
        if(!$model_phone->checkVerify($telephone,$verify,5)){
            $this->returnJson(0,$model_phone->getError());
        }


        //检查是否还有通道在使用

//        $result =model('MemberAera')->getMyEquipPip($member_id,$equip_id);
//        if(!empty($result)){
//            $is_open=0;
//            foreach($result as $k=>$v){
//                if($v['status']==1){
//                    $is_open++;
//                }
//            }
//            if($is_open){
//                $this->returnJson(0,'您还有通道未关闭，请先关闭');
//            };
//        };


        $data=[
            'member_id'=>$member_id,
            'equip_id'=>$equip_id
        ];
        $result1 =model('MemberEquip')->deleteMyEquip($member_id,$equip_id);
        if(!$result1){
            $this->returnJson(0, '解绑有误equid_id:'.$equip_id);
        }else{
            model('Equipment')->changeEquipStatus($equip_id,1);
            $model_phone->changePhoneVerify($telephone,5);


            //删除设备点
            if(isset($eq_result['gd_id']) && $eq_result['gd_id']>0){
                delPoint($eq_result['gd_id']);
            }

            //
            $datas['member_id']=$member_id;
            $datas['equip_id']=$equip_id;
            db('MemberAera')->where($datas)->delete();


            //清除redis
            model('RedisEquip')->clear_msg($eq_result['equip_code']);
            $this->returnJson(1, '解绑成功');
        }
    }

    /*获取通道列表
     *
     */
    public function getPipList(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id',$this->input);
        extract($this->input);
        $model_memberaera =model('MemberAera');
        $pip_list =$model_memberaera->getMyEquipPip($member_id,$equip_id);

        if(empty($pip_list)){
            $this->returnJson(0, '暂无内容');
        }
        $this->returnJsonData(1, '请求成功', $pip_list);
    }

    /*获取当前设备电流
     *
     */


    /*绑定通道
     * @param int equip_id 设备id
     * @param string nums 多个以,隔开
     */
    public function bindPip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id',$this->input);
        extract($this->input);
        $nums =isset($this->input['nums'])?$this->input['nums']:'';
        $target_nums =  empty($nums)?[]:explode(',', $nums);
//        
//        //找出已有的nums
//        $model_memberaera =model('MemberAera');
//        $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);
//        
//        
//        //获取要新增得
//        $insert_array =array_diff($target_nums,$save_nums);
//        $delete_array =array_diff($save_nums,$target_nums);
//        

        $equip_detail =db('Equipment')->where('id ='.$equip_id)->field('equip_code')->find();

        if(empty($equip_detail)){
            $this->returnJson(0, '错误的设备id');
        }


        $targets =  implode(',', $target_nums);

        $robot_lng =add_del_pip($equip_detail['equip_code'],$targets);

        $result_num =get_str(base_convert("00000100",2,16));
        //  echo $result_num;
        $in_redis =encode_message_v3("00000100",$equip_detail['equip_code'],20,$result_num,"00","00","0000",$robot_lng,"000000");
//        $redis = initRedis(); 
////        print_r($redis->keys('*'));
//        $eq_names =$equip_detail['equip_code']."_equip_ope_queue";
//        $resultss=$redis->lRange($eq_names,0,-1);
        //   print_r($resultss);exit;
//        $this->returnJson(0,$resultss);
        if($in_redis){
            $this->returnJson(1,'请求成功，请耐心等待');
        }else{
            $this->returnJson(0,'请求失败，请重新操作');
        }


    }


    /*修改通道信息初始化(这里还没添加文档)
     *
     * @param string equip_id 设备id
     * @param string num 通道编号
     */
    public function changePipInit(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,num',$this->input);
        extract($this->input);
        $model_memberaera =model('MemberAera');
        $result =model('MemberAera')->getMyEquipPip($member_id,$equip_id);
        if(!empty($result)){
            $check =$have=[];

            $all_pip =$model_memberaera->allPip();




            foreach($result as $k=>$v){
                $check[$v['num']]=$v;
                $have[]=$v['num'];
            };

            if(!isset($check[$num])){
                $this->returnJson(0,'传入的通道id有误,未找到您的通道');
            };
            $not_have =array_diff($all_pip,$have);
            $not_have[]=$num;
            sort($not_have);
            $check[$num]['can_choose_pip']=  implode(',', $not_have);
            $this->returnJsonData(1, '请求成功', $check[$num]);

        }else{
            $this->returnJson(0,'传入的设备id有误,未找到您的通道');
        }
    }


    /*删除通道(作废)
     * @param int id 通道id
     */
    public function deletePip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id',$this->input);
        extract($this->input);
        $model_memberaera =model('MemberAera');
        $prex =config('database.prefix');
        $thedb =db('MemberAera');
        $result =$thedb->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.equip_id,b.equip_code,a.num,a.member_id')
            ->where('a.id ='.$id)->find();
        // echo $thedb->getLastSql();exit;
        if(empty($result)){
            $this->returnJson(0,'pip错误');
        }
        if($result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }

        $save_nums =$model_memberaera->getPipNormal($member_id,$result['equip_id']);

        foreach($save_nums as $k=>$v){
            if($v==$result['num']){
                unset($save_nums[$k]);
            }
        }
        array_values($save_nums);
        Db::startTrans();
        $results =Db::name('member_aera')->where('id ='.$id)->delete();
        if($results){


            $targets =  (!empty($save_nums))?implode(',', $save_nums):[];
            $tcp_result =$model_memberaera->addDelHardPip($result['equip_code'],$targets);
            if($tcp_result['code']==1){
                Db::commit();
                $this->returnJson(1,'删除成功');
            }else{
                Db::rollback();
                $this->returnJson(0,'删除失败');
            }



        }else{
            Db::rollback();
            $this->returnJson(0, '删除失败');
        }

    }


    /*修改通道
     *
     */
    public function changePip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id,num,name',$this->input);
        extract($this->input);
        //     $remark =isset($this->input['remark'])?$this->input['remark']:"";
        $data =$this->input;
        $data['member_id']=$member_id;
        $data['update_time']=time();

        $model_memberaera =model('MemberAera');
        //$result =db('MemberAera')->find($id);
        $prex =config('database.prefix');
        $thedb =db('MemberAera');
        $result =$thedb->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.equip_id,b.equip_code,a.num,a.member_id')
            ->where('a.id ='.$id)->find();



        if(empty($result)){
            $this->returnJson(0,'pip错误');
        }
        if($result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }
        $equip_id=$result['equip_id'];


        //表示有做更改
        if($result['num']!=$num){

            if($model_memberaera->isPipUse($member_id,$equip_id,$id,$num)){
                $this->returnJson(0, '您所选的通道已被占用');
            }
            unset($data['num']);    //这里放到硬件成功在存储；
        }

        $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);
        $check_nums =$save_nums;
        foreach($check_nums as $k=>$v){
            if($v==$result['num'] && $result['num']!=$num){
                $check_nums[$k]=$num;
            }
        }


        //修改信息

        $save_result =$model_memberaera->__msave($data,'MemberAera','edit');
        if($save_result['code']==1){

            $msg="信息修改成功";
            $oce =1;    //标识
            if($result['num']!=$num){
                $targets =  implode(',', $check_nums);

                $robot_lng =add_del_pip($result['equip_code'],$targets);

                $result_num =get_str(base_convert("00000100",2,16));

                $in_redis =encode_message_v3("00000100",$result['equip_code'],20,$result_num,"00","00","0000",$robot_lng,"000000");

                if(!$in_redis){
                    $this->returnJson(0,'请求失败，请重新操作');
                }else{
                    $msg="信息修改成功，请等待更改通道";


                }
                $oce=1;
            }


            $this->returnJson($oce,$msg);
        }else{
            $this->returnJson(0,$save_result['msg']);
        }

    }


    /*打开通道
     * @param int id 通道id
     * @param int type 类型1 打开2关闭
     */

    public function openClosePip(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id,type',$this->input);
        extract($this->input);

        if($type!=1 && $type!=2){
            $this->returnJson(0,'操作有误');
        }

        $model_memberaera =model('MemberAera');
        $prex =config('database.prefix');
        $thedb =db('MemberAera');
        $result =$thedb->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.status,a.equip_id,b.equip_code,a.num,a.member_id')
            ->where('a.id ='.$id)->find();
        if(empty($result)){
            $this->returnJson(0,'pip错误');
        }
        if($result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }

        if($result['status']==1 && $type==1){
            $this->returnJson(0,'您所选的通道已经打开');
        }

        if($result['status']==0 && $type==2){
            $this->returnJson(0,'您所选的通道已经关闭');
        }

        //筛选出所有打开的

        $my_pip_list =model('MemberAera')->getMyEquipPip($member_id,$result['equip_id']);
        $save_nums=[];
        if(!empty($my_pip_list)){
            foreach($my_pip_list as $k=>$v){
                if($v['status']==1){
                    if($v['num']==$result['num'] && $type==2){
                        continue;
                    }else{
                        $save_nums[]=$v['num'];
                    }
                }
            }
        };

        if($type==1){
            $save_nums[]=$result['num'];
        }

//        dump($save_nums);exit();
        //    print_r()
        $thenums = empty($save_nums)?[]:implode(',', $save_nums);
        $robot_lng =batch_open_close_pip($result['equip_code'],$thenums);

        $result_num =get_str(base_convert("00000010",2,16));

        $in_redis =encode_message_v3("00000010",$result['equip_code'],"20",$result_num,"00","00","0000","000000",$robot_lng);
        if($in_redis){
            $this->returnJson(1,'请求成功，请耐心等待');
        }else{
            $this->returnJson(0,'请求失败，请重新操作');
        }
//        $status =($type==1)?1:0;
//        Db::startTrans();
//        $result1 =model('MemberAera')->changePipStatus($id,$status);
//        $type =$type+1;
//        $result2 =model('MemberAera')->oprateHardPip($result['equip_code'],$result['num'],$type);
//      //  print_r($result2);
//        if($result1 && $result2['code']==1){
//            Db::commit();
//            $this->returnJson(1,'操作成功');
//            
//        }else{
//            Db::rollback();
//            $this->returnJson(0, '操作失败'.$result1."  ".$result2['code']);
//        }
    }

    /*布防/撤防
     *
     */
    public function setWarn(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,type',$this->input);
        extract($this->input);
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;

        if($type!=1 && $type!=2){
            $this->returnJson(0,'类型错误');
        }
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.warn_status,a.equip_id,b.equip_code,a.member_id')->where($data)->find();

        if(empty($result)){
            $this->returnJson(0,'找不到该设备');
        }


        $types =$result['warn_status']>2?($type+2):$type;

        if($result['warn_status']==$types){
            $this->returnJson(1,'操作成功');
        }



        $robot_lng =set_del_warnning($result['warn_status'],$type);
        //      $this->returnJson(0,'测试:'.$robot_lng."   type：".$type);
        $result_num =get_str(base_convert("00100000",2,16));

        $in_redis =encode_message_v3("00100000",$result['equip_code'],"20",$result_num,"00",$robot_lng,"0000","000000","000000");

        if($in_redis){
            $this->returnJson(1,'请求成功，请耐心等待');
        }else{
            $this->returnJson(0,'请求失败，请重新操作');
        }

    }

    /*查询电流
     *
     */
    public function reflashElec(){
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
            $this->returnJson(0,'找不到该设备');
        }
        $result = (model('MemberEquip')->getLastElec($result['equip_code']));

        $this->returnJsonData(1,'请求成功',$result);
        // $redis->
    }


    /*同步操作
     *
     */
    public function sameToHard(){
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
            $this->returnJson(0,'找不到该设备');
        }

        //设置redis
        $equip_code=$result['equip_code'];
        $redis = initRedis();
        $eq_name ='equip_same_'.$equip_code;

        $redis->set($eq_name,1);

        $this->returnJson(1,'请求成功，请耐心等待');

    }

    /*获取用户定时开关操作日志
 *
 */
    public function getClockLogList(){
        $member_id =$this->verifyUser();
        $result=model('EquipClock')->getClockList($member_id);
        if(empty($result)){
            $this->returnJson(0,'暂无操作日志');
        }else{
            $this->returnJsonData(1, '请求成功', $result);
        }
    }
    /*获取用户定时开关操作
     *
     */
    public function getClockList(){
        $member_id =$this->verifyUser();
        $result=model('Clock')->getClockList($member_id);
        if(empty($result)){
            $this->returnJson(0,'暂无定时操作');
        }else{
            $this->returnJsonData(1, '请求成功', $result);
        }
    }
    /*添加/修改定时开关操作
     *
     */
    public function addEditOclock(){
        $member_id =$this->verifyUser();
        if(request()->isPost()) {
            $this->__checkParam('equip_id,ope_time,status,equip_area', $this->input);
            extract($this->input);
            $equip_clock=[];
            $arr=[];
            $equip_clock['create_time'] = time();
            if (isset($this->input['id']) && !empty(intval($this->input['id']))) {
                $data['id'] = intval($this->input['id']);
                $clock = db('EquipClock')->where('clock_id', $data['id'])->find();
                $equip_clock['create_time'] = $clock['create_time'];
                db('EquipClock')->where('clock_id', $data['id'])->delete();
                $result = db('Clock')->find($data['id']);
                if (empty($result) || $result['member_id'] != $member_id) {
                    $this->returnJson(0, '非法操作');
                }
            }
//            $equip_area=array(array('id'=>'1027','status'=>'1'),array('id'=>'1028','status'=>'2'),array('id'=>'1029','status'=>'1'));
            if (isset($this->input['loop_day']) && !empty($this->input['loop_day'])) {
                $data['is_loop'] = 1;
            } else {
                $data['is_loop'] = 0;
            }

            $save_nums = [];
            $open_id = [];
            $close_id = [];
            if (!empty($equip_area)) {
                $equip_area=is_array($equip_area)?$equip_area:explode(',',$equip_area);
                foreach ($equip_area as $k => $v) {
                    $area_num = db('MemberAera')->where('id', $v['id'])->find();
//                    if($equip_id!=$area_num('equip_id') || $member_id!=$area_num('member_id')){
//                        $this->returnJson(0, '非法操作');
//                    }
                    $v['num'] = $area_num['num'];
                    if ($v['status'] == 1) {
                        $save_nums[] = $v['num'];
                        $open_id[] = $v['id'];
                    }
                    if ($v['status'] == 2) {
                        $close_id[] = $v['id'];
                    }
                }
            };
            $data['member_id'] = $member_id;
            $data['equip_id'] = $equip_id;
            $data['ope_time'] = $ope_time;
            $data['loop_day'] = $loop_day;
            $data['status'] = $status;
            $data['open_area'] = $open_id;
            $data['close_area'] = $close_id;
            $result = model('Clock')->__msave($data, 'Clock');
            if ($result['code'] == 1) {
                $theid = (isset($data['id']) && !empty($data['id'])) ? $data['id'] : $result['id'];
                $equip = db('Equipment')->find($equip_id);
                $thenums = empty($save_nums) ? [] : implode(',', $save_nums);
                $robot_lng = batch_open_close_pip($equip['equip_code'], $thenums);
                $result_num = get_str(base_convert("00000010", 2, 16));
                $in_redis = encode_message_v3_str("00000010", $equip['equip_code'], "20", $result_num, "00", "00", "0000", "000000", $robot_lng);
                $equip_clock['member_id'] = $member_id;
                $equip_clock['equip_id'] = $equip_id;
                $equip_clock['clock_id'] = $theid;
                $equip_clock['ope_time'] = $ope_time;
                $equip_clock['ope_str'] = $in_redis;
                $equip_clock['status'] = 0;
                $equip_clock['update_time'] = time();
                $loop_day_arr = is_array($loop_day) ? $loop_day : explode(',', $loop_day);
                $day = date('Y-m-d', time());
                $ope_time = $day . " $ope_time";
                $redis = [];
                $redis['ope_str'] = $in_redis;
                $redis_init = initRedis();
                for ($i = 0; $i < count($loop_day_arr); $i++) {
                    $week = $this->get_week(time());
                    $time = date('H:i:s', time());
                    if ($loop_day_arr[$i] > $week) {
                        $differ_day = $loop_day_arr[$i] - $week;
                        $equip_clock['ope_time'] = date('Y-m-d H:i:s', strtotime("+$differ_day day", strtotime($ope_time)));
                        $opratetime = date('Y-m-d H:i:s', strtotime("+$differ_day day", strtotime($ope_time)));
//                        db('EquipClock')->insert($equip_clock);
//                        continue;
                    } elseif ($loop_day_arr[$i] == $week && $ope_time > $time) {
                        $equip_clock['ope_time'] = $ope_time;
                        $opratetime = $ope_time;
//                        db('EquipClock')->insert($equip_clock);
                    } else {
                        continue;
                    }
                    $redis[$opratetime] = $in_redis;
                    $arr[] = $equip_clock;
                    $redis_arr[] = $redis;
                    $json = json_encode($redis);
                    $redis_key = $equip['equip_code'] . '_cron';
                    $redis_init->rpush($redis_key, $json);
                }
                db('EquipClock')->insertAll($arr);

                $this->returnJson($result['code'], $result['msg']);

            }
        }else{
            $id=input('id');
            if(isset($id) && !empty(intval($id))){
                $clock_result=model('Clock')->getClockList($member_id,$id);
                if(empty($clock_result)){
                    $this->returnJson(0,'非法操作');
                }else{
                    $this->returnJsonData(1, '请求成功', $clock_result);
                }
            }else{
                $equip_data['equip_id']=input('equip_id');
                $equip_data['member_id']=$member_id;
                $equip_result=db('MemberAera')->where($equip_data)->select();
                if(empty($equip_result)){
                    $this->returnJson(0, '非法操作');
                }else{
                    $this->returnJsonData('1','获取成功',$equip_result);
                }
            }
        }


    }
    /*删除定时开关操作
     *
     *
     */
    public function delClock(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id',$this->input);
        extract($this->input);
        $result =db('Clock')->find($id);
        if(empty($result) || $result['member_id']!=$member_id){
            $this->returnJson(0,'非法操作');
        }
        $result =db('Clock')->where(['id'=>$id])->delete();
        if($result){
            db('EquipClock')->where('clock_id',$id)->delete();
            $this->returnJson(1,'删除成功');
        }else{
            $this->returnJson(0,'删除失败');
        }
    }


    /*购买设备
     *
     */
    public function buyEqiup(){
        //     $this->__checkParam('name,contact,province,city,area,address,imei',$this->input);
        $member_id =$this->verifyUser();
        $this->__checkParam('name,contact,address',$this->input);
        extract($this->input);

        $data =$this->input;
        $data['create_time']=time();

        $data1['member_id']=$member_id;

        $result =db('Yxorder')->where($data1)->order('create_time desc')->find();


        if($data['create_time']-86400<$result['create_time']){
            $thetime =($result['create_time']+86400-$data['create_time'])%86400;
            $hour =floor($thetime/3600);
            $thetime =floor($thetime%3600);
            $min =floor($thetime/60);
            $sen =floor($thetime%60);
            $this->returnJson(0,'您近期已提交过意向，请于'.$hour."小时".$min."分钟".$sen."秒后再发起");
        }



        //检测号码
        $contact =$data['contact'];
        if(!check_phone($contact)){
            $this->returnJson(0,'电话号码格式有误');
        }
        $data['member_id']=$member_id;

        $back =model('Yxorder')->__msave($data,'Yxorder');
        $this->returnJson($back['code'],$back['msg']);
    }

    /*布防/撤防
     *
     */
    public function setElecKz(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,elec_kz',$this->input);
        extract($this->input);
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.warn_status,a.elec_kz,a.equip_id,b.equip_code,a.member_id')->where($data)->find();

        if(empty($result)){
            $this->returnJson(0,'找不到该设备');
        }

        if($elec_kz<0 || $elec_kz>10){
            $this->error('阔值设定有误，必须在0.8到10之间');
        }




        if($result['elec_kz']==$elec_kz){
            $this->returnJson(1, '操作成功');
        }

        $robot_lng =set_kz($elec_kz);
        //   $this->returnJson(1,'test:'.$robot_lng);
        //   $robot_lng =set_del_warnning($result['warn_status'],$type);
        //      $this->returnJson(0,'测试:'.$robot_lng."   type：".$type);
        $result_num =get_str(base_convert("00010000",2,16));

        $in_redis =encode_message_v3("00010000",$result['equip_code'],"20",$result_num,"00","00",$robot_lng,"000000","000000");
        if($in_redis){
            $this->returnJson(1,'请求成功，请耐心等待');
        }else{
            $this->returnJson(0,'请求失败，请重新操作');
        }

    }

    public function setJgTime(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,time',$this->input);
        extract($this->input);
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;

        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
            ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
            ->field('a.id,a.warn_status,a.elec_kz,a.equip_id,b.equip_code,a.member_id,a.jg_time')->where($data)->find();

        if(empty($result)){
            $this->returnJson(0,'找不到该设备');
        }

        if($time<5 || $time>60){
            $this->error('时间设定有误');
        }




        if($result['jg_time']==$time){
            $this->returnJson(1, '操作成功');
        }

        $robot_lng =set_jg_time($time);
        //   $this->returnJson(1,'test:'.$robot_lng);
        //   $robot_lng =set_del_warnning($result['warn_status'],$type);
        //      $this->returnJson(0,'测试:'.$robot_lng."   type：".$type);
        $result_num =get_str(base_convert("01000000",2,16));

        $in_redis =encode_message_v3("01000000",$result['equip_code'],"20",$result_num,$robot_lng,"00","0000","000000","000000");
        if($in_redis){
            $this->returnJson(1,'请求成功，请耐心等待');
        }else{
            $this->returnJson(0,'请求失败，请重新操作');
        }

    }

    /*设置采集器别名
     *
     */
    public function setOtherName(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id,other_name',$this->input);
        extract($this->input);
        if(empty($other_name)){
            $this->returnJson(0,'请填写别名');
        }
        //检验采集器id
        $collector_result =db('EquipCollector')->find($id);
        if(empty($collector_result)){
            $this->returnJson(0, '上传id有误');
        }
        if($collector_result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }


        $data['id']=$id;
        $data['other_name']=$other_name;
        db('EquipCollector')->update($data);
        $this->returnJson(1,'修改成功');

    }


    /*query
     * @param type 1今天，2昨天，3最近7天，4最近两周，5最近30天，6上个月，7最近3个月，8最近12个月，9自定义
     */
    public function query_log(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_code,num,type',$this->input);
        extract($this->input);
        $type=intval($type);
        $today=date('Y-m-d');
        if($type<1 || $type>9){
            $this->returnJson(0,'请传正确的type');
        }
        if($type==9 && !isset($first_time)){
            $this->returnJson(0,'请选择正确的起始时间');
        } 
        if($type==9 && !isset($end_time)){
            $this->returnJson(0,'请选择正确的截至时间');
        } 
        $first_time = !isset($first_time)?"":$first_time;
        $end_time = !isset($end_time)?"":$end_time;


        $model_collectorlog =model('CollectorLog');

        $result=$model_collectorlog->query_log_more($equip_code,$num,$member_id,$type,$first_time,$end_time);
        if($result ===false){
            $this->returnJson(0, $model_collectorlog->getError());
        }

//        if($type<3){
//            $result =model('admin/EquipCollector')->query_today_collector($equip_code,$num,$type);
//        }else{
//            
//            if($type==3 && $the_time==$today){
//                $result =model('admin/EquipCollector')->query_today_collector($equip_code,$num,$type);
//            }else{
//                $result=$model_collectorlog->query_log($equip_code,$num,$member_id,$type,$the_time);
//                if($result ===false){
//                    $this->returnJson(0, $model_collectorlog->getError());
//                }
//            }
//        }
        $this->returnJsonData(1, '获取成功', $result);

    }
    public function   get_week($date){
        //强制转换日期格式
        $date_str=date('Y-m-d',$date);
        //封装成数组
        $arr=explode("-", $date_str);
        //参数赋值
        //年
        $year=$arr[0];
        //月，输出2位整型，不够2位右对齐
        $month=sprintf('%02d',$arr[1]);
        //日，输出2位整型，不够2位右对齐
        $day=sprintf('%02d',$arr[2]);
        //时分秒默认赋值为0；
        $hour = $minute = $second = 0;
        //转换成时间戳
        $strap = mktime($hour,$minute,$second,$month,$day,$year);
        //获取数字型星期几
        $number_wk=date("w",$strap);
        //自定义星期数组
        $weekArr=array("7","1","2","3","4","5","6");
        //获取数字对应的星期
        return $weekArr[$number_wk];
    }
    public function addVigilance(){
        $member_id =$this->verifyUser();
        if(request()->isPost()) {
//        $this->__checkParam('equip_id,type,low_value,high_value',$this->input);
            $this->__checkParam('equip_id,value',$this->input);
            extract($this->input);
//            $value=array(
//                array('type'=>'1','low_value'=>'8','high_value'=>'12'),
//                array('type'=>'2','low_value'=>'11','high_value'=>'18'),
//                array('type'=>'3','low_value'=>'10','high_value'=>'14'),
//            );
            $param['member_id']=$member_id;
            $param['equip_id']=$equip_id;
            $equip=db('MemberEquip')->where($param)->find();
            if(empty($equip)){
                $this->returnJson(0, '非法操作');
            }
            if (isset($this->input['id']) && !empty(intval($this->input['id']))) {
                $data1['id']=intval($this->input['id']);
                $result = db('Vigilance')->find($data1['id']);
                if (empty($result) || $result['member_id'] != $member_id) {
                    $this->returnJson(0, '非法操作');
                }
                db('Vigilance')->where($param)->delete();
            }
            $arr=[];
            foreach($value as $k=>$v){
                $data['type']=$v['type'];
                $data['low_value']=$v['low_value'];
                $data['high_value']=$v['high_value'];
                $data['member_id']=$member_id;
                $data['equip_id']=$equip_id;
                $data['status']=1;
                $data['create_time']=time();
                $arr[]=$data;
            }
            $result=db('Vigilance')->insertAll($arr);
            if(!empty($result)){
                $this->returnJson(1,'操作成功');
            }else{
                $this->returnJson(0,'操作失败');
            }
        }else{
            $vigilance['member_id']=$member_id;
            $vigilance['equip_id']=input('equip_id');
            $result1=db('Vigilance')->where($vigilance)->order('type')->select();
            $arr=[];
            foreach($result1 as $k=>$v){
                $result['type']=$v['type'];
                $result['low_value']=$v['low_value'];
                $result['high_value']=$v['high_value'];
                $arr[]=$result;
                $arr['member_id']=$member_id;
                $arr['equip_id']=$v['equip_id'];
                $arr['create_time']=$v['create_time'];
                $arr['status']=$v['status'];
                $arr['id']=$v['id'];
            }
            $this->returnJsonData(1,'获取成功',$arr);
        }
    }
}
