<?php
namespace app\admin\model;

use app\admin\model\Base;

class Finance extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['sn']) && !empty($param['sn'])){
            $map['sn']=$param['sn'];
        }
        if(isset($param['type']) && !empty($param['type'])){
            $map['type']=$param['type'];
        }
        if(isset($param['fee_type']) && !empty($param['fee_type'])){
            $map['fee_type']=$param['fee_type'];
        }
        if(isset($param['start_time']) && isset($param['end_time'])){
            $time=[$param['start_time'],$param['end_time']];
            $map['create_time']=['between time',$time];
        }
        if(isset($param['start_time']) && !isset($param['end_time'])){
            $map['create_time']=['>= time',$param['start_time']];
        }
        if(!isset($param['start_time']) && isset($param['end_time'])){
            $map['create_time']=['<= time',$param['end_time']];
        }
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        $type=$this->getType();
        if(!empty($list)){
            $color_arr =$this->get_type_color();
            $name_arr =$this->get_type_name();
            foreach($list as $k=>$v){
                $list[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
                $list[$k]['type']=$list[$k]['type']==1?'进账':'出账';
                $list[$k]['status']=$list[$k]['status']==1?'已付':'未付';
                if($list[$k]['fee_type']==1){
                    $list[$k]['fee_type']='卖设备';
                }else if($list[$k]['fee_type']==2){
                    $list[$k]['fee_type']='买设备';
                }
                else if($list[$k]['fee_type']==3){
                    $list[$k]['fee_type']='代理费';
                }
                $list[$k]['type_color']=$color_arr[$v['type']];
                $list[$k]['type_name']=$name_arr[$v['type']];
            }

        }
        return $list;
    }

    public function getUnpaidListData($param=null){
        $map =[];
        $map['a.type']=1;
        $map['a.status']=0;
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['sn']) && !empty($param['sn'])){
            $map['a.sn']=$param['sn'];
        }
        if(isset($param['name']) && !empty($param['name'])){
            $map['c.name']=$param['name'];
        }
        if(isset($param['fee_type']) && !empty($param['fee_type'])){
            $map['a.fee_type']=$param['fee_type'];
        }
        if(isset($param['start_time']) && isset($param['end_time'])){
            $time=[$param['start_time'],$param['end_time']];
            $map['a.create_time']=['between time',$time];
        }
        if(isset($param['start_time']) && !isset($param['end_time'])){
            $map['a.create_time']=['>= time',$param['start_time']];
        }
        if(!isset($param['start_time']) && isset($param['end_time'])){
            $map['a.create_time']=['<= time',$param['end_time']];
        }
        $prex =config('database.prefix');
        $count =$this->alias('a')
            ->join($prex.'agent_order b','a.object_id=b.id','left')
            ->join($prex.'agent c','b.object_id=c.id','left')->where($map)->count();
        $sql =$this->alias('a')
            ->join($prex.'agent_order b','a.object_id=b.id','left')
            ->join($prex.'agent c','b.object_id=c.id','left')
            ->field('a.id,a.sn,a.fee,a.fee_type,a.content,a.need_time,a.create_time,c.name')
            ->where($map)->limit(($page-1)*$page_size.','.$page_size)->order('need_time')->buildSql();
        $result =$this->query($sql);
