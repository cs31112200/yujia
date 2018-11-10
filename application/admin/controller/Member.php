<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Member extends Base
{
//    public function __construct(Request $request = null) {
//        parent::__construct($request);
//        $this->default_model =model('member');
//    }
//
//    public function member_list(){
//        $map  =$this->default_model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($this->default_model,null,'客户端用户列表',$map);
//    }
//
//    public function yxorder(){
//        $model =model('index/Yxorder');
//        $map  =$model->getSelect(input());
//        $order='id desc';
//        $this->setCookie();
//        return $this->__lists($model,null,'购买意向列表',$map,$order);
//
//    }
//
//
//
//
//    public function member_add(){
//
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$this->default_model->__msave($data,'Member');
//            $back['url']=($back['code']==0)?'':$this->getCookie();
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增':'修改';
//            return $this->__edits($this->default_model,input('id'),null,$title);
//        }
//    }
//
//
    public function member_equip_list(){
        $member_id =input('id',0,'intval');
        if($member_id <=0){
            $this->error('参数错误');
        }
        $user_name =model('Member')->getFieldsValue(['id'=>$member_id],'name');
        $this->assign('username',$user_name);

        $equip_id =input('equip_id',0,'intval');
        $my_equip_list =model('index/MemberEquip')->getMyEquipList($member_id);
        $detail_list=[]; $equip_ids=$equip_id;
        if(!empty($my_equip_list)){
            foreach($my_equip_list as $k=>$v){
                $my_equip_list[$k]['is_active']=($equip_id==$v['equip_id'] || ($equip_id==0 && $k==0))?"active":"";
                $detail_list[$v['equip_id']]=$v;
                if($equip_id==0 && $k==0){
                    $equip_ids=$v['equip_id'];
                }
            }
        }else{
            $detail_list[0]="";
        }
     //   print_r($my_equip_list);exit;

        $this->assign('elist',$my_equip_list);
        $this->assign('detail',$detail_list[$equip_ids]);


        //获取当日电流列表
        $redis = initRedis();
        $elec_list =$redis->lrange('equip_param_'.$detail_list[$equip_ids]['equip_code'],0,-1);

        $x=$y=[];
     //    $strtotime =strtotime(date('Y-m-d',time()));
        if(!empty($elec_list)){
            foreach($elec_list as $k=>$v){
                $the_value =json_decode($v,true);
                $hour =date('Y-m-d H:i:s',$the_value['hour']);

                $x[] =$hour;
                $y[] =$the_value['value'];
            }
        }
        $this->assign('x', implode(',',$x));
        $this->assign('y',implode(',',$y));
        $this->assign('_title','客户设备');
    //    print_r($elec_list);


        return $this->fetch('',['_name'=>$user_name.'的设备列表']);
    }

///*修改菜单/删除
// *
// */
//    public function change_tatus(){
//
//        $id = input('id/a');
//        if(is_array($id)) sort($id);
//        $id = is_array($id) ? implode(',',$id) : $id;
//        if ( empty($id) ) {
//            $this->error('请选择要操作的数据!');
//        }
//        $model =input('model_name');
//        if(empty($model))
//            $this->error('模型错误');
//
//
//        $action =input('action');
//        if(empty($action))
//            $this->error('请选择操作');
//        $result =model($model)->__changeStatus($id,$action);
//        if($result)
//            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
//        else
//            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
//
//    }
///*修改菜单/删除
// *
// */
//    public function change_tatus_two(){
//
//        $id = input('id/a');
//        if(is_array($id)) sort($id);
//        $id = is_array($id) ? implode(',',$id) : $id;
//        if ( empty($id) ) {
//            $this->error('请选择要操作的数据!');
//        }
//        $model =model('index/Yxorder');
//
//        $data['id']=['in',$id];
//        $data1['status']=2;
//        $result =$model->where($data)->update($data1);
//        if($result)
//            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
//        else
//            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
//
//    }
//
/*
 *
 */
    public function equip_up(){

        $member_id =input('member_id',0,'intval');
        $equip_id =input('equip_id',0,'intval');

        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->join($prex.'member c','a.member_id=c.id','LEFT')
                ->field('a.name,a.end_time,a.init_count,a.annual_fee,b.fee,c.name as member_name,b.product_id')
                ->where($data)->find();
        if(empty($result)){
            $this->error('非法访问');
        }
        $all_product =model('ProductPrice')->get_all_product_price();

        $all_products=[];
        foreach($all_product as $k=>$v){
            $all_products[$v['id']]=$v;
        }
        $this->assign('up_json',json_encode($all_products));


        $result['fee']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['fee']:0;
        $result['annual_fee']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['annual_fee']:0;
        $result['cut_fee_first']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['cut_fee_first']:0;
        $result['product_name']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['name']:0;

        $this->assign('app',$result);
        $this->assign('_toMethod','save_equip_up');


        $up_list =model('ProductPrice')->get_up_equip($result['init_count'],$result['fee'],$all_product);
        $this->assign('up_list',$up_list);
        $this->assign('_title','用户设备升级');

        //保存cookie
        cookie('up_data',json_encode($data));

        return $this->fetch('',['_name'=>'用户设备升级']);
    }


    public function save_equip_up(){
        if(request()->isPost()){
            $model_product_price =model('ProductPrice');
            $install_fee=input('post.install_fee',0,'intval');

            //检验下升级的对象是否正确
            $data =cookie('up_data');
            $data =empty($data)?[]:json_decode($data,true);
            if(empty($data)){
                $this->error('参数有误');
            }
            $prex =config('database.prefix');
            $result =db('MemberEquip')->alias('a')
                    ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                    ->join($prex.'member c','a.member_id=c.id','LEFT')
                    ->field('a.member_id,a.equip_id,a.name,a.end_time,b.init_count,b.annual_fee,b.fee,c.name as member_name,b.product_id,b.agent_id')
                    ->where($data)->find();
            if(empty($result)){
                $this->error('非法访问');
            }

            $all_product =$model_product_price->get_all_by_key();
            if(!isset($all_product[$result['product_id']])){
                $this->error('找不到该产品对应的产品价格啦');
            }
            $primary_count =$all_product[$result['product_id']]['init_count'];
            $primary_fee =$all_product[$result['product_id']]['fee'];


            //检验下升级的对象是否比较高级
            $product_id =input('post.product_id',0);
            if(!isset($all_product[$product_id])){
                $this->error('请正确选择要升级的产品价格');
            }
            $up_count =$all_product[$product_id]['init_count'];
            $up_fee =$all_product[$product_id]['fee'];




            if($primary_count>=$up_count && $primary_fee>=$up_fee){
                $this->error('请选择更高级的产品价格');
            }
            $up_fee =$up_fee-$primary_fee;
            //更新
            $data1['a.annual_fee']=$all_product[$product_id]['annual_fee'];
            $data1['a.init_count']=$all_product[$product_id]['init_count'];

            db('MemberEquip')->alias('a')->where($data)->update($data1);


            //插入记录
            model('UpRecord')->add_record($result['member_id'],$result['equip_id'],$result['product_id'],$product_id,$up_fee,$install_fee);

            $up_fee+=$install_fee;
            //插入平台流水
            model('PlatformFlow')->addFlow($result['equip_id'],$up_fee,'用户设备升级',6,1);

            //插入代理抽成
             model('AgentFlow')->addFlow($result['agent_id'],$result['equip_id'],$install_fee,'升级设备',2);
            $this->success('升级成功');


        }
    }

/*采集器列表
 *
 */
    public function collector_list(){

        $member_id =input('member_id',0,'intval');
        $equip_id =input('equip_id',0,'intval');
        $data['a.member_id']=$member_id;
        $data['a.equip_id']=$equip_id;
        $prex =config('database.prefix');
        $result =db('MemberEquip')->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','LEFT')
                ->join($prex.'member c','a.member_id=c.id','LEFT')
                ->field('a.name,a.end_time,a.init_count,a.annual_fee,b.fee,c.name as member_name,b.product_id')
                ->where($data)->find();
        if(empty($result)){
            $this->error('非法访问');
        }

        $model =model('EquipCollector');
        $map  =$model->getSelect(input());
        $order='id desc';
        $this->setCookie('collector_list');
        return $this->__lists($model,null,'用户'.$result['member_name'].'采集器列表',$map,$order);
    }

    public function add_equip_collector(){
        if(request()->isPost()){
            $member_id =input('member_id',0,'intval');
            $equip_id =input('equip_id',0,'intval');

            $result =model('index/MemberEquip')->getDetail($member_id,$equip_id);
            if(empty($result)){
                $this->error('您提交的数据有误');
            }

            $install_fee =input('post.install_fee',0,'intval');
            $type_id =input('post.type_id',0,'intval');

            //检验type-id
            $collector_result =db('CollectorType')->find($type_id);
            if(empty($collector_result)){
                $this->error('请正确选择采集器');
            }
            $collector_fee =$collector_result['price'];

            //获取最后一个序号
            $last_number =model('EquipCollector')->get_last_number($member_id,$equip_id);
            $data['member_id']=$member_id;
            $data['equip_id']=$equip_id;
            $data['other_name']=$last_number."号";
            $data['number']=$last_number;
            $data['type_id']=$type_id;
            $data['create_time']=time();
            db('EquipCollector')->insert($data);


            $data1['member_id']=$member_id;
            $data1['equip_id']=$equip_id;
            $data1['type_id']=$type_id;
            $data1['collector_fee']=$collector_fee;
            $data1['install_fee']=$install_fee;
            $data1['create_time']=time();
            db('CollectorRecord')->insert($data1);


            $collector_fee+=$install_fee;
            //插入平台流水
            model('PlatformFlow')->addFlow($result['equip_id'],$collector_fee,'出售采集器',7,1);

            //插入代理抽成
             model('AgentFlow')->addFlow($result['agent_id'],$result['equip_id'],$install_fee,'安装采集器',3);

             $this->success('添加成功');

        }
    }

    public function change_ec_tatus(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $model ="EquipCollector";
        if(empty($model))
            $this->error('模型错误');


        $action =input('action');
        if(empty($action))
            $this->error('请选择操作');
        $result =model($model)->__changeStatus($id,$action);
        if($result)
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        else
            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
    }


    public function member_list(){
        $this->assign('_title','客户列表');
        $this->setCookie('member_list');
        $this->setParam('member_list');
        return $this->fetch();
    }
    public function getMemberList()
    {
        $model = model('Member');
        $param = input();
        $param1 =$this->getParam('member_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*重置密码
     *
     */
    public function member_password_edit(){

        $model =model('Member');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Member');
            $back['url']=($back['code']==0)?'':$this->getCookie('member_list');
            if($back['code']==1){
                $theid =$data['id'];
                $is_insert =2;
                model('AdminLog')->addLog(UID,'Member',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title ='重置密码';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function member_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Member";

        $action =input('post.ope');
        $thestatus=0;
        switch($action){
            case 'open':
                $thestatus=3;
                break;
            case 'close':
                $thestatus=4;
                break;
            case 'delete':
                $thestatus=5;
                break;
            default:
                $this->error('操作类型有误',$_SERVER['HTTP_REFERER']);
        };


        $datass['id'] =['in',$id];
        $resultss =db($model)->where($datass)->select();
        $thecount=count($resultss);
        if($thecount!=$id_count){
            $this->error('选择的数据不匹配');
        }

//        if(!is_admin()){
//            $map['merchant_id']=MERID;
//        }


//        $map['is_root']=0;
        $map=[];

        $result =model($model)->__changeStatus($id,$action,$map);
        if($result){


            //记录修改状态、删除
            $insertall =[];
            foreach($resultss as $k=>$v){
                $insertall[$k]['admin_id']=UID;
                $insertall[$k]['themodel']=$model;
                $insertall[$k]['object_id']=$v['id'];
                $insertall[$k]['status']=$thestatus;
                $insertall[$k]['json_data']=json_encode($v);
                $insertall[$k]['create_time']=time();
                $insertall[$k]['content']=model('admin/AdminLog')->generalContent($thestatus,$model,$v['id'],json_encode($v));
            }
            model('admin/AdminLog')->insertAll($insertall);
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        }else
            $this->error('修改失败',$_SERVER['HTTP_REFERER']);


    }
//    public function collector_list(){
//        $this->assign('_title','采集器列表');
//        $this->setCookie('collector_list');
//        $this->setParam('collector_list');
//        return $this->fetch();
//    }
//    public function getCollectorList()
//    {
//        $model = model('EquipCollector');
//        $param = input();
//        $param1 =$this->getParam('collector_list');
//        $param = array_merge($param,$param1);
//        $result = $model->getListData($param);
//        $result = $this->__doList($model, $result);
//        return ($result);
//    }
//    /*
// *
// */
//    public function collector_change_status(){
//
//        $id = input('id/a');
//        if(is_array($id)) sort($id);
//        $id = is_array($id) ? implode(',',$id) : $id;
//        if ( empty($id) ) {
//            $this->error('请选择要操作的数据!');
//        }
//        $ids= explode(',', $id);
//        $id_count=count($ids);
//
//
//        $model="EquipCollector";
//
//        $action =input('post.ope');
//        $thestatus=0;
//        switch($action){
//            case 'open':
//                $thestatus=3;
//                break;
//            case 'close':
//                $thestatus=4;
//                break;
//            case 'delete':
//                $thestatus=5;
//                break;
//            default:
//                $this->error('操作类型有误',$_SERVER['HTTP_REFERER']);
//        };
//
//
//        $datass['id'] =['in',$id];
//        $resultss =db($model)->where($datass)->select();
//        $thecount=count($resultss);
//        if($thecount!=$id_count){
//            $this->error('选择的数据不匹配');
//        }
//
////        if(!is_admin()){
////            $map['merchant_id']=MERID;
////        }
//
//
////        $map['is_root']=0;
//        $map=[];
//
//        $result =model($model)->__changeStatus($id,$action,$map);
//        if($result){
//
//
//            //记录修改状态、删除
//            $insertall =[];
//            foreach($resultss as $k=>$v){
//                $insertall[$k]['admin_id']=UID;
//                $insertall[$k]['themodel']=$model;
//                $insertall[$k]['object_id']=$v['id'];
//                $insertall[$k]['status']=$thestatus;
//                $insertall[$k]['json_data']=json_encode($v);
//                $insertall[$k]['create_time']=time();
//                $insertall[$k]['content']=model('admin/AdminLog')->generalContent($thestatus,$model,$v['id'],json_encode($v));
//            }
//            model('admin/AdminLog')->insertAll($insertall);
//            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
//        }else
//            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
//
//
//    }


    public function yxorder(){
        $this->assign('_title','意向用户列表');
        $this->setCookie('yxorder_list');
        $this->setParam('yxorder_list');
        return $this->fetch();
    }
    public function getYxorderList()
    {
        $model = model('Yxorder');
        $param = input();
        $param1 =$this->getParam('yxorder_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*
 *
 */
    public function yxorder_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Yxorder";

        $action =input('post.ope');
        $thestatus=0;
        switch($action){
            case 'open':
                $thestatus=3;
                break;
            case 'close':
                $thestatus=4;
                break;
            case 'delete':
                $thestatus=5;
                break;
            default:
                $this->error('操作类型有误',$_SERVER['HTTP_REFERER']);
        };


        $datass['id'] =['in',$id];
        $resultss =db($model)->where($datass)->select();
        $thecount=count($resultss);
        if($thecount!=$id_count){
            $this->error('选择的数据不匹配');
        }

//        if(!is_admin()){
//            $map['merchant_id']=MERID;
//        }


//        $map['is_root']=0;
        $map=[];

        $result =model($model)->__changeStatus($id,$action,$map);
        if($result){


            //记录修改状态、删除
            $insertall =[];
            foreach($resultss as $k=>$v){
                $insertall[$k]['admin_id']=UID;
                $insertall[$k]['themodel']=$model;
                $insertall[$k]['object_id']=$v['id'];
                $insertall[$k]['status']=$thestatus;
                $insertall[$k]['json_data']=json_encode($v);
                $insertall[$k]['create_time']=time();
                $insertall[$k]['content']=model('admin/AdminLog')->generalContent($thestatus,$model,$v['id'],json_encode($v));
            }
            model('admin/AdminLog')->insertAll($insertall);
            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
        }else
            $this->error('修改失败',$_SERVER['HTTP_REFERER']);


    }
}
