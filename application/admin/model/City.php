<?php
namespace app\admin\model;

use app\admin\model\Base;

class City extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        if(isset($param['city_name'])){
            $map['city_name']=$param['city_name'];
        }
        return $map;
    }
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['city_name']) && !empty($param['city_name'])){
            $map['city_name']=$param['city_name'];
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
               $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
               $list[$k]['status']=$v['status']==1?"正常":"失效";
           }
       }
       return $list;
    }

    public function __formatEdit($data = null) {

        return $data;
    }
    
    
    public function __my_before_insert(&$data){
        
        if(empty($data['city_code']) ){
            $this->setError('城市标识不能为空');
            return;
        }
        
        $data['city_code']=strtoupper($data['city_code']);
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data){
        if(empty($data['city_code']) ){
            $this->setError('城市标识不能为空');
            return;
        }
        
        $data['city_code']=strtoupper($data['city_code']);
        return true;
    }
    
    
    public function getSelectCity(){
        $result =db($this->getTheTable())->where('status=1')->select();
        return $result;
    }
    
 /*获取可选城市 按标识排列
  * 
  */   
    public function getAllCity(){
        $result =db($this->getTheTable())->where('status=1')->select();
        $return =[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$v['city_code']][]=$v['city_name'];
            }
        }
        return $return;
    }
}