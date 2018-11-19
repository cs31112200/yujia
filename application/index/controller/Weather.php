<?php
namespace app\index\controller;
use think\Db;
use think\Config;
use think\Request;


class Weather extends ApiBase
{
 
/*获取首页
 * @param string $city 城市名
 * @param 
 */
    public function getWeatherInit(){
    //    echo "测试:test.";exit;
        $this->__checkParam('city',$this->input);
        extract($this->input);
        $member_id=0;
        if(isset($this->behavior['user_id'])){
            $member_id=$this->verifyUser();
        }
        
        
        $return=[];
        $result =cache('weather_'.$city);
        $result =null;
        if(empty($result)){
            $json =  getWeather($city);
            if(empty($json)){
                $this->returnJson(0,'请求天气失败');
            }
            $result =json_decode($json,true);
        }else{
            $result =json_decode($result,true);
        };
        $return['four_weather']=$result;
        
        
        
        //获取广告列表
        $return['ad_list']=model('admin/Advert')->getAdvertBySpace(1);
        


        //获取预警列表
        $return['alarm_list']=model('MemberWarn')->getCityList($member_id);
//        $warnweather=model('MemberWarn')->getCityList($member_id);
//        foreach($warnweather as $k=>$v){
//            $time=time()-strtotime($v["warn_detail"]['issueTime']);
//            if(abs($time)<=86400*5){
//                $return['alarm_list']=$warnweather;
//            }else{
//                $return['alarm_list']['warn_detail']=[];
//            }
//        }
        $this->returnJsonData(1,'请求成功',$return);
    } 
    
  
/*设置天气预警城市
 * 
 */    
    public function setWeatherWarnCity(){
        $member_id =$this->verifyUser();
        $this->__checkParam('city',$this->input);
        extract($this->input);
        $city_list =model('admin/City')->getSelectCity();
     //   print_r($city_list);
        if(empty($city_list)){
            $this->returnJson(0,'您所选的城市有误');
        };
        $in=0;
        foreach($city_list as $k=>$v){
            if($v['city_name']==$city){
                $in=1;
            }
        }
        if($in==0){
            $this->returnJson(0,'您所选的城市有误');
        }
        $memberwarn=model('MemberWarn');
        $result =$memberwarn->addMemberCity($member_id,$city);
        if($result==false){
            $this->returnJson(0,$memberwarn->getError());
        }else{
            $this->returnJson(1, '添加成功');
        }
    }
    
 /*删除预警城市
  * 
  */   
    public function deleteWarnCity(){
        $member_id =$this->verifyUser();
        $this->__checkParam('ids',$this->input);
        extract($this->input);
        $ids_arr =explode(',',$ids);
        $member_city_list =model('MemberWarn')->getCityList($member_id);
        if(empty($member_city_list)){
            $this->returnJson(0,'您没有可删除的城市');
        }
        $member_ids =[];
        foreach($member_city_list as $k=>$v){
            $member_ids[]=$v['id'];
        };
        $intersect =  array_intersect($ids_arr,$member_ids);
        if($ids_arr !=$intersect){
            $this->returnJson(0,'删除的id有误');
        };
        
        $result =model('MemberWarn')->deleteMemberCity($ids);
        
        $this->returnJson(1,'删除成功');
        
    }
    
    
/*获取所有城市
 * 
 */
    public function getAllSetCity(){
        $result =model('admin/City')->getAllCity();
        $this->returnJsonData(1, '请求成功', $result);
    }
    
/*获取常用配置
 * 
 */    
    public function getCommonSetting(){
        
        //获取用户协议
        $config =model('admin/Config')->getAllValueToSave();
        $return['register_protocol']=$config['USER_REGISTER_PROTOCOL'];
        $return['ope_explain']=$config['OPE_EXPLAIN'];
        $return['polling_range']=$config['POLLING_RANGE'];
        $return['polling_time']=$config['POLLING_TIME'];
        $return['customer_service']=$config['CUSTOMER_SERVICE'];
        
        //获取小贴士
        $return['weather_tips']=$config['WEATHER_TIPS'];
        $configs =config('QNY_CLOUD');
        //获取城市
        $return['city_list']=model('admin/City')->getAllCity();
        $pool_type_list =model('PoolType')->getPooltype();
        $return['pool_type_list']=$pool_type_list;
        $return['qny_pre_url']=$configs['pre_url'];
        $return['version']=model('admin/Version')->getNewVersion();
        $this->returnJsonData(1, '请求成功', $return);
    }
    
/*获取用户消息
 * 
 */    
    public function getMessageByType(){
        $member_id =$this->verifyUser();
        $this->__checkParam('page,type',$this->input);
        extract($this->input);
        
        if($type!=1 && $type!=2 && $type!=3){
            $this->returnJson(0,'类型错误');
        }
        $page= intval($page);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }
        
        $page_size=10;
        
        $data['member_id']=$member_id;
        $data['type']=$type;
        $result =db('Message')->where($data)->order('create_time desc')->limit(($page-1)*$page_size.",".$page_size)->select();
        if(empty($result)){
            $this->returnJson(0,'已无更多消息');
        }else{
            foreach($result as $k=>$v){
                $result[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
                $result[$k]['update_time']=date('Y-m-d H:i:s',$v['update_time']);
            }
            $this->returnJsonData(1,'请求成功',$result);
        }
    }
    
/*读消息
 * 
 */    
    public function readMsg(){
        $member_id =$this->verifyUser();
        $this->__checkParam('ids',$this->input);
        extract($this->input);
        
        $id_arr =explode(',',$ids);
        $data['id']=['in',$id_arr];
        $data['member_id']=$member_id;
        
        $data1['update_time']=time();
        $data1['status']=1;
        $result =model('Message')->where($data)->update($data1);
        $this->returnJson(1,'操作成功');
    }
    
/*获取未读消息总数
 * 
 */    
    
    public function getNoReadCount(){
        $member_id =$this->verifyUser();
    //    $this->__checkParam('type',$this->input);
     //   extract($this->input);
        
//        if($type!=1 && $type!=2 && $type!=3){
//            $this->returnJson(0,'类型错误');
//        }
        $data['member_id']=$member_id;
      //  $data['type']=$type;
        $result =db('Message')->where($data)->select();
    //    $back[0]=0;
        $back['weather']=0;
        $back['warn']=0;
        $back['system']=0;
        if(!empty($result)){
            foreach($result as $k=>$v){
                if($v['status']==0){
                    if($v['type']==1){
                        $back['weather']++;
                    }
                    if($v['type']==2){
                        $back['warn']++;
                    }
                    if($v['type']==3){
                        $back['system']++;
                    }
                }
            }
        }
        $back['total']=$back['weather']+$back['warn']+$back['system'];
        $this->returnJsonData(1,'请求成功',$back);
        
        
    }
    
/*全部阅读消息
 * 
 */
    public function batchRead(){
        $member_id =$this->verifyUser();
        $this->__checkParam('type',$this->input);
        extract($this->input);
        $data['member_id']=$member_id;
        if($type>=1 && $type<=3){
            $data['type']=$type;
        }
        $data1['update_time']=time();
        $data1['status']=1;
        $result =model('Message')->where($data)->update($data1);
        $this->returnJson(1,'操作成功');
    }
    
}
