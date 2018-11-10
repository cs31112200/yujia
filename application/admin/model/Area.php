<?php
namespace app\admin\model;

use app\admin\model\Base;

class Area extends Base{

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



}