<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Advert extends Base
{

    public function advert_list(){
        $this->assign('_title','广告列表');
        $this->setCookie('advert_list');
        $this->setParam('advert_list');
        return $this->fetch();
    }
    public function getAdvertList()
    {
        $model = model('Advert');
        $param = input();
        $param1 =$this->getParam('advert_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function advert_add(){

        $model =model('Advert');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Advert');
            $back['url']=($back['code']==0)?'':$this->getCookie('advert_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Advert',$theid,$is_insert,json_encode($data));
            }


            $this->returnBack($back['code'],$back['msg'],'',$back['url']);
        }else{
            $title =empty(input('id'))?'新增广告':'修改广告';
            return $this->__edits($model,input('id'),null,$title);
        }
    }

    /*
 *
 */
    public function advert_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Advert";

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
