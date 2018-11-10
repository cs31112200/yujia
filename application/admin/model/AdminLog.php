<?php
namespace app\admin\model;

use app\admin\model\Base;

class AdminLog extends Base{
    
/*新增log
 * 
 */
    public function addLog($admin_id,$themodel,$object_id,$status,$datas=""){
        $data['admin_id']=$admin_id;
        
        $data['themodel']=$themodel;
        $data['object_id']=$object_id;
        $data['status']=$status;
        $data['json_data']=$datas;
        $data['create_time']=time();
        $data['content']=$this->generalContent($status,$themodel,$object_id,$datas);
        return $this->insert($data);
    }
    
    
    public function getListData($param=null,$order=""){
        $map =[];
        $data =$param;
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['username']) && !empty($param['username'])){
            $data1['username']=['like',"%".$param['username']."%"];
            $result =db('Admin')->where($data1)->select();
        
            $ids =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $ids[]=$v['id'];
                }
                
            }
            $ids =empty($ids)?"":$ids;
            $map['a.admin_id']=['in',$ids];
        }
        
        if(isset($data['themodel']) && !empty($data['themodel'])){
            $map['a.themodel']=$data['themodel'];
        }
                
        
        if(isset($data['status']) && !empty($data['status'])){
            $map['a.status']=$data['status'];
        }
        $count =$this->alias('a')->where($map)->count();
        $prex =config('database.prefix');
        
        $sql =$this->alias('a')
                ->join($prex.'admin b','a.admin_id=b.id','left')
                ->field('a.id,a.admin_id,a.create_time,a.content,a.object_id,a.themodel,a.status,a.json_data,b.username')
                ->where($map)->order($order)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    
    
    public function addSqlLog($admin_id,$status,$content){
        $data['admin_id']=$admin_id;
        
        $data['themodel']='SQL';
        $data['object_id']=0;
        $data['status']=$status;
        $data['json_data']="";
        $data['create_time']=time();
        $data['content']=$content;
        return $this->insert($data);
    }
    
    
    public function getAllModel(){
        return [
            'Menu'=>'菜单管理',
            'Config'=>'配置管理',
            'SQL'=>'系统备份',
            'Admin'=>'后台用户人员',
            'MenuGroup'=>'分组权限',
            'UserGroup'=>'用户分组',
            'Group'=>'后台分组',
            'Advert'=>'广告管理',
            'Agent'=>'代理管理',
            'City'=>'城市管理',
            'Version'=>'版本管理',
            'PoolType'=>'水池类型管理',
            'Question'=>'常见问题管理',
            'Member'=>'客户管理',
            'EquipCollector'=>'采集器管理',
            'Yxorder'=>'意向用户管理',
            'EquipmentType'=>'设备类型管理',
            'ProductPrice'=>'产品价格管理',
            'CollectorType'=>'采集器管理',
            'Equipment'=>'设备管理',
            'Finance'=>'财务管理',
            'Feedback'=>'反馈管理',
        ];
    }
    
    public function getAllStatus(){
        return [
            1=>'新增',
            2=>'修改',
            3=>'启用',
            4=>'禁用',
            5=>'删除',
            6=>'一键备份',
            7=>'备份一键下载',
            8=>'还原',
        ];
    }
    
    public function getTheStatus($status){
        $allstatus =$this->getAllStatus();
        return $allstatus[$status];
    }
    
    
    
    public function getTheModel($themodel){
        $allmodel =$this->getAllModel();
        return $allmodel[$themodel];
    }
    
    
    public function generalContent($status,$themodel,$object_id,$data){
        $allStatus =$this->getAllStatus();
        $ope =$allStatus[$status];

        $model_name =$this->getTheModel($themodel);
        $content =$ope."了".$model_name.",id为".$object_id.",操作的json数据为:".$data;
        return $content;
    }
    
    
    
    
    public function getSelect($data=""){
        
        $map=[];
        if(!empty($data['username'])){
            $data1['username']=['like',"%".$data['username']."%"];
            $result =db('Admin')->where($data1)->select();
        
            $ids =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $ids[]=$v['id'];
                }
                
            }
            $ids =empty($ids)?"":$ids;
            $map['admin_id']=['in',$ids];
        }
        
        if(!empty($data['themodel'])){
            $map['themodel']=$data['themodel'];
        }
        
        
        if(!empty($data['status'])){
            $map['status']=$data['status'];
        }
        return $map;
//        
//        
//        if(!empty($data['create_time'])){
//            $map['create_time']=$data['status'];
//        }
//        
        
    }
    
    public function __formatList($list = null) {
        if(!empty($list)){
            $models =$this->getAllModel();
            $statuss =$this->getAllStatus();
            foreach($list as $k=>$v){
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
             //   $list[$k]['username']=model('admin/Admin')->getFieldValue(['id'=>$v['admin_id']],'username');
                $list[$k]['model_name']=$models[$v['themodel']];
                $list[$k]['status_name']=$statuss[$v['status']];
            }
        }
        return $list;
    }
    
    
}