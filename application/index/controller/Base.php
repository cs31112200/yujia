<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;
use think\exception\HttpResponseException;
use think\Request;
/*基础基类
 * 
 */
class Base extends Controller
{
    
    public function _initialize() {
    }
 
/*返回json
 * 
 */
    public function returnJson($code,$msg){
        $back=[
            'code'=>$code,
            'msg'=>$msg,
        ];
         throw new HttpResponseException(json($back)); 
    }
    
    public function returnJsonData($code,$msg,$data){
        $back=[
            'code'=>$code,
            'msg'=>$msg,
            'data'=>$data
        ];
        throw new HttpResponseException(json($back)); 
    }
    
/*返回消息
 * @param int code 状态码  1，0
 * @param string msg 消息
 * @param array data 数据
 * @param string url 地址
 */    
    public function returnBack($code,$msg='',$data='',$url=''){
        if($code==1){
            $this->success($msg,$url,$data);
        }else{
            $this->error($msg, $url, $data);
        }
    }
    
/*验证
 * 
 */
    public function checkAuth(){
        $token =input('token');
        if(empty($token))
            $this->error('非法请求');
        $check_token =session('__token__');
        if($check_token!=$token)
            $this->error('非法请求');
    }
    
/*验证参数是否为空
 * 
 */    
    public function __checkParam($param,$data){
        $param =  explode(',', $param);
        foreach($param as $k=>$v){
            if(!isset($data[$v]) ||(strlen($data[$v])==0 && !is_array($data[$v])) )
                 $this->returnJson (0, '缺少必要参数:' . $v);
            if(!isset($data[$v]) || (is_array($data[$v]) && count($data[$v])))
                 $this->returnJson (0, '缺少必要参数:' . $v);
        }
    }
    
    public function __checkParams($param,$data){
        $param =  explode(',', $param);
        foreach($param as $k=>$v){
            if(!isset($data[$v]) ||(strlen($data[$v])==0 && !is_array($data[$v])) )
                 $this->returnJson (-1, '缺少必要参数:' . $v);
            if(!isset($data[$v]) || (is_array($data[$v]) && count($data[$v])))
                 $this->returnJson (-1, '缺少必要参数:' . $v);
        }
    }
    
    
/*页面验证
 * 
 */    
    public function checkPageAuth($name){
        $check_token =cookie($name.'_check_token');
        $token =session($name.'_page_verify');
        if($check_token!=$token || empty($check_token) || empty($token))
            $this->error('验证失败');
        return true;
    }
    
/*设置页面验证
 * 
 */
    public function setPackAuth($name){
        $token =  Request::instance()->token($name.'_page_verify', 'md5');
        cookie($name.'_check_token',$token);
        return true;
    }
    
}
