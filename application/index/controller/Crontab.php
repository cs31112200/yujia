<?php
namespace app\index\controller;
use think\Db;
use think\Config;
use think\Request;
header("Content-type:text/html;charset=utf-8");

class Crontab extends Base
{
 
/*每4小时定时获取
 * 
 */
    public function getWeatherByCity(){
        
        //首先获取城市
        $city_list =model('admin/City')->getSelectCity();
        if(empty($city_list)){
            return;
        }

        foreach($city_list as $k=>$v){
            
            //获取城市对应的天气
            $json =getWeatherCrontab($v['city_name']);
            
            if(empty($json)){
                continue;
            }
            $result =json_decode($json,true);


            //记录缓存
            cache('weather_'.$v['city_name'], formatWeather($json),14400);

            //判断是否有alarm_list  如果有 存入预警
            if(isset($result['showapi_res_body']['alarmList']) && !empty($result['showapi_res_body']['alarmList'])){
                $alarmlist =$result['showapi_res_body']['alarmList'];
//                print_r($alarm);exit;
                foreach($alarmlist as $k1=>$v1){
                    $weatherLog=model('WeatherLog');
                    $add_result=$weatherLog->addWeatherLog($v1['province'],$v1['city'],$v1['issueTime'],$v1['signalLevel'],$v1['signalType'],$v1['issueContent']);
//                    echo $weatherLog->getLastInsID();
//                    dump($add_result);
                    //如果add_result有增加那就表示有预警则需要报警
                    if($add_result==1){
                        
                        $last_id =$weatherLog->getLastInsID();
                      //  var_dump($last_id);
                        //获取当前城市被设定的用户群
                        $city_member_list =model('MemberWarn')->getCityMember($v['city_name']);
                    //    print_r($city_member_list);
                        $insert_arr=$alias_arr=$msg_data=[];
                        $time =date('Y-m-d',strtotime($v1['issueTime']));
                        if(!empty($city_member_list)){
                          //  continue;
                           //生成数据
                            foreach($city_member_list as $k2=>$v2){
                      //          continue;
                                $insert_arr[$k2]['member_id']=$v2['member_id'];
                                $insert_arr[$k2]['mwarn_id']=$v2['mwarn_id'];
                                $insert_arr[$k2]['weather_id']=$last_id;
                                $insert_arr[$k2]['create_time']=time();
                                
                                
                                //这个是消息批量插入
                                $msg_data[$k2]['member_id']=$v2['member_id'];
                                $msg_data[$k2]['title']=$time.$v['city_name']."天气预警";
                                $msg_data[$k2]['content']=$v1['issueContent'];
                                $msg_data[$k2]['type']=1;
                                $msg_data[$k2]['create_time']=time();
                                if(!empty($v2['jpush_id'])){
                                    $alias_arr[]=$v2['jpush_id'];
                                }
                            }
                            
                            //批量插入日志
                            model('MemberWarnLog')->insertAll($insert_arr);
                            
                            //批量插入消息
                            model('Message')->insertAll($msg_data);
                            
                            
                            //插入后报警 极光推送
                            
                            jpush_send(0,$alias_arr, $time.$v['city_name']."天气预警", $v1['issueContent'],'1');
                            
                           
                            
                        }
                    }
                    
                    
                }
            }
        }
    }
    
    
/*判断心跳包是否正常
 * 
 */    
    public function isHeartNormal(){
        
        
        //找出所有的key
        $redis = initRedis();
   //     $keyarr =$redis->keys('my*');
        $keyarr =$redis->keys('*_msg');
        if(empty($keyarr)){
            return ;
        }
      //  print_r($keyarr);
        foreach($keyarr as $k=>$v){
            
            //获取equip_code
            $temp_arr = explode('_', $v);
            
            
            $equip_code =$temp_arr[0];    
            //如果是空或者不规则，跳过
            if(empty($equip_code) || strlen($equip_code)!=12){
                continue;
            }
            
          //  echo $equip_code."<br>";
         //   continue;;
            $now_time =time();
            
            $last_time=0;
            $last_arr =$redis->get($v);
            
            $last_arr =json_decode($last_arr,true);
          //  print_r($last_arr);exit;
            
            if(empty($last_arr)){
                continue;
            }
            
            if(isset($last_arr['createTime']) && !empty($last_arr['createTime'])){
                $last_time=$last_arr['createTime'];
            }
            
           // var_dump($last_time);
            $prex =config('database.prefix');
            $equip_result =db('MemberEquip')->alias('a')
                    ->join($prex.'equipment b','a.equip_id=b.id','left')
                    ->join($prex.'member c','a.member_id=c.id','left')
                   ->field('a.equip_id,b.equip_code,a.member_id,a.is_connect,c.jpush_id,a.warm_phone,a.spare_phone,a.water_name,a.name')->where('b.equip_code="'.$equip_code.'"')->find();  

            if(empty($equip_result)){
                $redis->delete($v);
                continue;;
            }
            
            if(empty($last_time)) {
                continue;
            }
            
            $outline_time = sysC('OUTLINE_TIME');
            $outline_time =empty($outline_time)?300:$outline_time;
            //如果不为空且时间相差小于$outline_time秒就算正常
            if(($now_time-$last_time)<$outline_time){
                
                
                //
                if($equip_result['is_connect']==2){
                    $theconnect['equip_id']=$equip_result['equip_id'];
                    $theconnect1['is_connect']=1;
                    db('MemberEquip')->where($theconnect)->update($theconnect1);
                    //推送
                    jpush_send($equip_result['member_id'],$equip_result['jpush_id'],'设备状态','您的设备'.$equip_result['name'].'已连接正常',6);

                }
                
                continue;
            }
            
            

//            echo "thev:".$v."<br>";
        //    print_r($equip_result);echo "<br>";
            
            //判断设备状态是否正常
            if($equip_result['is_connect']==2){
                //状态已经停止，则不需要报警
                $last_arr['createTime']=0;
                 $redis->set($v,json_encode($last_arr));
                continue;
            }
            
            //推送
            jpush_send($equip_result['member_id'],$equip_result['jpush_id'],'设备状态','您的设备'.$equip_result['name'].'已经断线，请尽快到现场检查设备是否断电',5);
            
            
             $json_datas =json_encode(['equip_name'=>$equip_result['name']]);
             $warn_result =SendWarn($equip_result['warm_phone'],2,$json_datas);
             $warn_result2=1;
             if($warn_result==false && !empty($equip_result['spare_phone'])){
                 $warn_result2 =SendWarn($equip_result['spare_phone'],2,$json_datas);
             }
            
            $indata['member_id'] =$equip_result['member_id'];
            $indata['equip_id'] =$equip_result['equip_id'];  
            $indata['create_time']=time(); 
            $indata['send_date']=date('Y-m-d',$indata['create_time']); 
             //报警存储
             if($warn_result || $warn_result2){
                $indata['call_num'] =$warn_result?$equip_result['warm_phone']:$equip_result['spare_phone'];
                $indata['type']=1;
                
                $indata['content'] ="设备断线，报警成功";
              
             }else{
                $indata['call_num'] ="";
                $indata['type']=2;
            //    $indata['type']=2;
                $indata['content'] ="设备断线,报警失败";
                 //记录未报警log
                 
             }
             $ins =db('EquipWarn')->insert($indata);
             
             
             
             
             
            //修改equip_status
            
            $data1['is_connect']=2;
            $data2['equip_id']=$equip_result['equip_id'];
            
            $results =db('MemberEquip')->where($data2)->update($data1);
            echo "ins:".$ins."   res:".$results."<br>";
            //清除redis
            $last_arr['createTime']=0;
            $redis->set($v,json_encode($last_arr));
        }
        
    }
    
    
/*记录电话回执
 * 
 */    
    public function saveCallList(){
        $result =consumeMessage('');
        $redis = initRedis();
        save_log('savecall',json_encode($result));
        print_r($result);//exit;
        //确认消费数组
        $confirm=[];
        if(isset($result['messages']['tmc_message'])){
            
            $target =$result['messages']['tmc_message'];
            if(!isset($target[0])){
                $list[0]=$target;
            }else{
                $list=$target;
            }
            foreach($list as $k=>$v){
                save_log('savecall',json_encode($v));
                if(isset($v['topic']) && $v['topic']=='alibaba_aliqin_FcCallCdr'){
                    
                    
                    $content =json_decode($v['content'],true);
                    if(empty($content['extend'])){
                    //等于0 说明通话时间为0，未接通
                        if($content['duration']==0){
                            $redis->zIncrBy('no_pick_callnum_list',1,$content['biz_id']);
                        }else{

                            //表示打通了
                            $sdata['biz_id']=$content['biz_id'];

                            $result =db('PhoneVoice')->where($sdata)->find();

                            if($result['status']==0){
                            //    $sdata1['recall_count']
                                $sdata1['status']=1;
                                $thesave=db('PhoneVoice')->where($sdata)->update($sdata1);
                                    if($thesave){
                                    //删除
                                        $redis->zDelete('no_pick_callnum_list',$content['biz_id']);
                                    }

                            }


                        }
                    }else{
                        //标识打通
                     //   save_log('savecall',"remark1".$content['duration']);
                        if($content['duration']!=0){

                            //表示打通了
                            $sdata['biz_id']=$content['extend'];

                            $result =db('PhoneVoice')->where($sdata)->find();
                         //    save_log('savecall',"remark2:".json_encode($result));
                            if($result['status']==0){
                                $sdata1['status']=1;
                                $thesave=db('PhoneVoice')->where($sdata)->update($sdata1);
                                    if($thesave){
                                    //删除
                                        $redis->zDelete('no_pick_callnum_list',$content['biz_id']);
                                    }

                            }
                        }
                    }
                    
                    
                }
                //消费掉
                $confirm[]=$v['id'];
            }
            
        }
        if(!empty($confirm)){
            $confirm= implode(',', $confirm);
            //全部消费
            $theconfirm=confirmMessage($confirm);
            save_log('savecall',"confirm:".json_encode($theconfirm));
        }
        
    }
    
    
/*执行电话回执
 * 
 */
    
