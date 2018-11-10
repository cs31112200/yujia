<?php
namespace app\admin\model;

use app\admin\model\Base;

class AdminGroup extends Base{
    
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
   public function getGroupMenuByGroup($group_id){
       $data['group_id']=$group_id;
       $result =db($this->getTheTable())->where($data)->select();
       return $result;
   } 
   
/*获取某个组的菜单并以普通数组返回
 * 
 */   
   public function getGroupMenuByGroupWithOne($group_id){
       $result =$this->getGroupMenuByGroup($group_id);
   //    print_r($result);exit;
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
    public function storeMenuGroup($ids,$group_id){
        $this->where('group_id ='.$group_id)->delete();
        $data=[];
        foreach($ids as $k=>$v){
            $data[$k]['menu_id']=$v;
            $data[$k]['group_id']=$group_id;
            $data[$k]['create_time']=time();
        };
        
        return $this->insertAll($data);
        
    }
    
}