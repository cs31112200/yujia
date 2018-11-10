<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Client extends Base
{


//    public function city_list(){
//        $model =model('City');
//        $map  =$model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($model,null,'菜单列表',$map);
//    }
//
//
//    public function version_list(){
//        $model =model('Version');
//        $map  =$model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($model,null,'版本列表',$map);
//    }
//
//
//    public function version_add(){
//
//        $model =model('Version');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $back =$model->__msave($data,'Version');
//            $back['url']=($back['code']==0)?'':$this->getCookie();
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增版本':'修改版本';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//
//    public function city_add(){
//
//        $model =model('City');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'City');
//            $back['url']=($back['code']==0)?'':$this->getCookie();
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增城市':'修改城市';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//    public function pool_list(){
//        $model =model('PoolType');
//        $map  =$model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($model,null,'水池类型列表',$map);
//    }
//
//
//    public function pool_add(){
//
//        $model =model('PoolType');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'PoolType');
//            $back['url']=($back['code']==0)?'':$this->getCookie();
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增水池类型':'修改水池类型';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//
//    public function ques_list(){
//        $model =model('Question');
//        $map  =$model->getSelect(input());
//        cookie('__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
//        return $this->__lists($model,null,'常见问题列表',$map);
//    }
//
//
//    public function ques_add(){
//
//        $model =model('Question');
//        if(request()->isPost()){
//
//            /***自定义验证区域*****/
//
//            //存储
//            $data =input('post.');
//            $data['create_time']=time();
//            $back =$model->__msave($data,'Question');
//            $back['url']=($back['code']==0)?'':$this->getCookie();
//            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
//        }else{
//            $title =empty(input('id'))?'新增问题':'修改问题';
//            return $this->__edits($model,input('id'),null,$title);
//        }
//    }
//
//
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

    public function city_list(){
        $this->assign('_title','城市列表');
        $this->setCookie('city_list');
        $this->setParam('city_list');
        return $this->fetch();
    }
    public function getCityList()
    {
        $model = model('City');
        $param = input();
        $param1 =$this->getParam('city_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function city_add(){

        $model =model('City');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'City');
            $back['url']=($back['code']==0)?'':$this->getCookie('city_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Advert',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增城市':'修改城市';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function city_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="City";

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


    public function version_list(){
        $this->assign('_title','版本列表');
        $this->setCookie('version_list');
        $this->setParam('version_list');
        return $this->fetch();
    }
    public function getVersionList()
    {
        $model = model('Version');
        $param = input();
        $param1 =$this->getParam('version_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增版本
 *
 */
    public function version_add(){

        $model =model('Version');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Version');
            $back['url']=($back['code']==0)?'':$this->getCookie('version_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $model->setqrcode($theid);
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Version',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增版本':'修改版本';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function version_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Version";

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


    public function pool_list(){
        $this->assign('_title','水池列表');
        $this->setCookie('pool_list');
        $this->setParam('pool_list');
        return $this->fetch();
    }
    public function getPoolList()
    {
        $model = model('PoolType');
        $param = input();
        $param1 =$this->getParam('pool_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function pool_add(){

        $model =model('PoolType');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            dump($data);exit();
            $back =$model->__msave(input('post.'),'PoolType');
            $back['url']=($back['code']==0)?'':$this->getCookie('pool_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'PoolType',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增水池类型':'修改水池类型';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function pool_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="PoolType";

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


    public function ques_list(){
        $this->assign('_title','常见问题列表');
        $this->setCookie('ques_list');
        $this->setParam('ques_list');
        return $this->fetch();
    }
    public function getQuesList()
    {
        $model = model('Question');
        $param = input();
        $param1 =$this->getParam('ques_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function ques_add(){

        $model =model('Question');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Question');
            $back['url']=($back['code']==0)?'':$this->getCookie('ques_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Question',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增常见问题':'修改常见问题';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function ques_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Question";

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
