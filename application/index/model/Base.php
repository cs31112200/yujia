<?php
namespace app\index\model;

use think\Model;

class Base extends Model{
    
    
    
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
           
/*设置错误
 * 
 */    
    public function setError($error_msg){
        $this->error=$error_msg;
    }
    
  
}