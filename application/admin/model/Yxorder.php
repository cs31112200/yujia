<?php
namespace app\admin\model;

use app\admin\model\Base;

class Yxorder extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {

        $map =[];
        if(isset($param['type_name'])){
            $map['type_name']=$param['type_name'];
        }
        return $map;
    }

    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['name']) && !empty($param['name'])){
            $map['name']=$param['name'];
        }
        if(isset($param['contact']) && !empty($param['contact'])){
            $map['contact']=$param['contact'];
        }
        if(isset($param['status'])){
            $map['status']=$param['status'];
        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }

    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
          //      $list[$k]['type_imgs']=generalImg($v['type_img']);
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['status']= $list[$k]['status']==1?'已处理':'未处理';
            }
        }
        return $list;
    }
    public function __formatEdit($data = null) {
        if(!empty($data)){
      //      $data['type_imgs']=empty($data['type_img'])?'':generalImg($data['type_img']);
        }
        return $data;
    }
    
    
    
    
    public function __my_before_insert(&$data){

        return true;
    }
    
    public function __my_before_update(&$data){

        return true;
    }
    

    
}