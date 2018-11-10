<?php
namespace app\index\model;

use app\admin\model\Base;

class Member extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


    


/**
 * 登录指定用户
 * @param  integer $uid 用户ID
 * @return boolean      ture-登录成功，false-登录失败
 */
    public function login($account,$pwd,$imei,$type){
     //   jpush_send('864744030781486','test','content');
        $data['account']=$account;
        $result =db($this->getTheTable())->where($data)->find();
        if(empty($result)){
            $this->setError('帐号不存在');
            return false;
        }
        //验证密码
        if(!$this->verifyPass($pwd,$result['passwd'])){
            $this->setError('您的密码有误');
            return false;
        }
        if($result['status']!=1){
            $this->setError('您已被管理员禁用');
           return false;
        }
        $result['avator']= generalQnyImg($result['avator']);
        
        if(!empty($result['jpush_id']) && $imei!=$result['jpush_id']){
            $time =time();
            $equip_name =($type==1)?'android':'ios';

            jpush_send($result['id'],$result['jpush_id'], '帐号异常提醒', '您的设备于'.date('Y-m-d H:i',$time).'在'.$equip_name."登录，如果非本人操作，请尽快修改密码",3);
        }
        $result['jpush_id']=$imei;
        //存储session
        return $this->autoLogin($result);
    }

/**
 * 注销当前用户
 * @return void
 */
    public function out($member_id){
        $session_name ="user_".$member_id."_sign";
        cache($session_name,null);
        $data['id']=$member_id;
        $data['jpush_id']="";
        
        db($this->getTheTable())->update($data);
        
        return true;
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        
        //
        $datas['jpush_id']=$user['jpush_id'];
        $datas1['jpush_id']='';
        $this->where($datas)->update($datas1);
        
        
        
        $data = array(
            'id'             => $user['id'],
            'last_login_time' => time(),
            'last_login_ip'   => get_client_ip(1),
            'jpush_id'=>$user['jpush_id'],
        );
        $this->update($data);
        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'                  => $user['id'],
            'name'                =>$user['name'],
            'balance'             =>$user['balance'],
            'frz_balance'         =>$user['frz_balance'],
            'last_login_time'     =>time(),
            'jpush_id'=>$user['jpush_id'],
            'avator'=>$user['avator'],
        );
        $session_name ="user_".$user['id']."_sign";
        $key=data_auth_sign($auth);
        cache($session_name, $key,86400);
        $auth['access_token']=$key;
        return $auth;
        
    }
/*验证密码
 * 
 */    

    protected  function verifyPass($need,$pass){
      //  echo md5($need)."<br>".md5('shiji159');
        return(md5($need)===$pass)?true:false;
    }

    


    
/*更新之前
 * 
 */    
    public function __my_before_update(&$data){
     
        return $data;
    }
    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        
       $data['create_time']=time();
       if($data['passwd']!=$data['repeat_passwd']){
           $this->setError ('两次密码不一致，请重新填写');
           return FALSE;
       }
       
       unset($data['repeat_passwd']);
       $data['passwd']=md5($data['passwd']);
        return TRUE;
    }
    
/*判断电话号码是否存在
 * 
 */    
    
    public function isExitPhone($account){
        $data['account']=$account;
        $result =$this->where($data)->count();
        return $result;
    }
    
/*新密码
 * 
 */
    public function setNewPwd($account,$newpwd){
        $data['account']=$account;
        $data1['passwd']=md5($newpwd);
        return $this->where($data)->update($data1);
    }
    

    
}