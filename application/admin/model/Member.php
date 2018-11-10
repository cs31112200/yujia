<?php
namespace app\admin\model;

use app\admin\model\Base;

class Member extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



/*筛选
 * 
 */
    public function getSelect($param=null) {
        $map =[];
        if(isset($param['account'])){
            $map['account']=$param['account'];
        }
        return $map;
    }
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['account']) && !empty($param['account'])){
            $map['account']=$param['account'];
        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
//        $prex =config('database.prefix');
//
//        $sql =$this->alias('a')
//            ->join($prex.'MemberEquip b','a.id=b.member_id','left')
//            ->field('a.id,a.name,a.avator,a.province,a.city,a.area,a.account')
//            ->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();

        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
       if(!empty($list)){
           foreach($list as $k=>$v){
               $list[$k]['avators']=  generalImg($v['avator']);
               $member_equip= db('MemberEquip')->where('member_id',$v['id'])->select();
               $list[$k]['equip_count']= count($member_equip);
               $list[$k]['status']= $list[$k]['status']==1?'正常':'失效';
               $list[$k]['create_time']= date('Y-m-d H:m:s',$list[$k]['create_time']);
           }
       }
       return $list;
    }

    public function __formatEdit($data = null) {

        return $data;
    }
    public function __my_before_update(&$data){
        if(empty($data['newpasswd'])){
            $this->setError('请输入重置密码');
            return false;
        }
        if(empty($data['renewpasswd'])){
            $this->setError('请再次输入重置密码');
            return false;

        }
        if($data['newpasswd']!==$data['renewpasswd']){
            $this->setError('您两次输入的密码不一致');
            return false;
        }
        else{
            $res['passwd']=md5($data['newpasswd']);
            $result=db('Member')->where('id',$data['id'])->update($res);
//            if($result){
            unset($data['newpasswd']);
            unset($data['renewpasswd']);
            return true;
//            }else{
//                $this->setError('请输入新的密码');
//                return false;
//            }

        }

    }

    /*插入之前
     *
     */

    public function __my_before_insert(&$data){

        $data['status']=1;
        $data['create_time']=time();
        return TRUE;
    }
    public function getAccountList(){
        $map=[];
        $result = db($this->getTheTable())->where($map)->field('account')->group('account')->select();
        return $result;
    }
}