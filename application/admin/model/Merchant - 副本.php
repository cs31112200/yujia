<?php
namespace app\admin\model;

use app\admin\model\Base;

class Merchant extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



    
    public function getListData($param=null,$order=''){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;
        $map['pid']=0;
        if(isset($param['pid']) && $param['pid']>0){
            $map['pid']=$param['pid'];
        }
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }

    public function __formatEdit($data = null) {
        return $data;
    }
    

    public function __formatList($list = null) {
        return $list;
    }
    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
       $data['merchant_id']=MERID;
        return true;
    }
        
    
    public function __my_before_update(&$data){
        $data['merchant_id']=MERID;
        return true;
    }
        
    

}