<?php
namespace app\index\controller;
use think\Db;
use think\Config;
use think\Request;


class Member extends ApiBase
{
 
/*注册
 * @param varchar telephone 电话号码
 * @param varchar verify 验证码
 * @param varchar passwd 密码
 * @param varchar repeat_passwd 重复密码
 * 
 */    
    public function register(){
        $this->__checkParam('telephone,verify,passwd,repeat_passwd',$this->input);
        extract($this->input);
        
        $model_phone=model('PhoneVerify');
        
        //验证验证码
        if(!$model_phone->checkVerify($telephone,$verify,1)){
            $this->returnJson(0,$model_phone->getError());
        }
        $data['account']=$telephone;
        $data['name']=$telephone;
        $data['telephone']=$telephone;
        $data['passwd']=$passwd;
        $data['repeat_passwd']=$repeat_passwd;
        $result =model('Member')->__msave($data,'Member','edit');
        
        if($result['code']==1){
            
            $model_phone->changePhoneVerify($telephone,1);
//            
//            $data1['id']=result['id'];
//            $data1['jpush_id']=getQrcode($result['id'],10);
       //     db('Member')->update($data1);
            
            
            $this->returnJson(1,'注册成功');
        }else{
            $this->returnJson(0,$result['msg']);
        }        
    }
    
/*发送验证码
 * @param varchar telephone 电话号码
 * @param int type 1注册用2 找回密码用 3验证首选号码 4验证备选号码
 */    
    public function send_code(){
        $this->__checkParam('telephone,type',$this->input);
        extract($this->input);
        
        if($type==2 && !model('Member')->isExitPhone($telephone)){
            $this->returnJson(0,'您的输入的电话未注册过');
        };
        $result =SendCodes($telephone,$type);
        $this->returnJson($result['status'],$result['msg']);
    }
    
/*重置密码
 * @param varchar telephone 电话号码
 * @param varchar verify 验证码
 * @param varchar passwd 密码
 * @param varchar repeat_passwd 重复密码
 */    
    public function set_pwd(){
        $this->__checkParam('telephone,verify,passwd,repeat_passwd',$this->input);
        extract($this->input);
        //验证验证码
        $model_phone=model('PhoneVerify');
        if(!$model_phone->checkVerify($telephone,$verify,2)){
            $this->returnJson(0,$model_phone->getError());
        }
        
        if($passwd!=$repeat_passwd){
            $this->returnJson(0,'两次密码不一致');
        };
        model('Member')->setNewPwd($telephone,$passwd);
        $model_phone->changePhoneVerify($telephone,2);
        $this->returnJson(1,'修改成功');
    }
    
    
/*登录
 * @param varchar account 帐号
 * @param varchar passwd 密码
 * @param varchar $imei 设备号
 * @param int $type 1android ，2ios
 */    
    public function login(){
        $this->__checkParam('account,passwd,imei,type',$this->input);
        extract($this->input);
        
        if($type!=1 && $type!=2){
            $this->returnJson(0,'设备类型错误');
        }
        
        $model_member =model('Member');
        $result =$model_member->login($account,$passwd,$imei,$type);
        
        if($result!==false){
            $result['up_token']=getQnyTokens();
            //更新所有的对应的redis的jpush_id
            model('MemberEquip')->set_all_detail($result['id'],$result['jpush_id']);
            $this->returnJsonData(1, '登录成功', $result);
        }else{
            $this->returnJson(0,$model_member->getError());
        }
    }
    
/*退出
 * 
 */   
    public function out(){
        $member_id = $this->verifyUser();
        model('Member')->out($member_id);
        $this->returnJson(1,'退出成功');
    }
    
    
/*绑定极光推送
 * 
 */   
    public function bindJpushId(){
        $member_id = $this->verifyUser();
        $this->__checkParam('imei',$this->input);
        extract($this->input);
        
        $result =db('Member')->find($member_id);
        if(empty($result) || $result['jpush_id']!=$imei){
            $data['id']=$member_id;
            $data['jpush_id']=$imei;
            db('Member')->update($data);
        }
        $this->returnJson(1,'绑定成功');
        
        
    }
    
/*修改个人信息
 * 
 */    
    public function changePersonMsg(){
        $member_id = $this->verifyUser();
       // $this->__checkParam('name,province,city,area,sex',$this->input);
        $name= isset($this->input['name'])?$this->input['name']:"";
        $province= isset($this->input['province'])?$this->input['province']:"";
        $city= isset($this->input['city'])?$this->input['city']:"";
        $area= isset($this->input['area'])?$this->input['area']:"";
        $address= isset($this->input['address'])?$this->input['address']:"";
        $sex= isset($this->input['sex'])?$this->input['sex']:"";
        $avator =isset($this->input['avator'])?$this->input['avator']:"";
        
        
        $data= $this->input;
        //unset($data)
//        $file = request()->file('file');
//        if(!empty($file)){
//            // 移动到框架应用根目录/public/uploads/ 目录下
//            $info = $file->validate(['size'=>2*1024*1024,'ext'=>'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads');
//            if($info){
//                    $save_name =$info->getSaveName();
//                    $save_name =  str_replace('\\', '/', $save_name);
//                    $file_path ='/uploads/'.$save_name;
//                    $data['avator']=$file_path;
//            }else{
//                    $this->returnJson(0,$file->getError());
//            }
//        }
        
        
        if(!isset($avator) && empty($name) && empty($province) && empty($city) && empty($area) && empty($sex)&& empty($address)){
            $this->returnJson(0,'您未提交信息');
        }
        
        $data['id']=$member_id;
        $data['update_time']=time();
        $result =model('Member')->__msave($data,'Member','changemsg');
        if($result['code']==1){
            if(!empty($avator)){
               $data['avator'] = generalQnyImg($data['avator']);
            }
            $this->returnJsonData(1,'修改成功',$data);
        }else{
            $this->returnJson(0,$result['msg']);
        }   
        
    }
    
    public function getQny(){
        $member_id =$this->verifyUser();
       $data['up_token']=getQnyTokens();
       $this->returnJsonData(1, '获取token成功', $data);
      // print_r($upToken);
    }
}
