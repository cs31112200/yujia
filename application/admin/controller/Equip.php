<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Equip extends Base
{
    function __construct(Request $request = null) {
        parent::__construct($request);
        $this->default_model =model('Equipment');
    }
//
//    public function equip_list(){
//        $map  =$this->default_model->getSelect(input());
//        $this->setCookie('equip_list');
//        return $this->__lists($this->default_model,null,'菜单列表',$map,'id desc');
//    }
//    public function equip_type_list(){
//        $model =model('EquipmentType');
//        $map  =$model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($model,null,'设备类型列表',$map,'id desc');
//    }
//
//
//    public function equip_price_list(){
//        $model =model('ProductPrice');
//        $map  =$model->getSelect(input());
//        $this->setCookie('equip_price_list');
//        return $this->__lists($model,null,'产品价格列表',$map,'id desc');
//    }
//
//
//    public function collector_list(){
//        $model =model('CollectorType');
//        $map  =$model->getSelect(input());
//        $this->setCookie('collector_list');
//        return $this->__lists($model,null,'采集器列表',$map,'id desc');
//    }
//
//
//    public function equip_type_add(){
//
//        $model =model('EquipmentType');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'EquipType');
//            $back['url']=($back['code']==0)?'':$this->getCookie('equip_list');
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增设备类型':'修改设备类型';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//
//
//    public function equip_add(){
//
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$this->default_model->__msave($data,'Equip');
//            $back['url']=($back['code']==0)?'':$this->getCookie('equip_list');
//            if($back['code']==1){
//                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
//                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
//                model('AdminLog')->addLog(UID,'Equipment',$theid,$is_insert,json_encode($data));
//            }
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增设备':'修改设备';
//            return $this->__edits($this->default_model,input('id'),null,$title);
//        }
//    }


    public function batch_add(){

      //  import('phpexcel.PHPExcel.IOFactory',EXTEND_PATH);
      //  $objPHPExcel = \PHPExcel_IOFactory::load($inputFileName);
        $model =model('Equipment');
        if(request()->isPost()){

            /***自定义验证区域*****/

            //存储
            $data =input('post.');
            $time=time();



            //检验是否存在批量文件数据
            $sheet_data =cache('sheet_data');


            if(empty($sheet_data)){
                $this->error('无效的批次数据');
            }

            $type =$data['type'];


            $type_result =db('EquipmentType')->find($type);
            if(empty($type_result) || $type_result['status']!=1){
                $this->error('无效的类型id');
            }



            //检验批次号
            if(!isset($data['batch_id']) || empty($data['batch_id'])){
                $this->error('请填写批次号');
            }


            //检验金额
//            if(!isset($data['fee']) || floatval($data['fee'])<=0){
//                $this->error('金额必须大于0');
//            }


//            if(!isset($data['agent_fee']) || floatval($data['agent_fee'])<=0){
//                $this->setError('代理价格错误');
//                return false;
//            }

//            if($data['fee']<$data['agent_fee']){
//                $this->setError('代理价格不能大于设备价格');
//                return false;
//            }



            $need_sheet=[];
            $need_card_num=[];

            foreach( $sheet_data as $k=>$v){
                $need_card_num[]=$v[1];
                $need_sheet[]=$v[2];
            }


            //获取所有的卡
            $all_sim =db('Equipment')->field('equip_code')->where(1)->select();
            $all_sim_no =[];
            if(!empty($all_sim)){
                foreach($all_sim as $k=>$v){
                    $all_sim_no[]=$v['equip_code'];
                }
            }


            //新增的卡与所有的卡求差集

            $new_insert_sim =array_diff($need_sheet,$all_sim_no);



            if(empty($new_insert_sim)){
                $this->error('您所需要插入的数据都已经插入过，不能在重复插入');
            }
            $batch_insert=[];
            $insert=[];
            foreach($new_insert_sim as $k=>$v){
                $price=db('ProductPrice')->where('id',$data['product_id'])->find();
                $batch_insert[$k]['create_time']=$time;
                $batch_insert[$k]['update_time']=$time;
                $batch_insert[$k]['fee']=$price['fee'];
                $batch_insert[$k]['annual_fee']=$price['annual_fee'];
                $batch_insert[$k]['agent_fee']=$price['cut_fee_first'];
                $batch_insert[$k]['purchase_fee']=$price['purchase_fee'];
                $batch_insert[$k]['type']=$type;
                $batch_insert[$k]['batch_id']=$data['batch_id'];
                $batch_insert[$k]['product_id']=$data['product_id'];
                $batch_insert[$k]['equip_code']=trim($v);
                $batch_insert[$k]['qrcode']=getQrcode($batch_insert[$k]['equip_code']);
                $savePath = APP_PATH . '../public/qrcode/';
                $webPath = '/qrcode/';
                $qrData = $batch_insert[$k]['qrcode'];
                $qrLevel = 'H';
                $qrSize = '10';
                $savePrefix = 'Yujia';
                if($filename = createQRcode($savePath, $qrData, $qrLevel, $qrSize, $savePrefix)){
                    $pic = $webPath . $filename;
                }
                $batch_insert[$k]['qrcode_pic']=$pic;
                foreach($need_card_num as $key=>$value){
                    $batch_insert[$k]['card_num']=$value;
                }
            }
            $insert_result=model('Equipment')->saveAll($batch_insert);
            foreach($insert_result as $k=>$v){
                $finance_insert[$k]['sn']=create_sn('fin');
                $finance_insert[$k]['type']=2;
                $finance_insert[$k]['fee']=$v['purchase_fee'];
                $finance_insert[$k]['fee_type']=2;
                $finance_insert[$k]['status']=$data['pay_status'];
                $finance_insert[$k]['object_id']=$v['id'];
                $finance_insert[$k]['create_time']=$time;
            }
            db('Finance')->insertAll($finance_insert);
            if($insert_result){
                $this->success('操作成功','/Equip/equip_list');
            }else{
                $this->error('操作失败');
            }


        //    $back =$model->__msave($data,'Equip');
         //   $back['url']=($back['code']==0)?'':$this->getCookie();
          //  $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            return $this->__edits($model,input('id'),null, '批量设备');
        }
    }
//
///*按批次号删除
// *
// */
//
//    public function batch_del(){
//        $batch_id =input('post.batch_id');
//        if(empty($batch_id)){
//            $this->error('请先输入批次号');
//        }
//        $data['batch_id']=$batch_id;
//        $data['status']=1;
//        $del =db('Equipment')->where($data)->delete();
//        if($del){
//             $this->success('操作成功');
//        }else{
//            $this->error('没有找到符合条件的设备');
//        }
//
//    }
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
//
//
///*报废或者删除
// *
// */
//    public function equip_ope(){
//        $action =input('action');
//        $id =input('id');
//        $result =db('Equipment')->find($id);
//        if($result['status']!=1)
//            $this->error('设备状态不对，只能未使用才能操作');
//        $data['id']=$id;
//        switch($action){
//            case 'delete':
//               $results =db('Equipment')->where($data)->delete();
//
//                break;
//
//
//            case 'bf':
//                $data1['status']=3;
//                $results =db('Equipment')->where($data)->update($data1);
//                break;
//
//
//            default:
//                $this->error('非法操作');
//                break;
//        }
//        if($results)
//            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
//        else
//            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
//
//    }
//
//
    public function to_agent(){
        $model=model('Agent');
        $agent_id =empty(input('agent_id'))?0:input('agent_id');

        if($agent_id){
            $result =db('Agent')->find($agent_id);

            if(empty($result)){
                $this->error('非法访问', $_SERVER['HTTP_REFER']);
            }

            $list =get_send_down_list($agent_id);
            $this->assign('_list',$list);
        }
        $view = empty($view)?request()->action():$view;
        $this->assign('_title','下发代理');
        return $this->fetch($view,['_name'=>'下发代理']);
//        return $this->__edits($model,input('id'),null,'下发代理');
    }


/*下发设备码
 *
 */
    public function send_down(){
        if(request()->isPost()){
            $equip_code =input('post.equip_code');
            $agent_id =input('post.agent_id');
            if(empty($equip_code)){
                $this->error('请填写设备码');
            }

            if(empty($agent_id)){
                $this->error('请选择代理人');
            }

            $agent_result =db('Agent')->find($agent_id);
            if(empty($agent_result)){
                $this->error('找不到该代理人id');
            }




            $result =db('Equipment')->where('equip_code='.$equip_code)->field('equip_code,product_id,batch_id,status')->find();
            if(empty($result)){
                $this->error('无效设备码');
            }



            if($result['status']!=1){
                $this->error('该设备码已经使用');
            }
            $all_product =model('ProductPrice')->get_all_product_price();
            $all_products=[];
            foreach($all_product as $k=>$v){
                $all_products[$v['id']]=$v;
            }
            $result['agent_fee']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['cut_fee_first']:0;
            $result['fee']=isset($all_products[$result['product_id']])?$all_products[$result['product_id']]['fee']:0;


            $redis_result =send_down($agent_id,$result);
            if($redis_result){
                $this->success('添加成功', '', $result);
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->error('非法请求');
        }
    }

/*
 *
 */
    public function del_send(){
        $agent_id =input('agent_id');
        $equip_code =input('equip_code');

        if(empty($agent_id) || empty($equip_code)){
            $this->error('非法访问');
        }

        $agent_result =db('Agent')->find($agent_id);
        if(empty($agent_result)){
            $this->error('找不到该代理人');
        }

        $list =get_send_down_list($agent_id);

        $count=0;
        $need_remove =[];
        foreach($list as $k=>$v){
            if($v['equip_code']==$equip_code){
                $count++;
                $need_remove=$v;
            }
        }

        if($count==0){
            $this->error('未找到要删除的设备');
        }
        $need_remove =json_encode($need_remove);

        $redis = initRedis();
        $redis_name ="agent_".$agent_id."_senddown";
        $redis->sRem($redis_name,$need_remove);
        $this->success('删除成功',$_SERVER['HTTP_REFERER']);

    }

/*
 *
 */
    public function send_order(){
        if(request()->isPost()){
            $agent_id =input('post.agent_id');
            $remark =input('post.remark','');
            if(empty($agent_id)){
                $this->error('非法访问');
            }

            $agent_result =db('Agent')->find($agent_id);
            if(empty($agent_result)){
                $this->error('找不到该代理人');
            }

            $list =get_send_down_list($agent_id);

            if(empty($list)){
                $this->error('未选择设备下发');
            }

            //赛选出要插入的设备号

            $equip_arr=[];
            foreach($list as $k=>$v){
                $equip_arr[]=$v['equip_code'];
            }


            //判定是否有重复的
            $result =db('AgentOrderDetail')->where(1)->field('equip_code')->select();
            $have_arr=[];
            foreach($result as $k=>$v){
                $have_arr[]=$v['equip_code'];
            }

            //需要插入的
            $diff_arr =array_diff($equip_arr,$have_arr);


            if(empty($diff_arr)){
                $this->error('您所选的设备都已经被下发');
            }

            $insert_arr=[];
            $i=$agent_fee=0;
            $real_fee=0;
            $update_data=[];
            foreach($list as $k=>$v){
                if(in_array($v['equip_code'], $diff_arr)){
                    $insert_arr[$i]['equip_code']=$v['equip_code'];
                  //  $insert_arr[$i]['agent_id']=$agent_id;
                    $insert_arr[$i]['batch_id']=$v['equip_code'];
                    $insert_arr[$i]['fee']=$v['fee'];
                    $insert_arr[$i]['agent_fee']=$v['agent_fee'];
                    $insert_arr[$i]['create_time']=time();
                    $i++;
                    $agent_fee+=$v['agent_fee'];
                    $real_fee+=$v['fee'];
                    $update_data[]=$v['equip_code'];
                }
            }

            $order_data['total_fee']=$real_fee;
            $order_data['agent_fee']=$agent_fee;
            $order_data['admin_id']=UID;
            $order_data['order_sn']=create_sn('agent');
            $order_data['object_id']=$agent_id;
            $order_data['remark']=$remark;
            $order_data['status']=1;
            $order_data['type']=1;
            $order_data['create_time']=time();
            $res['status']=2;
//            db('Equipment')->where('equip_code',$equip_arr)->update($res);

            Db::startTrans();
       //     print_r($order_data);exit;
            //插入订单
            $db =db('AgentOrder');
            $order_result =$db->insert($order_data,false,true);

            model('Equipment')->update_status($equip_arr);
            foreach($insert_arr as $k=>$v){
                $insert_arr[$k]['order_id']=$order_result;
            }
            //插入订单详情
            $detail_result =db('AgentOrderDetail')->insertAll($insert_arr);

            $finance['sn']=create_sn('fin');
            $finance['type']=1;
            $finance['fee']=$order_data['total_fee'];
            $finance['fee_type']=1;
            $finance['status']=input('post.pay_status');
            $finance['object_id']=$order_result;
            $finance['need_time']=strtotime(input('post.need_time'));
            $finance['create_time']=time();
            db('Finance')->insert($finance);

            $finance_agent['sn']=create_sn('fin');
            $finance_agent['type']=2;
            $finance_agent['fee']=$order_data['agent_fee'];
            $finance_agent['fee_type']=3;
            $finance_agent['status']=input('post.pay_status');
            $finance_agent['object_id']=$order_result;
            $finance_agent['need_time']=strtotime(input('post.need_time'));
            $finance_agent['create_time']=time();
            db('Finance')->insert($finance_agent);


            //插入代理的流水记录
            model('AgentFlow')->addFlow($agent_id,$order_result,$agent_fee,'购买一批设备，单号为:'.$order_data['order_sn'],1);


            //插入平台的流水记录
            $platform_flow =model('PlatformFlow')->addFlow($order_result,$real_fee,$agent_result['name'].'购买一批设备，单号为:'.$order_data['order_sn'],1,2);


            //修改设备的agent_id
            $save_data1['equip_code']=['in',$update_data];
            $save_data['agent_id']=$agent_id;
            db('Equipment')->where($save_data1)->update($save_data);

            //删除缓存
            $redis = initRedis();
            $redis_name ="agent_".$agent_id."_senddown";
            $redis->del($redis_name);

            if($order_result && $detail_result && $platform_flow){
                Db::commit();
                $this->success('下发成功','/Equip/equip_list');
            }else{
                Db::rollback();
                $this->error('下发失败');
            }

        }else{
            $this->error('非法请求');
        }
    }
//
//
//    public function price_add(){
//
//        $model =model('ProductPrice');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'ProductPrice');
//            $back['url']=($back['code']==0)?'':$this->getCookie('equip_price_list');
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//
//            $title =empty(input('id'))?'新增产品价格':'修改产品价格';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//
//    public function collector_add(){
//
//        $model =model('CollectorType');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'CollectorType');
//            $back['url']=($back['code']==0)?'':$this->getCookie('collector_list');
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//
//            $title =empty(input('id'))?'新增采集器':'修改采集器';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//    public function change_collector_tatus(){
//
//        $id = input('id/a');
//        if(is_array($id)) sort($id);
//        $id = is_array($id) ? implode(',',$id) : $id;
//        if ( empty($id) ) {
//            $this->error('请选择要操作的数据!');
//        }
//        $model ='CollectorType';
//        if(empty($model))
//            $this->error('模型错误');
//
//
//        $action ='delete';
//        if(empty($action))
//            $this->error('请选择操作');
//        $result =model($model)->__changeStatus($id,$action);
//        if($result)
//            $this->success('修改成功',$_SERVER['HTTP_REFERER']);
//        else
//            $this->error('修改失败',$_SERVER['HTTP_REFERER']);
//
//    }
    public function equip_type_list(){
        $this->assign('_title','设备类型列表');
        $this->setCookie('equip_type_list');
        $this->setParam('equip_type_list');
        return $this->fetch();
    }
    public function getEquipTypeList()
    {
        $model = model('EquipmentType');
        $param = input();
        $param1 =$this->getParam('equip_type_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function equip_type_add(){

        $model =model('EquipmentType');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'EquipmentType');
            $back['url']=($back['code']==0)?'':$this->getCookie('equip_type_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'EquipmentType',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增设备类型':'修改设备类型';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function equip_type_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="EquipmentType";

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



    public function equip_price_list(){
        $this->assign('_title','产品价格列表');
        $this->setCookie('equip_price_list');
        $this->setParam('equip_price_list');
        return $this->fetch();
    }
    public function getEquipPriceList()
    {
        $model = model('ProductPrice');
        $param = input();
        $param1 =$this->getParam('equip_price_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function price_add(){

        $model =model('ProductPrice');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'ProductPrice');
            $back['url']=($back['code']==0)?'':$this->getCookie('equip_price_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'ProductPrice',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增产品价格':'修改产品价格';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function equip_price_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="ProductPrice";

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



    public function collector_list(){
        $this->assign('_title','采集器列表');
        $this->setCookie('collector_list');
        $this->setParam('collector_list');
        return $this->fetch();
    }
    public function getCollectorTypeList()
    {
        $model = model('CollectorType');
        $param = input();
        $param1 =$this->getParam('collector_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function collector_add(){

        $model =model('CollectorType');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'CollectorType');
            $back['url']=($back['code']==0)?'':$this->getCookie('collector_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'CollectorType',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增采集器':'修改采集器';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function collector_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="CollectorType";

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

    public function equip_list(){
        $this->assign('_title','设备列表');
        $this->setCookie('equip_list');
        $this->setParam('equip_list');
        return $this->fetch();
    }
    public function getEquipList()
    {
        $model = model('Equipment');
        $param = input();
        $param1 =$this->getParam('equip_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function equip_add(){

        $model =model('Equipment');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            $back =$model->__msave(input('post.'),'Equipment');
            $back['url']=($back['code']==0)?'':$this->getCookie('equip_list');
            if($back['code']==1){
                if(isset($back['id'])&& !empty($back['id'])){
                    model('Finance')->finance_add($back['id'],$data['product_id'],$data['pay_status']);
                }
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Equipment',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增设备':'修改设备';
            return $this->__edits($model,input('id'),null,$title);
        }
    }
    public function print_qrcode(){
        $equip=db('Equipment')->where('id',input('id'))->find();
//        $savePath = APP_PATH . '../public/qrcode/';
//        $webPath = '/qrcode/';
//        $qrData = $equip['qrcode'];
//        $qrLevel = 'H';
//        $qrSize = '10';
//        $savePrefix = 'Yujia';
//        if($filename = createQRcodeToQny($savePath, $qrData, $qrLevel, $qrSize, $savePrefix)){
//            $pic = $webPath . $filename;
//        }
        $htmlstr=$equip['qrcode_pic'];
        $assign=[
            'htmlstr'=>$htmlstr,
            'equip_code'=>$equip['equip_code'],
        ];
        $this->assign($assign);
        $model =model('Equipment');
        $title='打印二维码预览';
        return $this->__edits($model,input('id'),null,$title);
    }

    /*
 *
 */
    public function equip_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Equipment";

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
            case 'bf':
                $thestatus=2;
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

        if($action=='delete'){
            for($i=0;$i<$thecount;$i++){
                if($resultss[$i]['status']==2){
                    $this->error('选择的数据有误');
                }
            }
        }

//        if(!is_admin()){
//            $map['merchant_id']=MERID;
//        }


//        $map['is_root']=0;
        $map=[];

        $result =model($model)->__changeStatus($id,$action,$map);
        if($action=='bf'){
            $data1['status']=3;
            $result=db($model)->where($datass)->update($data1);
        }
        if($action=='delete'){
            $finance['object_id']=['in',$id];
            db('Finance')->where($finance)->delete();
        }
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
