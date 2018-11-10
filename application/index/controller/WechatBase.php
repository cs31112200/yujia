<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;
use think\Request;
/*基础基类
 * 
 */
class WechatBase extends Base
{
    protected $weObj = false;
    
    public function _initialize() {
       $this->weObj =  initWechat();
    }
    
    
    

/*跳转到错误页面
 * @param type 1 非法请求 2二维码未生效
 * 
 */    
    public function toErrorPage($type,$error_code=302){
       // echo "fail";exit;
        $this->redirect('Show/show_msg', ['type'=>$type], $error_code);
    }
    
/*检测openid
 * 
 */    
    public function checkUser(){
        $openid =session('openid');
        if(empty($openid)){
            $this->setPackAuth('check_login');

            $this->redirect('Login/wechatLogin');
        }
    }
    
    
/*微信授权不获取信息登录
 * 
 */    
    
    public function wechatLogin(){
        $ret = $this->weObj->getOauthAccessToken();
        if (!$ret) {
            $app_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url     = $this->weObj->getOauthRedirect($app_url, '','snsapi_base');
           
            header("location: $url");
            exit;
        } else {
            $openid = $ret['openid'];
            if(!model('WechatUser')->isExitUser($openid)){
                $this->wechatLoginGetDetail();
            }
            //记录session openid
            session('openid',$openid);
            exit;
        }
    }
    
/**
 * 微信授权自动登陆
 */
    public function wechatLoginGetDetail()
    {
       
        $ret = $this->weObj->getOauthAccessToken();
        if (!$ret) {
            $app_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $url     = $this->weObj->getOauthRedirect($app_url);
            header("location: $url");
            exit;
        } else {
            $openid = $ret['openid'];
            //记录session openid
            session('openid',$openid);
            
            // 当前用户的微信基本信息
            $info = $wechat->getOauthUserinfo($ret['access_token'], $openid);
            
            // 判断是否有返回openid
            if (empty($info) || empty($info['openid'])) {
                $this->toErrorPage(300);
            }
            $results =model('WechatUser')->addWechatUser($info);
            exit;
        }
    }


    
    
}
