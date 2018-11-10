<?php
namespace app\admin\model;

use app\admin\model\Base;

class EquipmentType extends Base{
    
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
                $list[$k]['type_img']=generalImg($v['type_img']);
                $list[$k]['status']=$list[$k]['status']==1?'正常':'失效';
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return $list;
    }
    public function __formatEdit($data = null) {
        if(!empty($data)){
            $data['type_img']=empty($data['type_img'])?'':generalImg($data['type_img']);
        }
        return $data;
    }
    
    
    
    
    public function __my_before_insert(&$data){
        
//        if(empty($data['bref']) ){
//            $this->setError('设备码不能为空');
//            return;
//        }
        
        $data['bref']=strtoupper($data['bref']);
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data){
//        if(empty($data['bref']) ){
//            $this->setError('设备码不能为空');
//            return;
//        }
        $picture=db('EquipmentType')->where('id',$data['id'])->find();
        if(empty($data['type_img'])){
            $data['type_img']=$picture['type_img'];
        }
        $data['bref']=strtoupper($data['bref']);
        return true;
    }
    
/*获取所有类型
 * 
 */
    public function getAllType(){
        $result =db($this->getTheTable())->where(1)->select();
        $return=[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$v['id']]=$v;
            }
            
        }
        return $return;
    }
    
}