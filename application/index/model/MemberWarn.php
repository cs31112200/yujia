<?php
namespace app\index\model;

use app\admin\model\Base;

class MemberWarn extends Base{

//自动填充时间    
    protected $autoWriteTimestamp = false;



    /*获取城市设置对应的用户
     *
     */
    public function getCityMember($city){
        $data['a.city']=$city;
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
            ->join($prex.'member b','a.member_id=b.id','LEFT')
            ->field('a.id,b.jpush_id,a.member_id')->where($data)->select();
        $return=[];
        if(!empty($result)){
            foreach($result as $k=>$v){
                $return[$k]['member_id']=$v['member_id'];
                $return[$k]['mwarn_id']=$v['id'];
                $return[$k]['jpush_id']=$v['jpush_id'];
            }
        }
        return $return;
    }

    /*获取用户设置的城市列表
     *
     */
    public function getCityList($member_id){
        if($member_id==0){ return [];};
        $result =db($this->getTheTable())->where('member_id='.$member_id)->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
//                $result[$k]['warn_detail']=model('MemberWarnLog')->getMemberWeather($v['id']);
                $MemberWarnLog=model('MemberWarnLog')->getMemberWeather($v['id']);
                $time=time()-strtotime($MemberWarnLog['issueTime']);
                if(abs($time)<=86400*5) {
                    $result[$k]['warn_detail'] = $MemberWarnLog;
                }else{
                    $result[$k]['warn_detail']=[];
                }
            }
        }
        return $result;
    }

    /*添加
     *
     */
    public function addMemberCity($member_id,$city){
        $data['member_id']=$member_id;
        $data['city']=$city;
        $count =db($this->getTheTable())->where($data)->count();

        if($count>0){
            $this->setError('您已经添加过该城市');
            return false;
        }
        $data['create_time']=time();
        $this->insert($data);
        return true;
    }

    /*批量删除
     *
     */
    public function deleteMemberCity($ids){
        $data['id']=['in',$ids];
        $result =$this->where($data)->delete();
        return true;
    }
}