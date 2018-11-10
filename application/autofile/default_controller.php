<?php
namespace app\[module]\controller;
use think\Db;
use think\Config;
use think\Request;


class [controller_name] extends Base
{
    public function __construct(Request $request = null) {
        parent::__construct($request);
        $this->default_model =model('[model_name]');
    }
    
    public function [front_name]_list(){
        $map  =$this->default_model->getSelect(input());
        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        return $this->__lists($this->default_model,null,'菜单列表',$map);
    }
    
    public function [front_name]_add(){
        
        $model =model('Menu');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            
            //存储
            $data =input('post.');
            $data['create_time']=time();
            $back =$this->default_model->__msave($data,'[validate_name]');
            $back['url']=($back['code']==0)?'':$this->getCookie();
            $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        }else{
            $title =empty(input('id'))?'新增':'修改';
            return $this->__edits($this->default_model,input('id'),null,$title);
        }
    }
    
/*修改菜单/删除
 * 
 */    
    public function change_tatus(){
        
        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $model =input('model_name');
        if(empty($model))
            $this->error('模型错误');

        
        $action =input('action');
        if(empty($action))
            $this->error('请选择操作');
        $map['is_sys']=0;
        $result =model($model)->__changeStatus($id,$action,$map);
        if($result)
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        else 
            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
        
    }

}
