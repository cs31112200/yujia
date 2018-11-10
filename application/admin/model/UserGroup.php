<?php
namespace app\admin\model;

use app\admin\model\Base;

class UserGroup extends Base{
    
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

        if(isset($param['group_id'])){
            $map['group_id']=$param['group_id']; 
        }
           
        $count =$this->where($map)->count();
        $prex =config('database.prefix');
        
        $sql =$this->alias('a')
                ->join($prex.'admin b','a.admin_id=b.id','LEFT')
                ->join($prex.'group c','a.group_id=c.id','LEFT')
                ->field('a.id,a.admin_id,a.group_id,b.username,b.account,c.name')
                ->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    
    public function __formatEdit($data = null) {

        return $data;
    }


    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        
       $data['create_time']=time();
        return TRUE;
    }
    
/*获取某个组选中的
 * 
 */    
   public function getGroupUserByGroup($group_id){
       $data['group_id']=$group_id;
       $result =db($this->getTheTable())->where($data)->select();
       return $result;
   } 
   
/*获取某个组的菜单并以普通数组返回
 * 
 */   
   public function getGroupUserByGroupWithOne($group_id){
       $result =$this->getGroupUserByGroup($group_id);
   //    print_r($result);exit;
       $return =[];
       if(!empty($result)){
           foreach($result as $k=>$v){
               $return[]=$v['admin_id'];
           }
       }
       return $return;
   }
   
/*存储访问权限
 * 
 */
    public function storeUserGroup($ids,$group_id){
        
        if(empty($ids) || empty($group_id)){
            return false;
        }
        
        
        
        
    //    $this->where('group_id ='.$group_id)->delete();
        $data=[];
        if(!empty($ids)){
        foreach($ids as $k=>$v){
            $data[$k]['admin_id']=$v;
            $data[$k]['group_id']=$group_id;
            $data[$k]['create_time']=time();
        };
        
             $this->insertAll($data);
        }
        return true;
        
    }
    
/*获取except的用户id
 * 
 */
    public function getExcept($group_id){
        
        $result =db($this->getTheTable())->where(1)->select();
        $return =[];
        if(!empty($result)){
             foreach($result as $k=>$v){
                 //if($v['group_id']==$group_id)
                     $return[]=$v['admin_id'];
             }
        }
        return $return;
    }
    

}