<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Sheet extends Base{

/*
 * 
 */
    public function equip_sale(){
        
        $gd_config=config('GD_YUNTU');
        $this->assign('gd_config',$gd_config);
        $this->assign('_title','设备分布');
        return $this->fetch();
    }
    public function sale_list(){
        $this->assign('_title','设备销售列表');
        $this->setCookie('sale_list');
        $this->setParam('sale_list');
        return $this->fetch();
    }
    public function getSaleList()
    {
        $model = model('EquipSaleLog');
        $param = input();
        $param1 =$this->getParam('sale_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }

    public function finance_list(){
        $data=input();
        $fee=model('Finance')->getFee($data);
        $assign=[
            'fee'=>$fee,
            '_title'=>'财务管理列表',
        ];
        $this->assign($assign);
        $this->setCookie('finance_list');
        $this->setParam('finance_list');
        return $this->fetch();
    }
    public function getFinanceList()
    {
        $model = model('Finance');
        $param = input();
        $param1 =$this->getParam('finance_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*
*
*/
    public function finance_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Finance";

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
