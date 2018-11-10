<?php
namespace app\admin\model;

use app\admin\model\Base;

class Admin extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        if(!empty($param['account']))
            $map['account']=$param['account'];
        return $map;
    }
    
    
    public function getListData($param=null,$order=""){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['group_id'])){
            $map['a.group_id']=$param['group_id'];
        }
        if(isset($param['account'])){
            $map['a.account']=$param['account']; 
        }
        
        if(isset($param['username'])){
            $map['a.username']=['like',"%".$param['username']."%"]; 
        }
       // print_R($map);exit;
        $prex =config('database.prefix');
        $count =$this->where($map)->alias('a')->count();
        $sql =$this->where($map)->alias('a')
                ->join($prex.'group c','a.group_id=c.id','LEFT')
                ->field('c.name as group_name,a.id,a.username,a.is_charge,a.account,a.last_login_time,a.last_login_ip,a.is_root,a.status,a.station_name')
                ->order($order)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($account,$pwd){
        $data['account']=$account;
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
                ->where($data)->find();
        if(empty($result)){
            return $this->setError('帐号不存在');
        }
        //验证密码
        if(!$this->verifyPassNotSalt($pwd,$result['password'])){
            return $this->setError('您的密码有误');
        }
        if($result['status']!=1){
           return $this->setError('您已被管理员禁用');
        }
        //存储session
        $this->autoLogin($result);
        return true;
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function out(){
      
                clearSession();
                return true;
    }
    
    
    public function LoginById($id){
        $result =db($this->getTheTable())->where('id='.$id)->find();
        if(empty($result)){
            return $this->showMsg(0,'您的账号不存在');
        }
        $this->autoLogin($result);
        return $this->showMsg(1,'登录成功','','Index/index');
    }
    

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'id'             => $user['id'],
            'last_login_time' => time(),
            'last_login_ip'   => get_client_ip(1),
        );
        $this->update($data);
        /* 记录登录SESSION和COOKIES */
     //   $merchant_id =model('admin/Merchant')->getUseMerchant();
        $auth = array(
            'id'                  => $user['id'],
            'name'                =>$user['username'],
            'group_id'                =>$user['group_id'],
            'is_root'=>$user['is_root'],    
            'last_login_time'     =>time(),
        );
      //  print_r($auth);exit;
        
        settingSession($auth);
        
    }
/*验证密码
 * 
 */    
    protected  function verifyPass($need,$pass,$salt){
        $key =config('PS_MER_KEY');
        return(md5($need)===$pass)?true:false;
    }
    protected  function verifyPassNotSalt($need,$pass){
        return(md5($need)===$pass)?true:false;
    }

    
    
    
    
    public function __formatEdit($data = null) {
        return $data;
    }
    
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['last_login_time']=date('Y-m-d H:i:s',$v['last_login_time']);
                $list[$k]['last_login_ip']= long2ip($v['last_login_ip']);
            }
        }
        
        return $list;
    }

    
/*更新之前
 * 
 */    
    public function __my_before_update(&$data){
        $pasword =$repassword ="";
        if(isset($data['password'])){
            if(empty($data['password'])){
                unset($data['password']);
            }else{
                $pasword=$data['password'];
            }
        }
        if(isset($data['repassword'])){
            if(empty($data['repassword'])){
                unset($data['repassword']);
            }else{
                $repassword=$data['repassword'];
            }
        }
        if($repassword==$pasword && !empty($pasword)){
            
            if($repassword!=$pasword){
                $this->setError('两次密码不一致，请重新填写');
                return false;
            }
            unset($data['repassword']);
            $data['password']=md5($data['password']);
        }
        if(!isset($data['group_id']) || empty($data['group_id'])){
            $this->setError('请选择正确的分组');
            return false;
        }
        
        return true;
    }
    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        
       $data['create_time']=time();
       if($data['password']!=$data['repassword']){
           $this->setError ('两次密码不一致，请重新填写');
           return FALSE;
       }
       
       unset($data['repassword']);
       $data['password']=md5($data['password']);
       
        if(!isset($data['group_id']) || empty($data['group_id'])){
            $this->setError('请选择正确的分组');
            return false;
        }
        
       
        return TRUE;
    }
    
/*获取所有用户
 * 
 */ 
    public function getAllUser($except=[]){
        $data['is_root']=0;
        $data['status']=1;
        $result =db($this->getTheTable())->where($data)->select();
    //    print_r($result);
        $return =[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                if(!in_array($v['id'],$except)){
                        $return[]=$v;
                }
            }
        }
     //    print_r($return);exit;
        return $return;
    }
    
    public function getGroupCount($group_id){
        $data['group_id']=$group_id;
        $result =db($this->getTheTable())->where($data)->count();
        return empty($result)?0:$result;
    }
        
    
    public function getDetail($user_id){
        $prex =config('database.prefix');
        $map['a.id']=$user_id;
        $result =$this->alias('a')
                ->join($prex.'group c','a.group_id=c.id','LEFT')
                ->field('c.name as group_name,a.id,a.username,a.is_charge,a.account,a.last_login_time,a.last_login_ip,a.is_root,a.status')
               ->where($map)->find();
        return $result;
    }
    
    
}