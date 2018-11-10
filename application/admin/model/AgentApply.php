<?php
namespace app\admin\model;

use app\admin\model\Base;

class AgentApply extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];

        if(isset($param['province']) && !empty($param['province'])){
            $map['province']=$param['province'];
        }
        if(isset($param['city'])&& !empty($param['city'])){
            $map['city']=$param['city'];
        }
        if(isset($param['area'])&& !empty($param['area'])){
            $map['area']=$param['area'];
        }
        
        if(isset($param['name'])){
            $map['name']=['like',"%".$param['name']."%"];
        }
        
        if(isset($param['contact'])){
            $map['contact']=$param['contact'];
        }
        
        if(isset($param['duty'])){
            $map['duty']=$param['duty'];
        }
        
        return $map;
    }

    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['name']) && !empty($param['name'])){
            $map['a.name']=['like',"%".$param['name']."%"];;
        }
        if(isset($param['contact']) && !empty($param['contact'])){
            $map['a.contact']=$param['contact'];
        }
        if(isset($param['province']) && !empty($param['province'])){
            $map['a.province']=$param['province'];
        }
        if(isset($param['city']) && !empty($param['city'])){
            $map['a.city']=$param['city'];
        }
        if(isset($param['area']) && !empty($param['area'])){
            $map['a.area']=$param['area'];
        }
         $prex =config('database.prefix');
        $count =$this->alias('a')->where($map)->count();
        $sql =$this->alias('a')
                ->join($prex.'area b','a.province=b.id','left')
                ->join($prex.'area c','a.city=c.id','left')
                ->join($prex.'area d','a.area=d.id','left')
                ->field('a.*,b.region_name as province_name,c.region_name as city_name,d.region_name as area_name')
                ->where($map)->order('id desc')->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
       if(!empty($list)){
           $color_arr =$this->get_status_color();
           $name_arr =$this->get_status_name();
           foreach($list as $k=>$v){
               $list[$k]['status_color']=$color_arr[$v['status']];
               $list[$k]['status_name']=$name_arr[$v['status']];
               if(!empty($list[$k]['create_time'])){
                   $list[$k]['create_time']=date('Y-m-d H:m:s',$list[$k]['create_time']);
               }
           }
       }
       return $list;
    }

    public function __formatEdit($data = null) {
        
        if(!empty($data)){
           $color_arr =$this->get_status_color();
           $name_arr =$this->get_status_name();
           $data['status_color']=$color_arr[$data['status']];
           $data['status_name']=$name_arr[$data['status']];
        }
        
        return $data;
    }
    
    
    public function __my_before_insert(&$data){
        
        if(!isset($data['contact']) || !check_phone($data['contact'])){
            $this->setError('联系号码格式不正确');
            return false;
        }
        
        $data['create_time']=time();
        return true;
    }
    
    public function __my_before_update(&$data){
        
        if(!isset($data['contact']) || !check_phone($data['contact'])){
            $this->setError('联系号码格式不正确');
            return false;
        }
        $data['update_time']=time();
        
        return true;
    }
    
/*根据openid获取用户信息
 * 
 */
    public function getDetailByOpenid($openid){
        $data['openid']=$openid;
        $result =db($this->getTheTable())->where($data)->find();
        if(!empty($result)){
            $allcity =model('admin/Area')->getAll();
            $result['province']=$allcity[$result['province']]['region_name'];
            $result['city']=$allcity[$result['city']]['region_name'];
            $result['area']=$allcity[$result['area']]['region_name'];
        }
        return empty($result)?[]:$result;
    }
    
    public function get_status_color(){
        return [
            1=>'grey',
            2=>'blue',
            3=>'red'
        ];
    }
    
    public function get_status_name(){
        return [
            1=>'待审核',
            2=>'审核通过',
            3=>'审核失败'
        ];
    }
    
/*
 * 
 */
    public function get_detail($id){
        $map['a.id']=$id;
        $prex =config('database.prefix');
        $result =$this->alias('a')
                ->join($prex.'area b','a.province=b.id','left')
                ->join($prex.'area c','a.city=c.id','left')
                ->join($prex.'area d','a.area=d.id','left')
                ->field('a.*,b.region_name as province_name,c.region_name as city_name,d.region_name as area_name')
                ->where($map)->find();
        return $result;
    }
    

}