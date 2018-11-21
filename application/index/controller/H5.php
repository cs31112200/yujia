<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;


class H5 extends Controller
{
    public function index(){
        $new_version =model('admin/Version')->getNewVersion();
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')||strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')){
            $result =$new_version['ios'];
            $result['thetype']=1;
            
        }else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')){
           $result =$new_version['android'];
           $result['thetype']=2;
        }else{
            $result =$new_version['android'];
            $result['thetype']=3;
        }
        $result['create_time']=date('Y-m-d H:i:s',$result['create_time']);
        
        
        //获取最新的版本
  //      $new_result =model('admin/Version')->getNewVersion();
        $android_img =$result['qrcode'];
       // $android_url =generalQrcode($android_url,false);
       // echo $android_url;exit;
        $this->assign('android_img',$android_img);
        $this->assign('app',$result);
        return $this->fetch();
    }

}
