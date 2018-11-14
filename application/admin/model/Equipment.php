<?php
namespace app\admin\model;

use app\admin\model\Base;

class Equipment extends Base{

//自动填充时间    
    protected $autoWriteTimestamp = false;


    /*筛选
     *
     */
    public function getSelect($param=null) {
        $map =[];

        if(!empty($param['type'])){
            $map['type']=$param['type'];
        }

        if( isset($param['batch_id']) ){
            $map['batch_id']=$param['batch_id'];
        }

        if( isset($param['status']) && ($param['status'])>0){
            $map['status']=$param['status'];
        }

        if(!empty($param['equip_code'])){
            $map['equip_code']=['like',"%".$param['equip_code']."%"];
        }


        return $map;
    }
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['type']) && !empty($param['type'])){
            $map['type']=$param['type'];
        }
        if(isset($param['batch_id']) && !empty($param['batch_id'])){
            $map['batch_id']=$param['batch_id'];
        }
        if(isset($param['status']) && !empty($param['status'])){
            $map['a.status']=$param['status'];
        }
        if(isset($param['equip_code']) && !empty($param['equip_code'])){
            $map['equip_code']=['like',"%".$param['equip_code']."%"];
        }
        //   print_r($param);exit;
        $count =$this->alias('a')->where($map)->count();
        $prex =config('database.prefix');

        $sql =$this->alias('a')
            ->join($prex.'equipment_type b','a.type=b.id','left')
            ->field('a.id,a.equip_code,a.qrcode,a.qrcode_pic,a.card_num,a.purchase_fee,a.fee,a.agent_fee,a.annual_fee,a.agent_id,a.electric,a.product_id,a.status,
                    a.connect_status,a.init_count,a.sort,a.batch_id,a.update_time,a.create_time,b.type_name as type_name')
            ->where($map)->order('id desc')->limit(($page-1)*$page_size.','.$page_size)->buildSql();
//        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }

    public function __formatList($list = null) {
        $statusarr=$this->getStatus();
        if(!empty($list)){
            $all_product =model('ProductPrice')->get_all_product_price();

            $all_products=[];
            foreach($all_product as $k=>$v){
                $all_products[$v['id']]=$v;
            }

            $color_arr =$this->get_status_color();
            $name_arr =$this->get_status_name();

            foreach($list as $k=>$v){
//              $list[$k]['type_name']=model('EquipmentType')->getFieldsValue(['id'=>$v['type']],'type_name');
                $list[$k]['status_name']=$statusarr[$v['status']];

//              $list[$k]['fee']=isset($all_products[$v['product_id']])?$all_products[$v['product_id']]['fee']:0;
//              $list[$k]['annual_fee']=isset($all_products[$v['product_id']])?$all_products[$v['product_id']]['annual_fee']:0;
//              $list[$k]['agent_fee']=isset($all_products[$v['product_id']])?$all_products[$v['product_id']]['cut_fee_first']:0;
                $list[$k]['status_color']=$color_arr[$v['status']];
                $list[$k]['status_name']=$name_arr[$v['status']];
                $list[$k]['create_time']=date('Y-m-d H:m:s',$list[$k]['create_time']);
                $list[$k]['qrcode_pic']=generalQnyImg($list[$k]['qrcode_pic']);
            }
        }
        //  print_r($list);exit;
        return $list;
    }

    /*获取状态或者状态颜色
     */
    public function getStatus(){

        return [
            '1'=>['未使用','black'],
            '2'=>['已使用','green'],
            '3'=>['报废','red'],
        ];
    }
    public function get_status_color(){
        return [
            1=>'black',
            2=>'green',
            3=>'red'
        ];
    }

    public function get_status_name(){
        return [
            1=>'未使用',
            2=>'已使用',
            3=>'报废'
        ];
    }
    public function getStatusToSelect(){
        $result =$this->getStatus();
        $return =[];
        foreach($result as $k=>$v){
            $return[$k]=$v[0];
        }
        return $return;
    }



    public function __formatEdit($data = null) {

        return $data;
    }


    public function __my_before_insert(&$data){
        if(!empty($data['product_id'])){
            $price=db('ProductPrice')->where('id',$data['product_id'])->find();
            $data['fee']=$price['fee'];
            $data['annual_fee']=$price['annual_fee'];
            $data['agent_fee']=$price['cut_fee_first'];
            $data['purchase_fee']=$price['purchase_fee'];
        }
        if(empty($data['equip_code']) ){
            $this->setError('设备码不能为空');
            return;
        }
        if(!empty($data['equip_code']) ){
            $data['equip_code']=trim($data['equip_code']);
        }
        //  $data['equip_code']=transNum($data['type']).$data['equip_code'];
        $data['qrcode']=getQrcode($data['equip_code']);
        $savePath = APP_PATH . '../public/qrcode/';
        $webPath = '/qrcode/';
        $qrData = $data['qrcode'];
        $qrLevel = 'H';
        $qrSize = '10';
        $savePrefix = 'Yujia';
        if($filename = createQRcodeToQny($savePath, $qrData, $qrLevel, $qrSize, $savePrefix)){
            $pic = $filename;
        }
        $data['qrcode_pic']=$pic;
        $data['create_time']=time();
        $data['update_time']=time();
        $data['status']=1;
        unset($data['pay_status']);
        return true;
    }
    public function __my_before_update(&$data){
        $equip=db('Equipment')->where('id',$data['id'])->find();
        if(!empty($data['product_id'])){
            $price=db('ProductPrice')->where('id',$data['product_id'])->find();
            $data['fee']=$price['fee'];
            $data['annual_fee']=$price['annual_fee'];
            $data['agent_fee']=$price['cut_fee_first'];
            $data['purchase_fee']=$price['purchase_fee'];
        }
        if(empty($data['equip_code']) ){
            $this->setError('设备码不能为空');
            return;
        }
        if(!empty($data['equip_code']) ){
            $data['equip_code']=trim($data['equip_code']);
        }
        if(empty($data['qrcode'])){
            $data['qrcode']=$equip['qrcode'];
            $savePath = APP_PATH . '../public/qrcode/';
            $webPath = '/qrcode/';
            $qrData = $data['qrcode'];
            $qrLevel = 'H';
            $qrSize = '10';
            $savePrefix = 'Yujia';
            if($filename = createQRcodeToQny($savePath, $qrData, $qrLevel, $qrSize, $savePrefix)){
                $pic = $filename;
            }
            $data['qrcode_pic']=$pic;
        }


        //   $data['equip_code']=transNum($data['type']).$data['equip_code'];

        unset($data['pay_status']);
        $data['update_time']=time();
        return true;
    }

    public function get_equip_kong(){
        return [
            3=>'3控',
            6=>'6控',
            9=>'9控',
            12=>'12控',
            15=>'15控',
            18=>'18控',
        ];
    }




    /*可升级的类型
     *
     */
    public function get_up_kong($init){

    }
    public function update_status($data){
        $equipment=new Equipment;
        foreach($data as $v){
            $equip=db('Equipment')->where('equip_code',$v)->find();
            $update=[
                ['id'=>$equip['id'],'status'=>'2']
            ];
            $equipment->isUpdate()->saveAll($update);
        }
    }

}