<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Admin extends Base
{
/*后台用户列表
 * 
 */    
    public function index(){
        $this->assign('_title','后台用户列表');
        $this->setCookie('index');
        $param =input();
        cookie('index_list_p',$param);
        return $this->fetch();
    }
    
    public function getAdminList(){
          $model =model('Admin');
          $param1=cookie('index_list_p');
          $param =input();
          $param= array_merge($param,$param1);
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
    public function log_list(){
        $this->assign('_title','系统日志列表');
        $this->setCookie('log_list');
        $this->setParam('log_list');
        return $this->fetch();
    }
    
    public function getLogList(){
          $model =model('AdminLog');
          $param =input();
          $param1 =$this->getParam('log_list');
          $param = array_merge($param,$param1);
          $result  =$model->getListData($param,'id desc');
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
/*新增后台用户
 * 
 */    
    public function admin_add(){
        
        $model =model('Admin');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            
            //存储
            $data =input('post.');
            
            if(!isset($data['group_id']) || empty($data['group_id'])){
                $this->error('请选择分组，如未设置，请先设置分组');
            }
            
            $group_id =$data['group_id'];
            $admin_id =(isset($data['id']) && !empty($data['id']))?$data['id']:"";
            $back =$model->__msave($data,'Admin');
            $back['url']=($back['code']==0)?'':url('index');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Admin',$theid,$is_insert,json_encode($data));
                
                //这里查看分组的权限 并把用户权限设置进去
                $admin_id=empty($admin_id)?$back['id']:$admin_id;
                model('UserAuth')->transUserAuth($admin_id,$group_id);
            }
            
            $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        }else{
            $title =empty(input('id'))?'新增后台用户':'修改后台用户';
            return $this->__edits($model,input('id'),null,$title);
        }
    }
    
 /*分组列表
  * 
  */   
    public function group_list(){
        $this->assign('_title','分组列表');
        $param =input();
        cookie('group_list_p',$param);
        $this->setCookie('group_list');
        return $this->fetch();
    }
    
    public function getGroupList(){
          $model =model('Group');
          $param1=cookie('group_list_p');
          $param1 =empty($param1)?[]:$param1;
          
          $param =input();
          $param= array_merge($param,$param1);
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    } 
    
/*新增分组
 * 
 */    
    public function group_add(){
        
        $model =model('Group');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Group');
            $back['url']=($back['code']==0)?'':$this->getCookie('group_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Group',$theid,$is_insert,json_encode($data));
            }
            
            
            $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        }else{
            $title =empty(input('id'))?'新增分组':'修改分组';
            return $this->__edits($model,input('id'),null,$title);
        }
    }
    
