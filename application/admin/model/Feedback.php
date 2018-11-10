<?php
namespace app\admin\model;

use app\admin\model\Base;

class Feedback extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;




    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

//        if(isset($param['account']) && !empty($param['account'])){
//            $map['account']=$param['account'];
//        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return $list;
    }
    public function __formatEdit($data = null) {
        return $data;
    }
    
    
    
    
    public function __my_before_insert(&$data){
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data){
        return true;
    }

    
}