//        dump($result);exit();
        return $this->generalResult($result,$count);
    }
    public function __unpaidformatList($list = null)
    {
        if (!empty($list)) {
//            $color_arr =$this->get_type_color();
//            $name_arr =$this->get_type_name();
            foreach ($list as $k => $v) {
                $list[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
                $list[$k]['type'] = '进账';
                $list[$k]['status'] = '未付';
                if ($list[$k]['fee_type'] == 1) {
                    $list[$k]['fee_type'] = '卖设备';
                } else if ($list[$k]['fee_type'] == 2) {
                    $list[$k]['fee_type'] = '买设备';
                } else if ($list[$k]['fee_type'] == 3) {
                    $list[$k]['fee_type'] = '代理费';
                } else if ($list[$k]['fee_type'] == 4) {
                    $list[$k]['fee_type'] = '用户年费';
                }
                if ($list[$k]['need_time'] - time() > 5 * 3600 * 24) {
                    $list[$k]['status_color'] = 'gray';
                }
                if ($list[$k]['need_time'] - time() < 5 * 3600 * 24 && $list[$k]['need_time'] - time() > 3 * 3600 * 24) {
                    $list[$k]['status_color'] = 'blue';
                }
                if ($list[$k]['need_time'] - time() < 3 * 3600 * 24) {
                    $list[$k]['status_color'] = 'red';
//                $list[$k]['type_color']=$color_arr[$v['type']];
//                $list[$k]['type_name']=$name_arr[$v['type']];
                }

            }
            return $list;
        }
    }

    public function getType(){

        return [
            '1'=>['进账','black'],
            '2'=>['出账','green'],
        ];
    }
    public function get_type_color(){
        return [
            1=>'green',
            2=>'red',
        ];
    }

    public function get_type_name(){
        return [
            1=>'进账',
            2=>'出账',
        ];
    }
    public function __formatEdit($data = null) {
        return $data;
    }

    public function __my_before_insert(&$data){
        return true;
    }

    public function __my_before_update(&$data){
        return true;
    }
    public function finance_add($id,$product_id,$status){
        $price=db('ProductPrice')->where('id',$product_id)->find();
        $data['sn']=create_sn('fin');
        $data['type']=2;
        $data['fee']=$price['purchase_fee'];
        $data['fee_type']=2;
        $data['status']=$status;
        $data['object_id']=$id;
        $data['create_time']=time();
        return db('Finance')->insert($data);
    }

    public function getTypeToSelect(){
        $result =$this->getType();
        $return =[];
        foreach($result as $k=>$v){
            $return[$k]=$v[0];
        }
        return $return;
    }
    public function getFeeType(){

        return [
            '1'=>['卖设备'],
            '2'=>['买设备'],
            '3'=>['代理费'],
            '4'=>['用户年费'],
        ];
    }
    public function getFeeTypeToSelect(){
        $result =$this->getFeeType();
        $return =[];
        foreach($result as $k=>$v){
            $return[$k]=$v[0];
        }
        return $return;
    }
    public function getUnpaidFeeType(){

        return [
            '1'=>['卖设备'],
            '4'=>['用户年费'],
        ];
    }
    public function getUnpaidFeeTypeToSelect(){
        $result =$this->getUnpaidFeeType();
        $return =[];
        foreach($result as $k=>$v){
            $return[$k]=$v[0];
        }
        return $return;
    }

    public function getFee($param){
        $map =[];
        if(isset($param['sn']) && !empty($param['sn'])){
            $map['sn']=$param['sn'];
        }
        if(isset($param['name']) && !empty($param['name'])){
            $Agent=db('Agent')->where('name',$param['name'])->find();
            $AgentOrder=db('AgentOrder')->where('object_id',$Agent['id'])->find();
            $map['object_id']=$AgentOrder['id'];
        }
        if(isset($param['fee_type']) && !empty($param['fee_type'])){
            $map['fee_type']=$param['fee_type'];
        }
        if(isset($param['start_time']) && isset($param['end_time'])){
            $time=[$param['start_time'],$param['end_time']];
            $map['create_time']=['between time',$time];
        }
        if(isset($param['start_time']) && !isset($param['end_time'])){
            $map['create_time']=['>= time',$param['start_time']];
        }
        if(!isset($param['start_time']) && isset($param['end_time'])){
            $map['create_time']=['<= time',$param['end_time']];
        }

        $income=db('Finance')->where($map)->where('type',1)->select();
        $income_received=db('Finance')->where($map)->where('type',1)->where('status',1)->select();
        $pay=db('Finance')->where($map)->where('type',2)->select();
        $paid=db('Finance')->where($map)->where('type',2)->where('status',1)->select();
        if(isset($param['type']) && $param['type']==1){
            $income=db('Finance')->where($map)->where('type',1)->select();
            $income_received=db('Finance')->where($map)->where('type',1)->where('status',1)->select();
            $pay=[];
            $paid=[];
        }
        if(isset($param['type']) && $param['type']==2){
            $income=[];
            $income_received=[];
            $pay=db('Finance')->where($map)->where('type',2)->select();
            $paid=db('Finance')->where($map)->where('type',2)->where('status',1)->select();
        }
        $income_fee=0;
        $income_received_fee=0;
        $pay_fee=0;
        $paid_fee=0;
        for($i=0;$i<=count($income)-1;$i++){
            $income_fee+=$income[$i]['fee'];
        }
        for($i=0;$i<=count($income_received)-1;$i++){
            $income_received_fee+=$income_received[$i]['fee'];
        }
        for($i=0;$i<=count($pay)-1;$i++){
            $pay_fee+=$pay[$i]['fee'];
        }
        for($i=0;$i<=count($paid)-1;$i++){
            $paid_fee+=$paid[$i]['fee'];
        }
        $income_unreceived_fee=$income_fee-$income_received_fee;
        $unpaid_fee=$pay_fee-$paid_fee;
        $result=[
            'income_fee'=>$income_fee,
            'income_received_fee'=>$income_received_fee,
            'pay_fee'=>$pay_fee,
            'paid_fee'=>$paid_fee,
            'income_unreceived_fee'=>$income_unreceived_fee,
            'unpaid_fee'=>$unpaid_fee,
        ];
        return $result;

    }
}