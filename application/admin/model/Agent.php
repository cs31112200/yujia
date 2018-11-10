<?php
namespace app\admin\model;

use app\admin\model\Base;

class Agent extends Base{

//自动填充时间
    protected $autoWriteTimestamp = false;


    /*获取省
     *
     */
    public function GetProvinceList(){
        cache('province',null);
        $result =cache("province");
        if(empty($result)){
            $data['region_type']=1;
            $province =$this->where($data)->select();
            if(empty($province))
                return null;
            $result =[];
            foreach($province as $k=>$v){
                $result[$k]=$v->data;
            }
            cache('province',$result);
        }
        return $result;
    }

    /*获取市
     *
     */
    public function GetCityList($pid=""){
        if(empty($pid)) return [];
        //  $pid =$this->GetNameById($name);
        $str ="city".$pid;
        $result =cache($str);
        if(empty($result)){
            $data['parent_id']=$pid;
            $data['region_type']=2;
            $city =$this->where($data)->select();
            $result =[];
            foreach($city as $k=>$v){
                $result[$k]=$v->data;
            }
            cache($str,$result);
        };
        return $result;
    }

    /*获取区域
     *
     */
    public function GetAreaList($pid=""){
        if(empty($pid)) return [];
        //    $pid =$this->GetNameById($name,2);
        $str ="area".$pid;
        //exit;
        //  S($str,NULL);
        //    echo $name."  ".$pid;exit();

        $result =cache($str);
        if(empty($result)){
            $data['parent_id']=$pid;
            $data['region_type']=3;
            $area =$this->where($data)->select();
            $result =[];
            foreach($area as $k=>$v){
                $result[$k]=$v->data;
            }
            cache($str,$result);
        };
        return $result;
    }

    /*根据名称获取id
     *
     */
    public function GetNameById($name,$type=1){

        $str ='pcaname'.$name.$type;
        //   S($str,NULL);
        $names =cache($str);

        if(empty($names)){
            $data['region_name']=$name;
            $data['region_type']=$type;
            $result =$this->where($data)->find();
            if(empty($result)){echo $name;exit;}
            $result =$result->data;
            $names =$result['id'];
            cache($str,$names);
        };
        //   print_r($result);exit();
        return $names;
    }


    /*
     *
     *
     */
    public function getIdByName($id){
        $result =db($this->getTheTable())->find($id);
        return $result['region_name'];
    }




    /*获取市
     *
     */
    public function GetCityLists($areaId){
        $pid =$areaId;
        $str ="city".$pid;
        $result =cache($str);
        if(empty($result)){
            $data['parent_id']=$pid;
            $data['region_type']=2;
            $city =$this->where($data)->select();
            $result =[];
            foreach($city as $k=>$v){
                $result[$k]=$v->data;
            }
            cache($str,$result);
        };
        return $result;
    }

    /*获取区域
     *
     */
    public function GetAreaLists($areaId){
        $pid =$areaId;
        $str ="area".$pid;
        //exit;
        //  S($str,NULL);
        //    echo $name."  ".$pid;exit();

        $result =cache($str);
        if(empty($result)){
            $data['parent_id']=$pid;
            $data['region_type']=3;
            $area =$this->where($data)->select();
            $result =[];
            foreach($area as $k=>$v){
                $result[$k]=$v->data;
            }
            cache($str,$result);
        };
        return $result;
    }

    /*根据名称获取id
     *
     */
    public function GetNameByIds($name,$type=1){

        $str ='pcaname'.$name.$type;
        //   S($str,NULL);
        $names =cache($str);
        if(empty($names)){
            $data['areaName']=$name;
            $data['region_type']=$type;
            $result =$this->where($data)->find();
            if(empty($result)){echo $name;exit;}
            $result =$result->data;
            $names =$result['areaId'];
            cache($str,$names);
        };
        //   print_r($result);exit();
        return $names;
    }
    public function getAll(){
        $result =db($this->getTheTable())->where(1)->select();
        $return=[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$v['id']]=$v;
            }
        }
        return $return;
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
        if(isset($param['duty']) && !empty($param['duty'])){
            $map['a.duty']=$param['duty'];
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
        //   print_r($param);exit;
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
            foreach($list as $k=>$v){
//                $list[$k]['status']=$v['status']==1?"正常":"失效";
//                $list[$k]['tourl_type']=$v['tourl_type']==1?"内网":"外网";
//                $list[$k]['ad_space']=$v['ad_space']==1?"天气":"日志";
//                $list[$k]['is_force']=$v['is_force']==1?"是":"不是";
//
//                if(!empty($list[$k]['province'])){
//                    $area=db('Area')->where('id',$list[$k]['province'])->field('region_name')->find();
//                    $list[$k]['province']=$area['region_name'];
//                }
//                if(!empty($list[$k]['city'])){
//                    $area=db('Area')->where('id',$list[$k]['city'])->field('region_name')->find();
//                    $list[$k]['city']=$area['region_name'];
//                }
//                if(!empty($list[$k]['area'])){
//                    $area=db('Area')->where('id',$list[$k]['area'])->field('region_name')->find();
//                    $list[$k]['area']=$area['region_name'];
//                }
                if(!empty($list[$k]['create_time'])){
                    $list[$k]['create_time']=date('Y-m-d H:m:s',$list[$k]['create_time']);
                }
            }
            return $list;
        }
    }

    /*更新之前
     *
     */
    public function __my_before_update(&$data){
//        dump($data);exit();
//        $picture=db('Advert')->where('id',$data['id'])->find();
//        if(empty($data['img'])){
//            $data['img']=$picture['img'];
//        }
//        if(empty($data['url'])){
//            $data['url']=$data['down_url'];
//        }
//        unset($data['down_url']);
        return true;
    }

    /*插入之前
     *
     */

    public function __my_before_insert(&$data){
//        dump($data);exit();
//        if(isset($data['down_url'])){
//            unset($data['down_url']);
//        }
//        $data['status']=1;
        $data['create_time']=time();
        return TRUE;
    }

    public function getAgentname(){
        $map=[];
        $result = db($this->getTheTable())->where($map)->field('name')->group('name')->select();
        return $result;
    }

}