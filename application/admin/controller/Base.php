<?php
namespace app\admin\controller;
use think\Controller;
use think\Config;
use think\Db;
use think\Request;
/*基础基类
 * 
 */
class Base extends Controller
{

    /**
     * 初始化
     */
    protected function _initialize(){
        // 判断是否登录，否则返回
        // 获取当前用户ID
        $result =is_login();
        // echo session('agentAdmin');exit;
        if( !$result ){// 还没登录 跳转到登录页面
            $this->redirect('Login/login');
        };
        $this->assign('__username',$result['name']);
        define('UID',$result['id']);
        if(!is_admin()){

            $check_menu =$this->checkAuthByMenu();
            if(!$check_menu){
                $this->error('无权限访问');
            }
        }
    }

    /**
     * 通用分页列表数据集获取方法
     * @param array where  指定条件 where('id',1') 或者 where(['id'=>'1'])
     * @param array order ('id','desc') 或者 order(['id'=>'desc','create_time'=>'desc'])
     * @param string filed  指定筛选字段
     * @param string method 要处理list的方法名(这个函数写在对应model里面)
     * @param string view 要渲染的页面模板名称
     * @param string title 每个页面的title
     * @param object $model 实例化model
     * @return array|false
     * 返回数据集
     */
    protected function __lists($model,$method=null,$title='',$where=[],$order='',$field='',$page_size=null,$view=null){
        $page_size =($page_size<=0)?10:$page_size;

        $list = $model->where($where)->field($field)->order($order)->paginate($page_size);

        $method =empty($method)?'__formatList':$method;
        $list =$model->$method($list);
        //print_r($list);exit;
        // 获取分页显示
        $page = $list->render();
        $this->assign('_page', $page);
        $this->assign('_list',$list);

        //实例化要渲染的页面
        $view = empty($view)?request()->action():$view;
        return $this->fetch($view,['_title'=>$title]);
    }

    /*普通 添加/编辑页面初始化
     * @param object $model 实例化model
     * @param string method 要处理edit的方法名(这个函数写在对应model里面)
     * @param string view 要渲染的页面模板名称
     * @param string title 每个页面的title
     *
     */
    protected function __edits($model,$id=0,$method=null,$title='',$view=null){
        $result =array();

        //传主键id
        if(intval($id)>0){
            $pk =$model->getPk();
            $data[$pk]=$id;
            $result =$model->where($data)->find();
        };
        $method =empty($method)?'__formatEdit':$method;
        $result =$model->$method($result);
        $this->assign('app',$result);

        //实例化要渲染的页面
        $view = empty($view)?request()->action():$view;
        $this->assign('_toMethod',$view);
        return $this->fetch($view,['_title'=>$title]);
    }

    /*返回消息
     * @param int code 状态码  1，0
     * @param string msg 消息
     * @param array data 数据
     * @param string url 地址
     */
    public function returnBack($code,$msg='',$data='',$url=''){
        if($code==1){
            $this->success($msg,$url,$data);
        }else{
            $this->error($msg, $url, $data);
        }
    }

    //检验用户身份 与要操作的id是否匹配
    /*
     * @param int id 要检测的id
     * @param string $table 检测table
     * @param string $upk user_id 在表里的名称
     * @param string $ipk id 在表里的名称
     */
    public function checkAuth($id,$table,$upk,$ipk=''){
        $ipk= empty($ipk)?'id':$ipk;
        $condition[$ipk]=$id;
        $condition[$upk]=UID;
        return db($table)->where($condition)->count();
    }
    //检验用户身份 与要操作的id是否匹配 成功返回列表
    /*
     *
     */
    public function checkAuthResult($id,$table,$upk,$ipk=''){
        $ipk= empty($ipk)?'id':$ipk;

        $condition[$ipk]=$id;
        $result =db($table)->find($id);
        if(empty($result)){
            return false;
        }
        if($result[$upk]!=MERID && MERID!=0){
            return false;
        }
        return $result;
    }

    /*查重
     *
     */
    public function checkExit($model_names,$data,$check_id=0){
        $model_arr =explode(',',$model_names);
        foreach($model_arr as $k=>$v){
            $check_result =model($v)->isExit($data,$check_id);
            if($check_result['code']==0)
                $this->error($check_result['msg']);
        }

    }


    /*统一修改状态
     * @param string model_name 对应model名称
     * @param int id 要修改的id类型
     * @param string action 操作的名称
     * @param int $uid 对应的用户id
     * @pram string key_name 用户id的字段名称
     */



    public function changeStatus($model){
        $id = input('id/a');
        if(is_array($id)) sort($id);
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $ids= explode(',', $id);
        $id_count=count($ids);

        $action =input('ope');
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
                $this->error('操作类型有误');
        };

