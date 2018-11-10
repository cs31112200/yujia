<?php
namespace app\admin\model;

use app\admin\model\Base;

class Group extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        if(!empty($param['name']))
            $map['name']=$param['name'];
        return $map;
    }
    

    
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['name'])){
            $map['name']=$param['name'];
        }
     //   print_r($param);exit;
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    
    
    
    public function __formatEdit($data = null) {

        return $data;
    }
    
    public function __formatList($list = null) {
        if(!empty($list)){
            $model=model('admin/Admin');
            foreach($list as $k=>$v){
                $list[$k]['people_count']=$model->getGroupCount($v['id']);
            }
            
        }
        return $list;
    }


    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        if(!is_admin()){
            $data['merchant_id']=MERID;
        }
       $data['create_time']=time();
        return TRUE;
    }
    public function __my_before_update(&$data){
        if(!is_admin()){
            $data['merchant_id']=MERID;
        }
       $data['create_time']=time();
        return TRUE;
    }
    
    public function getGroupList($merchant_id=""){
        $result =db($this->getTheTable())->where(1)->select();
        return $result;
    }

    
    
}