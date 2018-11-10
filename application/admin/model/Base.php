<?php
namespace app\admin\model;

use think\Model;

class Base extends Model{
    
    protected $error_msg=''; 
    protected $the_table=''; 


/*初始化
 * 
 */    
   public function initialize() {
       parent::initialize();
        if(empty($this->table))
            $this->the_table =__classToStr($this->name);   
        else 
            $this->the_table =__classToStrs($this->table);   
   } 
    
    
    public function getTheTable(){
        return $this->the_table;
    }
           
    
/*__lists 方法的条件处理
 * 
 */    
    public function getSelect($data=null){
        return $data;
    }
/*处理list列表
 * 
 */    
    public function __formatList($list=null){
        return $list;
    }
    
/*处理编辑初始化
 * 
 */    
    public function __formatEdit($data=null){
        return $data;
    }
    
/*更新之前
 * 
 */    
    public function __my_before_update(&$data){
        return true;
    }
    
/*插入之前
 * 
 */
    
    public function __my_before_insert(&$data){
        return true;
    }
    
/*更新之后
 * 
 */     
    public function __my_after_update(){
        return true;
    }
    
/*插入之后
 * 
 */
    public function __my_after_insert(){
        return true;
    }


/*新增/更新操作,用于表单
 * @param array data 需要操作的数组
 * @param string validate 验证控制器名称
 * @param int is_edit 是否需要编辑验证
 * @param string msg 返回的信息
 */
    public function __msave($data,$validate='',$scance='',$msg=''){
        $pk =$this->getPk();
        //是否是更新标志
        $is_update=true;
        if(!isset($data[$pk]) || $data[$pk]<=0){
            unset($data[$pk]);$is_update=false;
        }
        //执行对数据操作
        $data_result=$is_update?$this->__my_before_update($data):$this->__my_before_insert($data);
        if(!$data_result){
            $back['msg']=$this->getError();
            $back['code']=0;
            return $back;
        }
        if(!empty($validate)){
            $validate =(!empty($scance))?$validate.".".$scance:$validate;
            $result =$this->validate($validate)->isUpdate($is_update)->save($data);
        }else{
            $result =$this->isUpdate($is_update)->save($data);
        }
        $idsss=$is_update?0:$this->getLastInsID();
     //   echo $this->getLastSql();exit;
//        $redis= initRedis();
//        $redis->lpush($this->getLastSql());
        if(false===$result){
            $back['msg']=$this->getError();
            $back['code']=0;
        }else{
            //执行对数据操作
            $is_update?$this->__my_after_update():$this->__my_after_insert();
            $back['msg']=($msg=='')?'操作成功':$msg;
            $back['code']=1;
            $back['id']=$idsss;
        }
        return $back;
    }

    
/*修改状态
 * @param mix id 需要修改的id
 * @param string action 操作的名称
 */    
    public function __changeStatus($id,$action,$spe=null){
        $pk =$this->getPk();
        $data[$pk]=['in',$id];
        
        if(!empty($spe)){
        $data =  array_merge($data,$spe);
        }
        
        $status =empty($this->status_name)?'status':$this->status_name;
        switch($action){
            case 'open':
                $data1[$status]=1;
                break;
            case 'close':
                $data1[$status]=0;
                break;
            case 'delete':
                $result =$this->where($data)->delete();
                return $result;
                break;
            default:
                return false;
        };
        
        $result =$this->where($data)->update($data1);
        return $result;
    }
    
/*设置错误
 * 
 */    
    
    public function setError($error_msg){
        $this->error=$error_msg;
    }
    
/*获取名称
 * 
 */
    public function getFieldValue($param,$field){
        $result =db($this->getTheTable())->where($param)->find();
        return  isset($result[$field])?$result[$field]:"";
    }
    
   
/*生成list结果
 * 
 */    
    public function generalResult($list,$count=0,$success_msg='获取成功',$error_msg='暂无结果'){
        $result['data']=$list;
        $result['code']=empty($list)?1:0;;
        $result['msg']=empty($list)?$error_msg:$success_msg;
        $result['count']=$count;
        return $result;
    }
    public function getFieldsValue($param,$fields){
        $result =db($this->getTheTable())->where($param)->find();
        return  isset($result[$fields])?$result[$fields]:'';
    }
}