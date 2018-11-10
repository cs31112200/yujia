<?php
namespace app\agent\model;

use app\admin\model\Base;

class AgentApply extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*邢增代理用户
 * 
 */
    public function addAgentApply($openid,$name,$tel,$province,$city,$area,$address){
        $data['openid']=$openid;
        $result =db($this->getTheTable())->where($data)->find();
        if(!empty($result)){
            $this->setError('您已经提交过申请资料,无需重复提交');
            return false;
        }
        //检验手机号
        if(!check_phone($tel)){
            $this->setError('手机号码有误，请重新输入');
            return false;
        }
        
        $data['name']=$name;
        $data['contact']=$tel;
        $data['province']=$province;
        $data['city']=$city;
        $data['area']=$area;
        $data['address']=$address;
        $data['create_time']=time();
        
        
        //$check_result =validate('AgentApply');
        $check_result =$this->validateData($data,'AgentApply');
        if($check_result===false){
            return false;
        }else{
            return $this->insert($data);
        }
    }
    
    public function getDetailByOpenid($openid){
        $data['openid']=$openid;
        $result =db($this->getTheTable())->where($data)->find();
        return empty($result)?[]:$result;
    }
  
}