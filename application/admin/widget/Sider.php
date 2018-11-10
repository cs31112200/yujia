<?php
namespace app\admin\widget;
use think\Config;
use think\View;

class Sider
{
    protected $view ="";
    public function __construct()
    {
        $this->view =new View();
    }
    
    public function left_menu(){
        $list =model('Menu')->getAllMenu();
        $this->view->assign('menu',$list);

        return  $this->view->fetch('sider/left_menu');
    }
    
    
    public function menu(){
        
        $result =model('Menu')->getMenu();
        $this->view->assign('sider',$result);
        
        return $this->view->fetch('sider/menu');
    }
    
    public function footer(){
        return $this->view->fetch('sider/footer');
    }
    
    public function logo(){
        return $this->view->fetch('sider/logo');
    }
    
    public function first_title(){
        $pid_session =getMenuFirstSession();
     //   echo $pid_session;
        $result =model('Menu')->getFirstMenu($pid_session);
       // print_r($result);exit;
        $this->view->assign('fmenu',$result);
        return $this->view->fetch('sider/first_title');
    }
    
    public function second_title(){
        
        $senlist =model('Menu')->getAllMenu();
       if(!is_admin()){
        $group_list =db('UserAuth')->where('admin_id='.UID)->select();
        if(!empty($group_list)){
            
    
        $ids =[];
        foreach($group_list as $k=>$v){
            $ids[]=$v['menu_id'];
        }
        if(!empty($senlist)){
            foreach($senlist as $k=>$v){
               if(!in_array($v['id'], $ids)){
                   unset($senlist[$k]);
               }else{
                   if(isset($v['childs']) && !empty($v['childs'])){
                       foreach($v['childs'] as $k1=>$v1){
                            if(!in_array($v1['id'], $ids)){
                                unset($senlist[$k]['childs'][$k1]);
                            }else{
                                if(isset($v1['childs']) && !empty($v1['childs'])){
                                     foreach($v1['childs'] as $k2=>$v2){
                                        if(!in_array($v2['id'], $ids)){
                                            unset($senlist[$k]['childs'][$k1]['childs'][$k2]);
                                        }
                                     }
                                }
                            }
                       }
                   }
               }
               
               
            }
        }
            }
       }
        
        $this->view->assign('menu',$senlist);
        return $this->view->fetch('sider/second_title');
        
    }
    
    public function header(){
        $name=  getSessionName();
        $user = session($name.'_auth');
        $this->view->assign('hello_name',$user['name']);
        return $this->view->fetch('sider/header');
    }

}
