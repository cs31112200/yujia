<?php
namespace app\agent\controller;
use think\Db;
use think\Config;
use think\Request;
use think\Controller;

class Agent extends WechatBase
{
    
/*
 * 
 */ 
    public function apply(){
        $openid=$this->openid;
        
        //查看申请状态
        $apply_result=model('AgentApply')->getDetailByOpenid($openid);
        if(!empty($apply_result)){
            if($apply_result['status']==1){
                $url =url('/Agent/show_msg',['type'=>1,'msg'=>'申请成功，请耐心等待结果']);
                $this->redirect($url);
            }else if($apply_result['status']==2){
                $url =url('/Agent/center');
                $this->redirect($url);
            }else if($apply_result['status']==3){
                $url =url('/Agent/show_msg',['type'=>2,'msg'=>'申请失败，'.$apply_result['last_msg']]);
                $this->redirect($url);
            }
        }
        
        //查找是否已经存在经销商
        $agent_result =model('admin/Agent')->getDetailByOpenid($openid);
        if(!empty($agent_result)){
            $url =url('/Agent/center');
            $this->redirect($url);
        }
        
        
        $result =model('WechatUser')->getUserByOpenid($openid);
        $this->assign('app',$result);
        
        
        
        return $this->fetch();
    }
    
    
/*申请代理接口
 * 
 */    
    public function to_apply(){
        if(request()->IsPost()){
            $model_agent_apply =model('AgentApply');
            $openid =$this->openid;
            $username =input('post.username');
            $phone =input('post.phone');
            $province =input('post.province');
            $city =input('post.city');
            $area =input('post.area');
            $addr =input('post.addr');
            $add_result =$model_agent_apply->addAgentApply($openid,$username,$phone,$province,$city,$area,$addr);
            if($add_result===false){
                $this->error($model_agent_apply->getError());
            }
            $url =url('/Agent/show_msg',['type'=>1,'msg'=>'申请成功，请耐心等待结果']);
            $this->success('申请成功',$url);
        }
    }
    
    public function show_msg(){
        $msg =input('param.msg','');
        $type =input('param.type','3');
        
        $this->assign('msg',$msg);
        $this->assign('type',$type);
        return $this->fetch();
    }
    
/*个人中心
 * 
 */    
    public function center(){
        $agent_result =$this->checkAgent();
        $this->assign('app',$agent_result);
        return $this->fetch();
    }
    
/*我的名片
 * 
 */
    public function mycard(){
        $agent_result =$this->checkAgent();
        $this->assign('app',$agent_result);
        return $this->fetch();
    }
    
/*登陆
 * 
 */   
    public function loout(){
        $agent_result =$this->checkAgent();
        session('openid',null);
        $this->success('登出成功');
    }
    
/*我的客户
 * 
 */    
    public function myclient(){
        $agent_result =$this->checkAgent();
        $type =input('type',0);
        $page =input('page',1);
        $content =input('content','');
        $page_size=3;
        $this->assign('app',$agent_result);
        
        //获取数据
        $agent_id =$agent_result['id'];
        $model_member_equip =model('index/MemberEquip');
        $all_client =$model_member_equip->getAgentClient($agent_id,$type,$content);
        $all_client = array_values($all_client);
        
        //分页
        $first =($page-1)*$page_size;
        $last =$page*$page_size-1;
        foreach($all_client as $k=>$v){
            if($k<$first || $k>$last){
                unset($all_client[$k]);
            }
        }
        cookie('client_type',$type);
        cookie('client_content',$content);
        $this->assign('_list',$all_client);
        return $this->fetch();
    }
    
    public function get_my_client_list(){
        if(request()->isPost()){
            $page =input('page',2);
            $page_size=3;
            $type= cookie('client_type');
            $content= cookie('client_content');
            $agent_result =$this->checkAgent();
            
            //获取数据
            $agent_id =$agent_result['id'];
            $model_member_equip =model('index/MemberEquip');
            $all_client =$model_member_equip->getAgentClient($agent_id,$type,$content);
            
            //分页
            $first =($page-1)*$page_size;
            $last =$page*$page_size-1;
            foreach($all_client as $k=>$v){
                if($k<$first || $k>$last){
                    unset($all_client[$k]);
                }
            }
            $all_client= !empty($all_client)?array_values($all_client):[];
            $this->success('获取成功', '', $all_client);

        }
    }
    
}
