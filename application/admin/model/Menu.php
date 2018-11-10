<?php
namespace app\admin\model;

use app\admin\model\Base;

class Menu extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*筛选
 * 
 */
//    public function getSelect($param=null) {
//        $map =[];
//        $map['pid']=0;
//        if(isset($param['pid']) && $param['pid']>0)
//            $map['pid']=$param['pid'];
//
//        return $map;
//    }

    
    public function getListData($param=null){
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
    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        $data['id_name']="menu_".$data['id_name'];
        return true;
    }
        
    

/*获取菜单
 * 
 */    
   public function getMenu(){
	   $data['is_show']=1;
       $data['status']=1;
       $result =db($this->getTheTable())->where($data)->order('pid asc')->select();
       $return =[];
       if(!empty($result)){
           foreach($result as $k=>$v){
               if($v['pid']==0){
                   $return[$v['id']]=$v;
               }else{
                   if(isset($return[$v['pid']]))
                   $return[$v['pid']]['child'][]=$v;
               }
           }
       }
       return $return;
   }
   
   
   public function getFirstMenu($thepid=0){
       
       
       if(!is_admin()){
            $name=  getSessionName();
            $user = session($name.'_auth');
            $group_id =$user['group_id'];
            if(empty($group_id)){
                return [];
            }
            $group_list =db('UserAuth')->where('admin_id='.UID)->select();
            if(empty($group_list)){
                return [];
            }
            $ids =[];
            foreach($group_list as $k=>$v){
                $ids[]=$v['menu_id'];
            }
           
           $data['id']=['in',$ids];
       }
       $data['is_show']=1;
       $data['status']=1;
       $data['pid']=0;
       
       $result =db($this->getTheTable())->where($data)->order('pid asc,sort asc')->select();
       if(!empty($result)){
           foreach($result as $k=>$v){
               if($thepid==0){
                   if($k==0){
                        $result[$k]['is_choose']=1;
                   }else{
                       $result[$k]['is_choose']=0;
                   }
               }else if($thepid!=0){
                   if($thepid==$v['id']){
                       $result[$k]['is_choose']=1;
                   }else{
                       $result[$k]['is_choose']=0;
                   }
               }
           }
       }
   //    print_r($result);exit;
       return $result;
   }
   
   
   public function getSecondMenu($pid){

       $allMenu  =$this->getAllMenu();

       $return =[];
       foreach($allMenu as $k=>$v){
           if($v['id']==$pid){
               $return= $v['childs'];
           }
       }  
       return $return;
     //  return isset($allMenu[$pid])?$allMenu[$pid]:[];
   }
   
   
   public function getAllMenu($select=[]){
       $data['is_show']=1;
       $data['status']=1;
       $result =db($this->getTheTable())->where($data)->order('pid asc,sort desc')->select();
       
       $tree= (getTree($result,0,$select)); 
       
       return $tree;
        
   }
   
   
   public function getAllSelectMenu($select=[]){
       $result =$this->getAllMenu();
       if(!empty($select)){
           foreach($result as $k=>$v){
               
           }
       }
       
       
       
   }
    
}