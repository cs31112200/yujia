<?php
namespace app\agent\controller;
use think\Db;
use think\Config;
use think\Request;
use think\Controller;

class Login extends Controller
{
    
    
/*登陆
 * 
 */
    public function login(){
        $openid =session('openid');
        if(!empty($openid)){
            $agent_result =model('admin/Agent')->getDetailByOpenid($openid);
            if(empty($agent_result)){
                $this->redirect('/Agent/center');
            }
        }
        return $this->fetch();
    }
    
    public function to_login(){
        if(request()->isPost()){
            if(!model('agent/Ip')->checkIp()){
                $this->error('您今天已经输错5次密码，出于安全，请明天在试');
            }
            
            $account =input('post.phone','');
            $password =input('post.psd','');
            if(empty($account) || empty($password)){
                $this->error('请输入帐号或者密码');
            }
            
            $mer =model('admin/Agent');
            $back =$mer->login($account,$password);
            if(!empty($back)){
             $this->success('登录成功','Agent/center');
            }else{

                //记录ip
                model('Ip')->addIp();
                $this->error($mer->getError());
            }
            
            
            
        }
    }
 
    
    public function auth_wx_agent(){
        $result=[
            'shop_chensir_666'=>'http://shop.feioou.com/Authwx/wechatLogin'
        ];
        cache('auth_agent',json_encode($result));
    }
    
/*getagent
 * 
 */    
    public function wx_agent_info(){
        $code =input('code');
        $target =input('target');
        if(empty($target)){
            $this->error('illegal request');
        }
        $auth_agent =cache('auth_agent');
        $auth_agent= empty($auth_agent)?[]:json_decode($auth_agent,true);
        if(empty($auth_agent)){
            $this->error('no register');
        }
        
        if(!isset($auth_agent[$target])){
            $this->error('no register');
        }
        
        $app_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(empty($code)){
            $wechat =initWechat();
            $url     = $wechat->getOauthRedirect($app_url, '','snsapi_base');
            header("location: $url");
        }else{
            $url =$auth_agent[$target];
            $url ="$url?code=".$code;
            header("location: $url");
        }
    }
}