/*新增分组
 * 
 */    
    public function menu_group(){
        
        $model =model('MenuGroup');
        if(request()->isPost()){
            
            $id =cookie('group_id');
            if(empty($id))
                $this->error('非操作');
            $rule =input('rules/a');
            $result =$model->storeMenuGroup($rule,$id);
          //  print_r($rule);
            if($result){
                $this->returnBack(1,'操作成功',$this->getCookie());
            }else{
                $this->returnBack(0,'操作失败');
            }

        }else{
            
            $id =input('id',0,'intval');
            if($id<=0)
                $this->error('非法访问');
            cookie('group_id',$id);
            $group_result =db('Group')->find($id);
            $this->assign('gl',$group_result);
            
            //获取
            $result = $model->getGroupMenuByGroupWithOne($id);
            $menu_result =model('Menu')->getAllMenu($result);
          //  print_r($menu_result);exit;
            $this->assign('_list',$menu_result);
            return $this->fetch('menu_group',['_title'=>'访问授权']);
        }
    }
    public function user_auth(){
        
        $model =model('UserAuth');
        if(request()->isPost()){
            
            $id =cookie('admin_id');
            if(empty($id))
                $this->error('非操作');
            $rule =input('rules/a');
            $result =$model->storeMenuGroup($rule,$id);
          //  print_r($rule);
            if($result){
                $this->returnBack(1,'操作成功','',$this->getCookie('index'));
            }else{
                $this->returnBack(0,'操作失败');
            }

        }else{
            
            $id =input('id',0,'intval');
            if($id<=0)
                $this->error('非法访问');
            cookie('admin_id',$id);
            
            $results =db('Admin')->find($id);
            $this->assign('user_name',$results['username']);
            
            
            //获取
            $result = $model->getUserMenuByMenuid($id);
            $menu_result =model('Menu')->getAllMenu($result);
           // print_r($result);exit;
            $this->assign('_list',$menu_result);
            return $this->fetch('user_auth',['_title'=>'访问授权']);
        }
    }
    
 /*关联成员
  * 
  */   
    public function user_group(){
        
        $model =model('UserGroup');
        if(request()->isPost()){
            
            $id =cookie('user_group_id');
            if(empty($id))
                $this->error('非操作');
          //  print_r(input('ids/a'));exit;
            $ids =input('ids/a');
            
            if(empty($ids)){
                $this->error('请选择一个人员上传');
            }
            
            $result =$model->storeUserGroup($ids,$id);
          //  print_r($rule);
            if($result){
                $this->success('操作成功',url('user_group','id='.$id));
            }else{
                $this->error('操作失败');
            }

        }else{
            
            $id =input('id',0,'intval');
            if($id<=0)
                $this->error('非法访问');
            cookie('user_group_id',$id);
            $group_result =db('Group')->find($id);
            if(empty($group_result)){
                $this->error('非法访问');
            }
            
            
            $this->assign('gl',$group_result);
            $this->setCookie();
            //获取
            return $this->fetch('user_group',['_title'=>'部门成员列表']);
        }
    }
    
    public function getUsergroupList(){
        
          $id =cookie('user_group_id');
            if(empty($id)){
                $this->error('非操作');
            }
        
          $model =model('Admin');
          $param['group_id']=$id;
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
    
/*
 * 
 */    
    public function admin_change_status(){
        
        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);
        
        
        $model="Admin";
        
        $action =input('post.ope');
        $thestatus=0;
        switch($action){
            case 'open':
                $thestatus=3;
                break;
            case 'close':
                 $thestatus=4;
                break;
            case 'delete':
                 $thestatus=5;
                break;
            default:
                $this->error('操作类型有误',$_SERVER['HTTP_REFERER']);
        };
        
        
        $datass['id'] =['in',$id];
        $resultss =db($model)->where($datass)->select();
        $thecount=count($resultss);
        if($thecount!=$id_count){
             $this->error('选择的数据不匹配');
        }
        
        if(!is_admin()){
            $map['merchant_id']=MERID;
        }
        
        
        $map['is_root']=0;
        $result =model($model)->__changeStatus($id,$action,$map);
        if($result){
            
            
            //记录修改状态、删除
            $insertall =[];
            foreach($resultss as $k=>$v){
                $insertall[$k]['admin_id']=UID;
                $insertall[$k]['themodel']=$model;
                $insertall[$k]['object_id']=$v['id'];
                $insertall[$k]['status']=$thestatus;
                $insertall[$k]['json_data']=json_encode($v);
                $insertall[$k]['create_time']=time();
                $insertall[$k]['content']=model('admin/AdminLog')->generalContent($thestatus,$model,$v['id'],json_encode($v));
            }
            model('admin/AdminLog')->insertAll($insertall);
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        }else 
            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
        
       
    }
    
    public function usergroup_change_status(){
         if(request()->isPost()){
             
             $id =input('id');
            if(empty($id)){
                $this->error('非法操作');
            }
            $admin_result =db('Admin')->find($id);
            if(empty($admin_result)){
                $this->error('非法id');
            }
            $data['group_id']=0;
            $data['is_charge']=2;
            $data1['id']=$id;
            db('Admin')->where($data1)->update($data);
            $this->success('修改成功');
              
         }
      
    }
    
    public function group_change_status(){
        $model="Group";
        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);

        $action =input('ope');
        $thestatus=0;
        switch($action){
            case 'open':
                $thestatus=3;
                break;
            case 'close':
                 $thestatus=4;
                break;
            case 'delete':
                 $thestatus=5;
                break;
            default:
                $this->error('操作类型有误');
        };
        
        $datass['id'] =['in',$id];
        $resultss =db($model)->where($datass)->select();
        $thecount=count($resultss);
        if($thecount!=$id_count){
             $this->error('选择的数据不匹配');
        }
        
        
        $result =model($model)->__changeStatus($id,$action);
        if($result){
            
            //记录修改状态、删除
            $insertall =[];
            foreach($resultss as $k=>$v){
                $insertall[$k]['admin_id']=UID;
                $insertall[$k]['themodel']=$model;
                $insertall[$k]['object_id']=$v['id'];
                $insertall[$k]['status']=$thestatus;
                $insertall[$k]['json_data']=json_encode($v);
                $insertall[$k]['create_time']=time();
                $insertall[$k]['content']=model('admin/AdminLog')->generalContent($thestatus,$model,$v['id'],json_encode($v));
            }
            
            model('admin/AdminLog')->insertAll($insertall);
            
            //修改group
            $datasss['group_id']=['in',$id];
            $data2['group_id']=0;
            $data2['is_charge']=2;
            db('Admin')->where($datasss)->update($data2);
            
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        }else 
            $this->error('修改失败');
    }
    

    public function set_zg(){
        if(request()->isPost()){
            $id =input('id');
            if(empty($id)){
                $this->error('非法操作');
            }
            $admin_result =db('Admin')->find($id);
            if(empty($admin_result)){
                $this->error('非法id');
            }
            if(empty($admin_result['group_id'])){
                $this->error('该人员未分组');
            }
            $data['group_id']=$admin_result['group_id'];
            $data1['is_charge']=2;
            db('Admin')->where($data)->update($data1);
            
            $data2['id']=$id;
            $data1['is_charge']=1;
            db('Admin')->where($data2)->update($data1);
            $this->success('修改成功');
            
            
            
            
            
        }
    }
    
    public function test(){

        $result=$this->checkAuthByMenu();
        echo $result;
    }
}
