<?php
namespace app\agent\model;

use app\admin\model\Base;

class WechatUser extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*新增微信用户
 * 
 */
    public function addWechatUser($wechat_arr){
        $openid =isset($wechat_arr['openid'])?$wechat_arr['openid']:"";
        if(empty($openid)){
            $this->setError('openid未获取到');
            return false;
        }
        if(!isset($wechat_arr['nickname']) || empty($wechat_arr['nickname'])){
            $this->setError('未获取到个人信息');
            return false;
        }
        if(!empty($this->getUserByOpenid($openid))){
            $this->setError('已有重复的openid');
            return false;
        }
        $wechat_arr['create_time']=time();
        return $this->insert($wechat_arr);
    }
    
/*根据openid 获取用户
 * 
 */    
    public function getUserByOpenid($openid){
        $data['openid']=$openid;
        $result =db($this->getTheTable())->where($data)->find();
        if(empty($result)){
            return [];
        }
        return $result;
    }
    
    
}