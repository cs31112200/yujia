<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Sys extends Base
{
    
    public function menu_list(){
        $this->assign('_title','菜单列表');
        $this->setCookie('menu_list');
        $this->setParam('menu_list');
        return $this->fetch();
    }
    public function get_menu_list(){
          $model =model('Menu');
          $param1=$this->getParam('menu_list');
          $param =input();
          $param= array_merge($param,$param1);
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
    
    
    public function menu_add(){
        $model =model('Menu');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            
            //存储
            $data =input('post.');
            $data['create_time']=time();
            $back =$model->__msave($data,'Menu');
            $back['url']=($back['code']==0)?'':$this->getCookie('menu_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Menu',$theid,$is_insert,json_encode($data));
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
    public function menu_change_status(){
        
        
        $id = input('id/a');
        $type=input('type',0);
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $model =input('model');
        if(empty($model))
            $this->error('模型错误');


        
        $action =input('ope');
        if(empty($action))
            $this->error('请选择操作');
        if($type==0){
             $map['is_sys']=0;
        }else{
            $map=[];
        }
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
        if(empty($resultss)){
             $this->error('选择的数据不匹配',$_SERVER['HTTP_REFERER']);
        }
        
        $result =model($model)->__changeStatus($id,$action,$map);
        if($result){
            if($type==1){
                model('Config')->getAllValueToSave();
            }
            
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
    
    public function change_status(){
        
        $this->changeStatus('Config');
    }
    
    
/*配置管理
 * 
 */    
    
    public function setting(){
        $this->assign('_title','配置列表');
        $this->setCookie('setting');
        $this->setParam('setting');
        return $this->fetch();
         
         
    }
    
/*获取配置列表
 * 
 */    
    public function get_setting_list(){
          $model =model('Config');
          $param1=$this->getParam('setting');
          $param =input();
          $param= array_merge($param,$param1);
          $result  =$model->getListData($param);
          $result= $this->__doList($model,$result);
          return ($result);
    }
    
    
 /*配置添加/修改
  * 
  */   
    
    public function setting_add(){
        
        $model =model('Config');
        if(request()->isPost()){
            
            /***自定义验证区域*****/
            
            //存储
            $data =input('post.');
            $data['create_time']=time();
            $back =$model->__msave($data,'Config');
            $back['url']=($back['code']==0)?'':$this->getCookie('setting');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Config',$theid,$is_insert,json_encode($data));
            }
            
            $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        }else{
            $title =empty(input('id'))?'新增配置':'修改配置';
            return $this->__edits($model,input('id'),null,$title);
        }
    }
    
    
    public function test(){
            header("Content-type: application/vnd.ms-excel; charset=utf8");
            header("Content-Disposition: attachment; filename=test".date('Y-m-d',time()).".xls");

        echo "<style>td{border:1px solid #000;text-align:center;}table{width:100%;}</style>";
        $db =db();
        $sql ="SHOW TABLES";
        $tables =$db->query($sql);
        foreach ($tables as $i => $table) {
            $table =$table['Tables_in_green'];
          //  print_r($tables);exit;
           
             $tables = ($table);    
            $table = $this->backquote($table);                                  //为表名增加 ``
           
            
            $create_tabls =$db->query('select TABLE_COMMENT  from information_schema.tables Where table_name ="'.$tables.'"');
            $table_name =!empty($create_tabls)?$create_tabls[0]['TABLE_COMMENT']:"";
        //    print_r($create_tabls);exit;
            $tableRs = $db->query("SHOW FULL COLUMNS FROM {$table}");       //获取当前表的创建语句
            
            $table_str="<table style='border:1px solid #000;'><thead><tr><td colspan=6>".$table_name."</td></tr><tr><td>字段</td><td>是否是主键</td><td>类型/长度</td><td>是否为空</td><td>默认值</td><td>注释</td></tr></thead>";
            foreach($tableRs as $k=>$v){
                $is_key =($v['Key']=="PRI")?"是":"否";
                $table_str.="<tr><td>".$v['Field']."</td><td>".$is_key."</td><td>".$v['Type']."</td><td>".$v['Null']."</td><td>".$v['Default']."</td><td>".$v['Comment']."</td></tr>";
            }
            $table_str.="<table>";
              echo $table_str."<br><br>";
        //    print_r($tableRs);exit;
        }
      // echo $table_str;
       // print_r($result);
    }
    private function backquote($str) {
        return "`{$str}`";
    }    
    private function backquotes($str) {
        return '\"{$str}\"';
    } 
}
