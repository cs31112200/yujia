<?php
namespace app\index\model;

use app\admin\model\Base;

class PhoneVerify extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*验证码验证
 * 
 */
    public function checkVerify($tepehone,$verify,$type){
        $data['telephone']=$tepehone;
        $data['type']=$type;
        $result =db($this->getTheTable())->where($data)->order('id desc')->find();
        if(empty($result)){
            $this->setError('无效验证码');
            return false;
        };
//        
//        if($result['type']!=$type){
//            $this->setError('无效验证码');
//            return false;
//        }
        
        if($result['status']==1){
            $this->setError('无效验证码');
            return false;
        };
        
        $now =time();
        if($result['create_time']<$now-300){
            $this->setError('验证码超时');
            return false;
        };
        
        if($result['verify_code']!=$verify){
            $this->setError('验证码输入有误');
            return false;
        };
        
        
//        $result['status']=1;
//        $this->__msave($result);
        
        return true;
    }
    
    
/*修改短信验证码状态
 * 
 */    
    public function changePhoneVerify($tepehone,$type){
        $data['telephone']=$tepehone;
        $data['type']=$type;
        $data1['status']=1;
        $result=$this->where($data)->update($data1);
        return true;
    }
    
/*新增验证码
 * 
 */
    public function addVerify($telephone,$verify_code,$type){
        $data['telephone']=$telephone;
        $data['verify_code']=$verify_code;
        $data['type']=$type;
        $data['create_time']=time();
        return $this->insert($data);
    }
    
}