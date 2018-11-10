<?php
namespace app\admin\model;

use app\admin\model\Base;

class CollectorType extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
      // $map['member_id']=$param['member_id'];
       // $map['equip_id']=$param['equip_id'];
        return $map;
    }

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

/*获取所有的产品价格
 * 
 */
    public function get_all_product_price(){
        $map=[];
        $result =db($this->getTheTable())->where($map)->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $result[$k]['merge_name']=$v['name']."(".$v['init_count']."控)";
            }
        }
        return $result;
        
    }
    
    public function get_all_by_key(){
        $result =$this->get_all_product_price();
        $return=[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$v['id']]=$v;
            }
        }
        return $return;
    }
    
    
/*获取设备的上级
 * 
 */    
    public function get_up_equip($init_count,$fee,$all_product=[]){
        $all_product =empty($all_product)?$this->get_all_product_price():$all_product;
        
        foreach($all_product as $k=>$v){
            if($v['init_count']<$init_count){
                unset($all_product[$k]);
            }else{
                if($v['fee']<$fee){
                    unset($all_product[$k]);
                }
            }
        }
        return $all_product;
    }

    
}