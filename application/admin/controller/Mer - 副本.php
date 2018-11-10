<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Mer extends Base
{
    
    public function mer_list(){
        $this->assign('_title','企业列表');
        return $this->fetch();
    }
    public function get_mer_list(){
          $model =model('Merchant');
          $param =input();
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
    
    
    public function mer_add(){
        
        $model =model('Merchant');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            
            //存储
            $data =input('post.');
            $data['create_time']=time();
            $back =$model->__msave($data,'Merchant');
            $back['url']=($back['code']==0)?'':$this->getCookie();
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Merchant',$theid,$is_insert,json_encode($data));
            }
            
            
            $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        }else{
            $title =empty(input('id'))?'新增菜单':'修改菜单';
            return $this->__edits($model,input('id'),null,$title);
        }
    }
    
/*修改菜单/删除
 * 
 */    
    public function mer_change_status(){
        $this->changeStatus('Merchant');
    }

    
}