        $datass['id'] =['in',$id];
        $resultss =db($model)->where($datass)->select();
        $thecount=count($resultss);
        if($thecount!=$id_count){
            $this->error('选择的数据不匹配');
        }


        $result =model($model)->__changeStatus($id,$action);
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
            $this->error('修改失败');
    }

    /*设置本页cookie
     *
     */
    public function setCookie($name=""){
        cookie($name.'__forward__','http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        return true;
    }
    /*返回本页cookie
     *
     */
    public function getCookie($name=""){
        return cookie($name.'__forward__');
    }

    /*菜单权限判断
     *
     */
    public function checkAuthByMenu(){


        $request = Request::instance();
        $name=  getSessionName();
        $user = session($name.'_auth');
        //  return true;
        if($user['is_root']==1){
            return true;
        }

        $group_menu_list =model('admin/UserAuth')->getGroupMenuByadmin($user['id']);
        //print_r($group_menu_list);exit;
        $menu_index="/".$request->controller()."/".$request->action();
        if($menu_index="/Index/index"){
            return true;
        }
        // echo $menu_index;exit;
        //验证
        $check=0;

        foreach($group_menu_list as $k=>$v){
            if($menu_index==$v['href']){
                $check=1;
            }
        }
        return $check;
    }


    /*执行listsql
     * @param object $model 模型对象
     * @param string $sql 执行语句
     * @param string method 结果处理函数
     */
    public function __doList($model,$result,$method="__formatList"){
        if(!empty($result['data'])){
            $result['data']=$model->$method($result['data']);
        };
        return ($result);
    }

    /*设置参数
     *
     */
    public function setParam($name='',$arr=[]){
        $param =input();
        if(!empty($arr)){
            $param = array_merge($param,$arr);
        }

        cookie($name.'_p',$param);
        return true;
    }

    /*获取参数
     *
     */
    public function getParam($name=''){
        $result = cookie($name.'_p');
        return empty($result)?[]:$result;
    }




//    
///*基本的change_status
// * 
// */    
//    public function base_change_status($model){
//        $id = input('id/a');
//        $type=input('type',0);
//        if(is_array($id)) sort($id);
//        $id = is_array($id) ? implode(',',$id) : $id;
//        if ( empty($id) ) {
//            $this->error('请选择要操作的数据!');
//        }
//        $ids= explode(',', $id);
//        $id_count=count($ids);
//        
//        
//        if(empty($model))
//            $this->error('模型错误');
//
//        
//        $action =input('post.ope');
//        $thestatus=0;
//        switch($action){
//            case 'open':
//                $thestatus=3;
//                break;
//            case 'close':
//                 $thestatus=4;
//                break;
//            case 'delete':
//                 $thestatus=5;
//                break;
//            default:
//                $this->error('操作类型有误');
//        };
//        
//        $datass['id'] =['in',$id];
//        $resultss =db($model)->where($datass)->select();
//        $thecount=count($resultss);
//        if($thecount!=$id_count){
//             $this->error('选择的数据不匹配');
//        }
//        
//        
//
//        $model=model($model);
//        $result =$model->__changeStatus($id,$action);
//        if($result){
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
//            $this->error('修改失败');
//    }
    /*public function saveAll($saveWhere,&$saveData,$tableName){
        if($saveWhere==null||$tableName==null)
        {
            return false;
        }//获取更新的主键id名称
        $key=array_keys($saveWhere)[0];
//获取更新列表的长度
        $len=count($saveWhere[$key]);
        $flag=true;
        $model=isset($model)?$model:model($tableName);
//开启事务处理机制
        Db::startTrans();
//记录更新失败ID
        $error=[];
        for($i=0;$i<$len-1;$i++){
//预处理sql语句
            $isRight=$model->where($key.'='.$saveWhere[$key][$i])->save($saveData[$i]);
            if($isRight==0){
//将更新失败的记录下来
                $error[]=$i;
                $flag=false;
            }
//$flag=$flag&&$isRight;
        }
        if($flag){
//如果都成立就提交
            Db::commit();
            return $saveWhere;
        }elseif(count($error)>0&count($error)<$len){
//先将原先的预处理进行回滚
            Db::rollback();
            for($i=0;$i<count($error);$i++){
//删除更新失败的ID和Data
                unset($saveWhere[$key][$error[$i]]);
                unset($saveData[$error[$i]]);
            }
//重新将数组下标进行排序
            $saveWhere[$key]=array_merge($saveWhere[$key]);
            $saveData=array_merge($saveData);
//进行第二次递归更新
            $this->saveAll($saveWhere,$saveData,$tableName);
            return $saveWhere;
        }
        else{
//如果都更新就回滚
            Db::rollback();
            return false;
        }
    }*/

}
