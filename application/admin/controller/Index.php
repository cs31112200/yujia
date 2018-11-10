<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;
header('content-type:text/html;charset=utf-8' );  

class Index extends Base
{
    
    public function index(){
        
        $result =is_login();
        $this->assign('username',$result['name']);
        $this->assign('is_root',$result['is_root']);
        return $this->fetch('/index');
    }
    
    public function home(){
        $this->assign('_title','首页');
        return $this->fetch();
    }

    
   
    public function pwd_change(){
        if(request()->isPost()){
            $data =input('post.');
            if(!isset($data['old_password']) || empty($data['old_password'])){
                $this->error('请填写旧密码');
            }
            
            if(!isset($data['new_password']) || empty($data['new_password'])){
                $this->error('请填写新密码');
            }
            
            if(!isset($data['new_repassword']) || empty($data['new_repassword'])){
                $this->error('请再次填写新密码');
            }
            
            if(strlen($data['new_password'])<6 || strlen($data['new_password'])>15){
                $this->error('密码长度需在6-15个字符之间');
            }
            
            
            if($data['new_password']!==$data['new_repassword']){
                $this->error('两次输入的新密码不一致，请重新输入');
            } 
            
            
            $user_result =db('Admin')->find(UID);
            if($user_result['password']!==md5($data['old_password'])){
                $this->error('您的密码有误，请重新输入');
            }
            $data1['id']=UID;
            $data1['password']=md5($data['new_password']);
            
            db('Admin')->update($data1);
            
            //退出重新操作
            model('Admin')->out();
            
            $this->success('修改密码成功');
            
            
        }else{
            return $this->fetch('',['_title'=>'修改个人密码']);
        }
    }
    
    public function logout(){
        model('Admin')->out();
        $this->success('退出成功');
    }
    
    
}
