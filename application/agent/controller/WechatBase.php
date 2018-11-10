<?php
namespace app\agent\controller;
use think\Controller;
use think\Config;
use think\Db;
use think\Request;

/*微信基础基类
 * 
 */
class WechatBase extends Base
{
 protected $weObj = false;
 protected $openid ="";
 /*初始化
 * 
 */    
    public function _initialize() {
        $this->weObj =  initWechat();
        $openid=session('openid');
     //   $openid="ofUd7t4Myw5XZGKqLZMkT1M0FDUs";
        if(empty($openid)){
            $this->wechatLogin();
        }else{
            $this->openid=$openid;
        }
    }
    
/*跳转到错误页面
 * @param type 1 非法请求 2二维码未生效
 * 
 */    
    public function toErrorPage($error_code,$error_msg=''){
        $this->redirect('Result/show_msg',$error_code,$error_msg);
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
            
            if(empty(model('WechatUser')->getUserByOpenid($openid))){
                $info = $this->weObj->getOauthUserinfo($ret['access_token'], $openid);
                if(empty($info)){
                    $this->wechatLoginGetDetail();
                    exit;
                }else{
                    $results =model('WechatUser')->addWechatUser($info);
                }
            }
            //记录session openid
            session('openid',$openid);
            $this->openid=$openid;
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
     //       echo $url;exit;
            header("location: $url");
           
            exit;
        } else {
        }
    }
    
    public function checkAgent(){
        $openid =session('openid');
        $agent_result =model('admin/Agent')->getDetailByOpenid($openid);
        if(empty($agent_result)){
            $this->redirect('/Login/login');
        }
        return $agent_result;
    }

}
