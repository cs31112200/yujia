<?php
namespace app\admin\model;

use app\admin\model\Base;

class ProductPrice extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
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
        
        $init_arr =model('equipment')->get_equip_kong();
        $init_count =(isset($data['init_count']) && !empty($data['init_count']))?$data['init_count']:0;
        if(empty($init_count)){
            $this->setError('请选择设备控制数');
            return false;
        }
        if(!isset($init_arr[$init_count])){
            $this->setError('请选择正确的设备控制数');
            return false;
        }
        
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data){
        $init_arr =model('equipment')->get_equip_kong();
        $init_count =(isset($data['init_count']) && !empty($data['init_count']))?$data['init_count']:0;
        if(empty($init_count)){
            $this->setError('请选择设备控制数');
            return false;
        }
        if(!isset($init_arr[$init_count])){
            $this->setError('请选择正确的设备控制数');
            return false;
        }
        
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
    
/*根据product_id 获取是否最高级
 * 
 */    
    public function is_heighest($product_id,$all_product=[]){
        $all_product =empty($all_product)?$this->get_all_by_key():$all_product;
        
        
        
        
        
    }
    
}