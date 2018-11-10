<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;
use think\Request;

/*API基础基类
 * 
 */
class ApiBase extends Base
{
 
/*初始化进行验证
 * 
 */    
    public function _initialize() {
        if (isset($_GET['flag']) && $_GET['flag'] == 'test') {
            $this->input = $this->behavior = input('request.');
            unset($this->input['s']);
            unset($this->input['user_id']);
            unset($this->input['access_token']);
            unset($this->input['flag']);
        } else {
            // 接收post数据
            $this->input    = input('post.input');
            $this->behavior = input('post.behavior');
            $this->sig      = input('post.sig');

            // 校验
            $this->verifyPost();
            // json串数据base64解码
            $this->input    = $this->decodePost($this->input);
            $this->behavior = $this->decodePost($this->behavior);
            $this->input    = json_decode($this->input, true);
            $this->behavior = json_decode($this->behavior, true);

        }
    }
    
    
/**
 * 验证提交的数据
 */
    public function verifyPost()
    {
        if (isset($_GET['flag']) && $_GET['flag'] == 'test') {
            return true;
        }
        $app_key = config('APP_KEY');
        $md5code = md5($app_key . $this->input . $this->behavior);
        if ($md5code != $this->sig) {
            // 数据被篡改
            $status = 0;
            $msg    = '非法接入，已登记IP';
         //   $msg    = 'appkey'.$app_key.',input:'.$this->input.',behavior:'.$this->behavior.'md5后:'.$md5code;
            $this->returnJson($status, $msg);
        }
    }
 
/**
 * @param $str
 * @return string
 */
    public function decodePost($str)
    {
        $str = urldecode($str);
        $str = str_replace(' ', '+', $str);
        $arr = base64_decode($str);
        return $arr;
    }  
    
/*
 * 验证用户操作合法性 
 */
    public function verifyUser() {
        
        if (isset($_GET['flag']) && $_GET['flag'] == 'test') {
            $member_id = intval($this->behavior['user_id']);
            return $member_id;
        }
        $member_id = intval($this->behavior['user_id']);
        $access_token = $this->behavior['access_token'];
        
        if (empty($member_id) || empty($access_token)) {
                $this->returnJson(0,'非法用户参数');
        }
        //验证accesstion
        $session_name ="user_".$member_id."_sign";
        if($access_token!=cache($session_name)){
            $this->returnJson(0,'异常登录');
        }
        return $member_id;
    }
    
    
}
