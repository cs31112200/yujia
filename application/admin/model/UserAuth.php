<?php
namespace app\admin\model;

use app\admin\model\Base;

class UserAuth extends Base{
    
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
        return true;
    }
        
    
    public function __my_before_update(&$data){
        return true;
    }
    
    public function transUserAuth($admin_id,$group_id){
        //首先进行删除
        
        $data['admin_id']=$admin_id;
        $this->where($data)->delete();
        
        //查找group
        $data1['group_id']=$group_id;
        $result =model('admin/MenuGroup')->getGroupMenuByGroupWithOne($group_id);
        if(empty($result)){
            return ;
        }
        
        $insert=[];
        foreach($result as $k=>$v){
            $insert[$k]['admin_id']=$admin_id;
            $insert[$k]['menu_id']=$v;
            $insert[$k]['create_time']=time();
        }
        
        db($this->getTheTable())->insertAll($insert);
        return true;
        
    }
    
    public function getUserMenu($admin_id){
        $data['admin_id']=$admin_id;
        $result =db($this->getTheTable())->where($data)->select();
        return $result;
    }
    
    public function getUserMenuByMenuid($admin_id){
        $result =$this->getUserMenu($admin_id);
        $return =[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[]=$v['menu_id'];
            }
        }
        return $return;
    }
/*存储访问权限
 * 
 */
    public function storeMenuGroup($ids,$admin_id){
        $this->where('admin_id ='.$admin_id)->delete();
        $data=[];
        foreach($ids as $k=>$v){
            $data[$k]['menu_id']=$v;
            $data[$k]['admin_id']=$admin_id;
            $data[$k]['create_time']=time();
        };
        return $this->insertAll($data);
    }
   public function getGroupMenuByadmin($admin_id){
       $data['a.admin_id']=$admin_id;
       $prex =config('database.prefix');
       $result =db($this->getTheTable())->alias('a')
               ->join($prex.'menu b','a.menu_id=b.id','LEFT')
               ->field('a.menu_id,a.admin_id,b.href,b.name')->where($data)->select();
       return $result;
   } 
}