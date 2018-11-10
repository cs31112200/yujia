<?php
namespace app\admin\model;

use app\admin\model\Base;

class EquipSaleLog extends Base{

//自动填充时间    
    protected $autoWriteTimestamp = false;

    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['equip_code']) && !empty($param['equip_code'])){
            $data['equip_code']=$param['equip_code'];
            $result =db('Equipment')->where($data)->select();
            $ids =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $ids[]=$v['id'];
                }

            }
            $ids =empty($ids)?"":$ids;
            $map['a.equip_id']=['in',$ids];
        }

        if(isset($param['name']) && !empty($param['name'])){
            $data1['name']=['like',"%".$param['name']."%"];
            $result =db('Agent')->where($data1)->select();

            $ids =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $ids[]=$v['id'];
                }

            }
            $ids =empty($ids)?"":$ids;
            $map['a.agent_id']=['in',$ids];
        }

        if(isset($param['username']) && !empty($param['username'])){
            $data2['name']=['like',"%".$param['username']."%"];
            $result =db('Member')->where($data2)->select();

            $ids =[];
            if(!empty($result)){
                foreach($result as $k=>$v){
                    $ids[]=$v['id'];
                }

            }
            $ids =empty($ids)?"":$ids;
            $map['a.member_id']=['in',$ids];
        }

        //   print_r($param);exit;
        $count =$this->alias('a')->where($map)->count();
//        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $prex =config('database.prefix');
//
        $sql =$this->alias('a')
            ->join($prex.'Equipment b','a.equip_id=b.id','left')
            ->join($prex.'Agent c','a.agent_id=c.id','left')
            ->join($prex.'Member d','a.member_id=d.id','left')
            ->field('a.id,a.create_time,a.sale_fee,a.sale_date,a.sale_year,a.sale_month,b.equip_code,c.name,d.name as username')
            ->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
//        dump($result);exit();
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            }
        }
        return $list;
    }
    public function __formatEdit($data = null) {
        if(!empty($data)){
        }
        return $data;
    }




    public function __my_before_insert(&$data){
        $data['create_time']=time();
        return true;
    }

    public function __my_before_update(&$data){

        return true;
    }

}