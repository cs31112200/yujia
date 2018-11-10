<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Request;


class Agent extends Base
{

    public function agent_list(){
        $this->assign('_title','代理商列表');
        $this->setCookie('agent_list');
        $this->setParam('agent_list');
        return $this->fetch();
    }
    public function getAgentList()
    {
        $model = model('Agent');
        $param = input();
        $param1 =$this->getParam('agent_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    /*新增
 *
 */
    public function agent_add(){

        $model =model('Agent');
        if(request()->isPost()){

            /***自定义验证区域*****/
            $data=input('post.');
            //存储
            $back =$model->__msave(input('post.'),'Agent');
            $back['url']=($back['code']==0)?'':$this->getCookie('agent_list');
            if($back['code']==1){
                $theid =(isset($data['id'])&& !empty($data['id']))?$data['id']:$back['id'];
                $is_insert =(isset($data['id'])&& !empty($data['id']))?2:1;
                model('AdminLog')->addLog(UID,'Agent',$theid,$is_insert,json_encode($data));
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
    public function agent_change_status(){

        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);


        $model="Agent";

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
    public function apply_list(){
        $this->assign('_title','代理申请列表');
        $this->setCookie('apply_list');
        $this->setParam('apply_list');
        return $this->fetch();
    }
    public function getApplyList()
    {
        $model = model('AgentApply');
        $param = input();
        $param1 =$this->getParam('apply_list');
        $param = array_merge($param,$param1);
        $result = $model->getListData($param);
        $result = $this->__doList($model, $result);
        return ($result);
    }
    
/*获取市
 * 
 */
    public function getCityList(){
        $pro =input('province_name');
        if($pro =="北京" ||$pro =="上海"|| $pro =="天津"){
            $arr[0]['region_name']=$pro;
            $this->returnBack(1,'请求成功',$arr);
        }
        
        $cityresult =model('Area')->GetCityList($pro);
        $this->returnBack(1,'请求成功',$cityresult);
    }
 
/*获取区
 * 
 */
    public function getAreaList(){
        $pro =input('city_name');

        $cityresult =model('Area')->GetAreaList($pro);
        if(empty($cityresult))
            $this->returnBack(0,'暂无数据');
        $this->returnBack(1,'请求成功',$cityresult);
    }  
    
    
   public function agent_charge(){
       $model=model('AgentApply');
       if(request()->isPost()){
           $status =input('post.status',0,'intval');

           if($status !=2 && $status!=3){
               $this->error('请填写正确审核状态');
           }
           $last_msg =input('post.last_msg',"","trim");
           if($status==3 && empty($last_msg)){
               $this->error('请填写失败的审核理由');
           }


           $id =input('post.id',0,'intval');
           $datas['a.id']=$id;
           $results =db('AgentApply')->alias('a')
                   ->join('bd_wechat_user b','b.openid=a.openid','LEFT')
                   ->field('a.id,a.status,a.name,a.contact,a.province,a.city,a.area,a.address,b.openid,b.headimgurl')
                   ->where($datas)->find();
           if(empty($results)){
               $this->error('非法访问');
           }
           if($results['status']==2){
               $this->error('该代理已经审核通过');
           }
           $data['id']=$id;
           $data['status']=$status;
           $data['last_msg']=$last_msg;
           $data['update_time']=time();
           $result =db('AgentApply')->update($data);
           if($result){
               $insert['openid']=$results['openid'];
               $insert['headimgurl']=$results['headimgurl'];
               $insert['province']=$results['province'];
               $insert['city']=$results['city'];
               $insert['area']=$results['area'];
               $insert['address']=$results['address'];
               $insert['name']=$results['name'];
               $insert['contact']=$results['contact'];
               $insert['status']=$results['status'];
               $insert['account']=$results['contact'];
               $insert['password']=md5($results['contact']);
               $insert['create_time']=time();
               db('Agent')->insert($insert);
               $this->success('操作成功',$this->getCookie('apply_list'));
           }else{
               $this->error('操作失败');
           }

       }else{
           $id =input('id',0,'intval');
           $result =model('AgentApply')->get_detail($id);
           if(empty($result)){
               $this->error('非法访问');
           }
           if($result['status']==2){
               $this->error('该代理已经审核通过');
           }
           $result =model('AgentApply')->__formatEdit($result);
            $this->assign('details',$result);
            return $this->fetch('agent_charge',['_title'=>'审核代理']);
       }
   }
    
}
