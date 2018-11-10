<?php
namespace app\admin\model;

use app\admin\model\Base;

class Config extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*获取类型
 * 
 */
    public function getType(){
        return [
            '1'=>'普通文本',
            '2'=>'多文本',
            '3'=>'富文本',
            '4'=>'图片单图',
        ];
    }

/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        
        if(!empty($param['key_name']))
            $map['key_name']=['like',"%".$param['key_name']."%"];
        if(!empty($param['key']))
            $map['key']=$param['key'];
        
        return $map;
    }

    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;
        if(isset($param['key_name']))
            $map['key_name']=['like',"%".$param['key_name']."%"];
        if(isset($param['key']))
            $map['key']=$param['key'];
        
        
        
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    
    
    
    public function __formatList($list = null) {
        $type_arr=$this->getType();
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['values'] =($v['key_type']==4)?generalImg($v['the_value']):'';
                $list[$k]['key_type_name']=$type_arr[$v['key_type']];
                $list[$k]['the_value']=htmlspecialchars($v['the_value']);
       //         $list[$k]['the_value']=  htmlspecialchars((mb_strlen($v['the_value'],'UTF-8')>100)?mb_substr($v['the_value'], 0,100)."...":$v['the_value']);
            }
        }
     //   print_r($list);exit;
        return $list;
    }

    public function __formatEdit($data = null) {
        if(isset($data['key_type']) && $data['key_type']==4){
            $data['values']=generalImg($data['the_value']);
        }else{
            $data['values']='';
        }
        return $data;
    }
    
/*重新获取所有值并缓存
 * 
 */    
    public function getAllValueToSave(){
        
        $return =cache('sysconfig');
        if(empty($return)){

            $result =db($this->getTheTable())->where(1)->select();
            $return =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $return[$v['key']]=$v['the_value'];
                }
            }
            cache('sysconfig',$return);
        }
        return $return;
    }
    
    
/*更新之后
 * 
 */     
    public function __my_after_update(){
        cache('sysconfig',null);
        $this->getAllValueToSave();
        return true;
    } 
    
/*插入之后
 * 
 */
    public function __my_after_insert(){
        cache('sysconfig',null);
        $this->getAllValueToSave();
        return true;
    }
    
    
    
}