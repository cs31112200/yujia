<?php
namespace app\admin\controller;
use think\Controller;
use think\Config;


class Login extends Controller
{
    public function login()
    {
        return $this->fetch('login');
    }
    
/*验证登录
 * 
 */    
   public function checkLogin(){
       setSessionName('admin');
       $account = input('post.account');
       $pwd = input('post.password');
       $admin =model('Admin');
       $back =$admin->login($account,$pwd);
       if($back){
           $this->success('登录成功','Index/index');
       }else{
           $this->error($admin->getError());
       }
   } 
   
    public function returnBack($back){
        extract($back);
        $code =isset($code)?$code:0;
        $msg =isset($msg)?$msg:'';
        $url =isset($url)?$url:'';
        $data =isset($data)?$data:[];
        
        if($code==1){
            $this->success($msg,$url,$data);
        }else{
            $this->error($msg, $url, $data);
        }
    }   
    
/*退出
 * 
 */
    public function Out(){
        $result =model('Admin')->out();
        $back['code']=1;
        $back['msg']='退出成功';
        $back['url']=url('Index/index');
        $this->returnBack($back);
    }
    
    /*其他相关人员登录
 * 
 */    
    public function loginByAdmin(){
        
        $id = input('id');
        if(intval($id)<=0)
            $this->error ('非法访问', 'Index/index');
        
    	// 清空session   
        setSessionName('merchantAdmin');
        clearSession();
        
        
    	$timestamp = input('stamp');
    	$token = input('token');
        $type =empty(input('type'))?1:  input('type');
    	// 30秒过期
    	if (($timestamp + 30) < time()) {
    		$this->error('非法操作！链接失效！','Index/index');
    	}
    	if (md5($id . $timestamp . 'siFromAdmin') !== $token) {
    		$this->error('非法操作！签名无效！','Index/index');
    	}
        $back =model('Merchant')->LoginById($id);
        
        if($type==2)
            session('merchant_dl_auth_'.$id,1);
        $this->returnBack($back);
    }    
}
