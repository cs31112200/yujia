<?php
namespace app\index\widget;
use think\Config;
use think\View;

class Sider
{
    protected $view ="";
    public function __construct()
    {
        $this->view =new View();
    }
    
    
    public function sider(){
        
        Config::load(APP_PATH.'menu.php');
        $result = Config::get('menu');
        $this->view->assign('sider',$result);
       
        return $this->view->fetch('sider/sider');
    }
    
    public function footer(){
        return $this->view->fetch('sider/footer');
    }
    
}