    public function toDoCall(){
      //  sleep(30);
        $redis = initRedis();
        $call_list =$redis->zrange('no_pick_callnum_list',0,-1,true);
      //  $call_list =$redis->zrange('mytest',0,-1,true);
      //  print_r($call_list);exit;
        if(empty($call_list)){
            return;
        }
        
        foreach($call_list as $k=>$v){
            
            //打电话小于10次才打
            if($v<10){
                
                //  通过biz_id打电话
                $data['biz_id']=$k;
                
                $result =db('PhoneVoice')->where($data)->find();
                
                //表示还没打通
                if($result['status']==0){
                    sendWarn($result['telephone'],$result['type'],$result['senddata'],$k);
                    
                    $data1['recall_count']=$result['recall_count']+1;
                    db('PhoneVoice')->where($data)->update($data1);
                    
                    $redis->zIncrBy('no_pick_callnum_list',1,$k);
                }else{
                    $redis->zDelete('no_pick_callnum_list',$k);
                }
                
                
                
            }else{
                $redis->zDelete('no_pick_callnum_list',$k);
            }
        }
        
    }
    
    
//    public function clockOpe(){
//        
//    }
    
    
/*清除非电流缓存 
 * 
 */    
    
    public function clear_equip_cache(){
        $redis = initRedis();
        $time =strtotime('2018-10-21 23:59:59');
     //   $time =strtotime(date('Y-m-d',time()))+600;
        $all_keys =$redis->keys('*_electric_history');
        if(!empty($all_keys)){
            foreach($all_keys as $k=>$v){
                $all_value =$redis->lrange($v,0,-1);
                $count =0;
                if(!empty($all_value)){
                    foreach($all_value as $k1=>$v1){
                        $the_value = json_decode($v1,true);
                        if($time>$the_value['time']){
                            $count++;
                        }
                    }
                    if($count>0){
                        $redis->ltrim($v,$count,-1); 
                    }
                    
               //     print_r($redis->lrange($v,0,-1));exit;
                }
            }
        }
        print_r($all_keys);
    }
    
/*清除采集器
 * 
 */    
    public function clear_collect_cache($redis=""){
        $redis = empty($redis)?initRedis():$redis;
        $d =input('d');
        $d =empty($d)?date('Y-m-d H:i:s',time()):$d;
        $time =strtotime($d);
        $all_keys =$redis->keys('*_sensor_data_history');
        if(!empty($all_keys)){
            foreach($all_keys as $k=>$v){
                $all_value =$redis->lrange($v,0,-1);
                
                $count =0;
                if(!empty($all_value)){
                    foreach($all_value as $k1=>$v1){
                        $the_value = json_decode($v1,true);
                        $remark=0;
                        foreach($the_value as $k2=>$v2){
                            if($time>$v2['time']){
                                $remark=1;
                            }
                        }
                        //如果该数据中存在时间吻合的，则计数+1
                        if($remark==1){
                            $count++;
                        }
                    }
                }
                
                if($count>0){
                    $redis->ltrim($v,$count,-1); 
                }
                
            }
        }
        print_r($all_keys);
    }
    
/*
 * 
 */
    public function save_collect_value(){
        set_time_limit(0);
        $redis = initRedis();
        $the_day =input('d');
        $the_day =empty($the_day)?date('Y-m-d',time()):$the_day;
        $first =strtotime($the_day." 00:00:00");
        $end =strtotime($the_day." 23:59:59");
        //获取总的member_equip
        $all_react =model('MemberEquip')->get_all_list();

        $all_keys =$redis->keys('*_sensor_data_history');
        $insert=[];
        if(!empty($all_keys)){
            foreach($all_keys as $k=>$v){
                
                //获取num与equip_code
                $temp_arr = explode('_', $v);
                if(count($temp_arr)!=4){
                    continue;
                }
               // $num =$temp_arr[0];
                $equip_code =$temp_arr[0];
                if(!isset($all_react[$equip_code])){
                    continue;
                }
                $member_id =$all_react[$equip_code];
                
                $all_value =$redis->lrange($v,0,-1);
                if(!empty($all_value)){
                    foreach($all_value as $k1=>$v1){
                        $save_arr = json_decode($v1,true);
                        if(!empty($save_arr)){
                            foreach($save_arr as $k2=>$v2){  
                              $the_time =$v2['time'];
                              if($the_time>=$first && $the_time<=$end){

                                  //获取小时，天数，月份，年份
                                  $hour =date('H',$the_time);
                                  $mini =date('i',$the_time);
                                  $insert_day =date('Y-m-d',$the_time);
                                  $month =date('Y-m',$the_time);
                                  $year =date('Y',$the_time);
                                  if(!isset($insert[$v."_".$hour."_".$mini])){
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['hour']=$hour;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['month']=$month;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['year']=$year;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['mini']=$mini;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['the_day']=$insert_day;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['oxygen']=$v2['dissolvedOxygen'];
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['temperature']=$v2['waterTemperature'];
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['ph']=$v2['PH'];
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['num']=$v2['number'];
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['equip_code']=$equip_code;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['member_id']=$member_id;
                                      $insert[$equip_code."_".$v2['number']."_".$hour."_".$mini]['create_time']=time();
                                  }
                              }
                            }
                        }
                    }
                }
            }
            $insert=array_values($insert);
            if(!empty($insert)){
                db('CollectorLog')->insertAll($insert);
            }
            
        }
        $this->clear_collect_cache($redis);
    }
    
/*消息存储
 * 
 */    
    public function save_message_log(){
        $redis= initRedis();
        $result =$redis->lrange('message_log',0,-1);
        
        $tui_data=$bj_data=[];
        foreach($result as $k=>$v){
            $temp_json = json_decode($v,true);
            $the_data =$temp_json['data'];
            $the_data = json_decode($the_data,true);
          //  print_r($the_data);exit;
            switch($temp_json['code']){
                
                //推送存储
                case 0:
                    $temp1['member_id']=$the_data['memberId'];
                    $temp1['create_time']=$the_data['createTime'];
                    $temp1['content']=$the_data['content'];
                    $temp1['title']=$the_data['title'];
                    $temp1['type']=2;
                    $temp1['status']=0;
                    $tui_data[]=$temp1;
                    
                    break;
                
                //报警存储
                case 1:
                    $temp2['telephone']=$the_data['telephone'];
                    $temp2['biz_id']=$the_data['bizId'];
                    $temp2['send_data']=$the_data['sendData'];
                    $temp2['temp']=$the_data['template'];
                    $temp2['type']=1;
                    $temp2['recall_count']=1;
                    $temp2['status']=0;
                    $bj_data[]=$temp2;
                    
                    break;
                default:
                    break;
            }
        }
        
        print_r($tui_data);
        print_r($bj_data);
    }
    
    
    
    
    public function test1(){
        $result_num =get_str(base_convert("00010000",2,16));
        encode_message_v3("00010000","040766302711","20",$result_num,"00","00","0005","000000","000000");
    }
    public function test(){
       sendWarn('15711542017',2,'{"equip_name":"\u4e2d\u9a8f"}');
    }
    public function test2(){
        print_r(cache('test'));
    }
    public function test3(){
        $callsid =input('sid');
        (queryCallResult($callsid));
    }
}
