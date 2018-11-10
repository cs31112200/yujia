<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Config;
// 应用公共文件
use think\Request;
use think\Log;

//初始化Redis
function initRedis(){
        Config::load(APP_PATH.'redis.php');
        $conf = Config::get('redis');
        import('redis.RedisClient',EXTEND_PATH);
        $redis =new \RedisClient($conf);
        return $redis;
}



//初始化微信
function initWechat(){
        Config::load(APP_PATH.'wechat.php');
        $conf = Config::get('wechat');
        import('wechat.Wechat',EXTEND_PATH);
        $wechat =new \Wechat($conf);
        return $wechat;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)){
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string 
 */
function think_ucenter_encrypt($data, $key, $expire = 0) {
	$key  = md5($key);
	$data = base64_encode($data);
	$x    = 0;
	$len  = strlen($data);
	$l    = strlen($key);
	$char =  '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x=0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	$str = sprintf('%010d', $expire ? $expire + time() : 0);
	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
	}
	return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string 
 */
function think_ucenter_decrypt($data, $key){
	$key    = md5($key);
	$x      = 0;
	$data   = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data   = substr($data, 10);
	if($expire > 0 && $expire < time()) {
		return '';
	}
	$len  = strlen($data);
	$l    = strlen($key);
	$char = $str = '';
	for ($i = 0; $i < $len; $i++) {
		if ($x == $l) $x = 0;
		$char  .= substr($key, $x, 1);
		$x++;
	}
	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		}else{
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}
	return base64_decode($str);
}




/*
 * 
 */
function createSn($name=''){
    $day =date('Ymd',time());
    $now_day =empty(cache($name.'nowday'))?$day:cache($name.'nowday');
    $now_num =(empty(cache($name.'nowcount')) || $now_day!=$day)?1:cache($name.'nowcount');
       //     cache('nowcount');
    
    $sn =$name.$day.sprintf("%05d", $now_num);
    if($now_day!=$day){
        cache($name.'nowday',$day);
    }
    cache($name.'nowcount',$now_num+1);
    return $sn;
   // return $name.uniqid().$str[mt_rand(0,$count-1)].mt_rand(10000,99999);
}
/*大小写转换_
 * 类似AgentAudit => agent_audit
 */    
 function __classToStr($string){
        $len =strlen($string);
        $new ="";
        for($i=0;$i<$len;$i++){
            $old =ord($string[$i]);
            if($old>64 && $old<91 && $i!=0){
                 $new =$new.'_'.strtolower($string[$i]);
            }else{
                $new .= strtolower($string[$i]);
            }
        }
        return $new;
    }
/*大小写转换_
 * 类似si_merchant_product => merchant_product
 */    
 function __classToStrs($string){
        $new =  str_replace('si_','', $string);
        return $new;
    }

/*检验是否为空
 * 
 */
function checkEmpty($value) {
    if (!isset($value))
            return true;
    if ($value === null)
            return true;
    if (trim($value) === "")
            return true;

    return false;
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login(){
    $name=  getSessionName();
    $user = session($name.'_auth');
    if (empty($user)) {
        return 0;
    } else {
        return session($name.'_auth_sign') == data_auth_sign($user) ? $user : 0;
    }
}

/**
 * 检测用户是否是超级管理员
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_admin(){
    $name=  getSessionName();
    $user = session($name.'_auth');
    return $user['id']==1?1:0;
}


/*清除session
 * 
 */

function clearSession(){
    $name =getSessionName();
    session($name.'_auth', null);
    session($name.'_auth_sign', null);
    session($name.'_dl_auth',null); 
    
   // $redis = initRedis();
    //$redis->delete("PHPREDIS_SESSION:".session_id());
    
    return true;
}

/*设置session
 * 
 */
function settingSession($auth){
    $name =getSessionName();
    session($name.'_auth', $auth);
    session($name.'_auth_sign', data_auth_sign($auth));
    return true;
}
/*设置当前模块session名称
 * 
 */
function setSessionName($name){
    $request = Request::instance();
    $module =$request->module();
   session($module.'_login_session_name',$name); 
   return true;
}

/*获取当前模块session名称
 * 
 */
function getSessionName(){
    $request = Request::instance();
    $module =$request->module();
    return session($module.'_login_session_name');
}


/*获取一个字符串里面的所有数字
 * 
 */
function getNumInString($string){
  $patterns = "/\d+/";
  preg_match_all($patterns,$string,$arr);
  return implode('',$arr[0]);
}

/*图片
 * 
 */
function generalImg($img){
    
    return empty($img)?"":"http://".$_SERVER['HTTP_HOST'].$img;
}     
function generalAvatorImg($img){
    
    return empty($img)?"":"http://".$_SERVER['HTTP_HOST'].$img;
}     
function generalEquipImg($img){
    
    return empty($img)?"http://".$_SERVER['HTTP_HOST']."/img/default_img.png":"http://".$_SERVER['HTTP_HOST'].$img;
}  

function generalQnyImg($img){
    $config =config('QNY_CLOUD');
    return empty($img)?"":"http://".$config['pre_url']."/".$img;
}

/*修改数字
 * @param type 1: 1=>01 ,11=>11;;;;2:1=>001,11=>011,111=>111
 * 
 */

function transNum($num,$type=1){
    if($type==1){
        
        if($num<10){
            $num = "0".$num;
        };
    }else{
        if($num<10){
            $num = "00".$num;
        }else if($num <100){
            $num ="0".$num;
        };
    }
    return $num;
}

/*阿里大于 发送短信
 * @param int type 1注册，2找回密码，3验证首选，4验证备选 5删除模版
 */
function SendCodes($tel,$type){
    include EXTEND_PATH."alivoice".DS."Topsdk.php";
    $setting=config('ALI_VOICE');
    $temp=config('MSG_TEMP');
    
    
    if(!isset($temp[$type])){
        $back['msg']="短信模版错误";
        $back['status']=0;
        return $back;
    }
    
    $app_name =config('APP_NAME');
    extract($setting);
    $code =rand(100000,999999);
    $c = new \TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secretKey;
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($sign_name);
    $json =json_encode(['product'=>"$app_name",'code'=>"$code"]);
   // echo $json;exit;
    $req->setSmsParam("$json");
    $req->setRecNum($tel);
    $req->setSmsTemplateCode($temp[$type]);
    $resp = $c->execute($req);
    $resp=json_decode( json_encode( $resp),true);
  //  var_dump($resp);//exit;
    if(isset($resp['result']) && isset($resp['result']['success']) && $resp['result']['success']=="true"){
        model('index/PhoneVerify')->addVerify($tel,$code,$type);
        $back['msg']="发送成功";
        $back['status']=1;
        return $back;
    }else{
        $back['msg']="发送失败";
        $back['status']=0;  
        return $back;
    }
  //   print_r($resp);
}

/*阿里大于发送语音报警
 * 
 */
//function SendWarns($tel,$user_name,$equip_name,$elec,$type){
//    include EXTEND_PATH."alivoice".DS."Topsdk.php";
//    $setting=config('ALI_VOICE');
//   // $warn_voice =config('ALI_WARN_VOICE');
//    extract($setting);
//    $c = new \TopClient;
//    $c->appkey = $appkey;
//    $c->secretKey = $secretKey;
//    $arr =['name'=>"$user_name",'equip_name'=>"$equip_name",'elec'=>"$elec"."安"];
//    $json =json_encode($arr);
//    $req = new AlibabaAliqinFcTtsNumSinglecallRequest;
//    $req->setTtsParam("$json");
//    $req->setCalledNum("$tel");
//    $req->setCalledShowNum("$show_tel");
//    $req->setTtsCode("$tts_temp");
//    $resp = $c->execute($req);
//    $resp=json_decode( json_encode( $resp),true);
//   // print_r($resp);exit;
//   // var_dump($resp);//exit;
//    if(isset($resp['result']) && isset($resp['result']['success']) && $resp['result']['success']=="true"){
//        
//        
//        //记录
//        $biz_id =$resp['result']['model'];
//        $data['biz_id']=$biz_id;
//        
//        
//        $result =db('PhoneVoice')->where($data)->find();
//        
//        //空就执行插入
//        if(empty($result)){
//            $data['telephone']=$tel;
//            $data['senddata']=$json;
//            $data['tmp']=$tts_temp;
//            $data['type']=$type;
//            $data['create_time']=time();
//            db('PhoneVoice')->insert($data);
//        }else{
//            $data1['recall_count']=$result['recall_count']+1;
//            db('PhoneVoice')->where($data)->update($data1);
//        }
//            
//        
//        return true;
//    }else{
//        return false;
//    }
//}

/* @param string $tel 电话
 * @param int $type 1故障2断线
 * @param json $json_data json格式的数组
 * @param string $extend 传回打的biz_id
 */

function sendWarn($tel,$type,$json_data,$extend=""){
    
    switch($type){
        case 1:
            $tts_temp="TTS_130830421";
            break;
        case 2:
            $tts_temp="TTS_130845353";
            break;
        default:
            return false;
            break;
    }
    
    include EXTEND_PATH."alivoice".DS."Topsdk.php";
    $setting=config('ALI_VOICE');
   // $warn_voice =config('ALI_WARN_VOICE');
    extract($setting);
    $c = new \TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secretKey;
//    $arr =['name'=>"$user_name",'equip_name'=>"$equip_name",'elec'=>"$elec"."安"];
//    $json =json_encode($arr);
    $req = new AlibabaAliqinFcTtsNumSinglecallRequest;
    $req->setTtsParam("$json_data");
    if(!empty($extend)){
        $req->setExtend("$extend");
    }
    $req->setCalledNum("$tel");
    $req->setCalledShowNum("$show_tel");
    $req->setTtsCode("$tts_temp");
    $resp = $c->execute($req);
    $resp=json_decode( json_encode( $resp),true);
  //  print_r($resp);exit;
   // var_dump($resp);//exit;
    if(isset($resp['result']) && isset($resp['result']['success']) && $resp['result']['success']=="true" ){
        
        
        //记录
        $biz_id =$resp['result']['model'];
        $data['biz_id']=$biz_id;
        $data['telephone']=$tel;
        $data['senddata']=$json_data;
        $data['tmp']=$tts_temp;
        $data['type']=$type;
        $data['create_time']=time();
        if(!empty($extend)){
            $data['parent_biz']=$extend;
            $data['status']=1;
        }
        db('PhoneVoice')->insert($data);
        
        if(!empty($extend)){
            $data2['biz_id']=$extend;
            db('PhoneVoice')->where($data2)->setInc('recall_count');
        }
        

        
        return true;
    }else{
        return false;
    }
}


/*确认消息
 * 
 */

function confirmMessage($ids,$group_name=''){

    include_once EXTEND_PATH."alivoice".DS."Topsdk.php";
    $setting=config('ALI_VOICE');
    extract($setting);
    $c = new \TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secretKey;
    $req = new TmcMessagesConfirmRequest;
    if(!empty($group_name)){
        $req->setGroupName($group_name);
    }
    $req->setsMessageIds($ids);

    $resp = $c->execute($req);
   // var_dump($resp);exit;
     $resp=json_decode( json_encode( $resp),true);
     print_r($resp);
     return $resp;
}

/*阿里消费消息
 * 
 */
function consumeMessage($group_name,$quilty=200){
    include_once EXTEND_PATH."alivoice".DS."Topsdk.php";
    $setting=config('ALI_VOICE');
    extract($setting);
    $c = new \TopClient;
    $c->appkey = $appkey;
    $c->secretKey = $secretKey;
    $req = new TmcMessagesConsumeRequest;
    if(!empty($group_name)){
        $req->setGroupName($group_name);
    }
    $req->setQuantity($quilty);
    $resp = $c->execute($req);
   // var_dump($resp);exit;
     $resp=json_decode( json_encode( $resp),true);
     return $resp;
    // print_r($resp);
}


/*阿里确认消息
 * 
 */


/*
 * 发送验证码
 * @param string $tel
 */

function SendCodess($tel,$type){


        Vendor("verficode.yuntongxun");
        $accountSid = 'aaf98f8952b6f5730152e3fabcdf2c43';
        $accountToken = '67efef8f82ac45baa24c8218c088b1bf';
        $appId = 'aaf98f8952f255490152f3a18d4d0402';
        $serverIP = 'sandboxapp.cloopen.com';
        $serverPort = '8883';
        $softVersion = '2013-12-26';
        $minute =30;
        import('verficode.Yunmsg',EXTEND_PATH);
        $rest = new \Yunmsg($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);
        $veriry =rand(100000,999999);
        
        $result = $rest->sendTemplateSMS($tel, array($veriry, $minute), 68639);
     //   $this->returnJson(1,json_encode($result));
      //  print_r($result);exit();
        if ($result == NULL) {
                $back['msg']="发送失败";
                $back['status']=0;
                return $back;
        }
        if ($result->statusCode != 0) {
                $back['msg']="发送失败";
                $back['status']=0;  
                return $back;
        } else {
            model('PhoneVerify')->addVerify($tel,$veriry,$type);
            $back['msg']="发送成功";
            $back['status']=1;
            return $back;
        }    
}






/*数字串插入字符串
 * 
 */
function str_insert($str, $i, $substr) 
{ 
	$startstr=$laststr='';
    for($j=0; $j<$i; $j++){ 
    $startstr .= $str[$j]; 
    } 
    for ($j=$i; $j<strlen($str); $j++){ 
    $laststr .= $str[$j]; 
    } 
    $str = ($startstr . $substr . $laststr); 
    return $str; 
}
/*形成字符串
 * 
 */
function doCile($num,$string){
	$rand_num =rand(0,strlen($num));
	return str_insert((string)$num,$rand_num,$string);
	
}
/*生成二维码
 * 
 */
function getQrcode($num,$strlen="20"){
	$str ='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$length = $strlen-strlen($num);
	for($i=0;$i<$length;$i++){
		$rand_string =$str[rand(0,51)];
		$num= doCile($num,$rand_string);
	}
	return $num;
}

/*获取配置值
 * 
 */
function sysC($key=''){
    $result =model('admin/Config')->getAllValueToSave();
    
    if(empty($key))
        return $result;
    
    if(!empty($key) && !isset($result[$key]))
        return null;
    return $result[$key];
}


/*TCP 长连接
 * @param string $protocol 协议
 * @param string $ip  ip地址
 * @param int port 端口
 * @param array $data 传输的数据
 * @param int $type 1开通管道2关闭管道
 */
function tcpLongConnect($data,$type){
    $config =config('TCP_CONFIG');
    $connect =$config['protocol']."://".$config['ip'].":".$config['port'];
    // 建立socket连接到内部推送端口
    $client = stream_socket_client($connect, $errno, $errmsg, 5);
    if (!$client) {
        $back['msg']=$errmsg;
        $back['code']=0;
        return $back;
    }
    $data['type']=$type;
    $data['sign']=config('LONG_KEY');
    // 推送的数据，包含uid字段，表示是给这个uid推送
    fwrite($client, json_encode($data));
    // 读取推送结果
    $result= fread($client, 8192);
    $result =  json_decode($result,true);
    return $result;
    

}

/*获取天气预报
 * 
 */
    function getWeather($city){
        $ch = curl_init();
        $key_array = Config::get('WEATHER_KEY');
        $need =[
            'area'=>$city,
            'needAlarm'=>1,
            'needMoreDay'=>1,
            'needHourData'=>0,
            'showapi_appid'=>$key_array['app_id']
        ];
        ksort($need);
      //  print_r($need);
        //拼接str 
        $str="";
        foreach($need as $k=>$v){
            $str.=$k.$v;
        };
        $str.=$key_array['secret'];
        $md5_sign =md5($str);
        $need['showapi_sign']=$md5_sign;
        
        $param =http_build_query($need);
        $url ="http://route.showapi.com/9-2/?".$param;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        $result =formatWeather($res);
        return $result;
    }
    
    function getWeatherCrontab($city){
        $ch = curl_init();
        $key_array = Config::get('WEATHER_KEY');
        $need =[
            'area'=>$city,
            'needAlarm'=>1,
            'needMoreDay'=>1,
            'needHourData'=>0,
            'showapi_appid'=>$key_array['app_id']
        ];
        ksort($need);
      //  print_r($need);
        //拼接str 
        $str="";
        foreach($need as $k=>$v){
            $str.=$k.$v;
        };
        $str.=$key_array['secret'];
        $md5_sign =md5($str);
        $need['showapi_sign']=$md5_sign;
        
        $param =http_build_query($need);
        $url ="http://route.showapi.com/9-2/?".$param;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        
        return $res;        
    }
    
function formatWeather($res){
        $result =json_decode($res,true);
        if(!isset($result['showapi_res_code']) || $result['showapi_res_code']!=0){
           return null;
        }else{
            $result =$result['showapi_res_body'];
            for($i=1;$i<=7;$i++){
                $thek ='f'.$i;
                
                //判断白天黑夜
                $now =date('H',time());
               
                if($now<6 || $now >18){
                    
                    $result[$thek]['day_weather']= $result[$thek]['night_weather'];
                    $result[$thek]['day_weather_pic']= $result[$thek]['night_weather_pic'];
                    $result[$thek]['day_wind_power']= $result[$thek]['night_wind_power'];
                    $result[$thek]['day_weather_code']= $result[$thek]['night_weather_code'];
                    $result[$thek]['day_wind_direction']= $result[$thek]['night_wind_direction'];
                    $result[$thek]['day_air_temperature']= $result[$thek]['night_air_temperature'];
                   
                }
                
                $result[$thek]['day_weather_pic']=getWeatherIcon($result[$thek]['day_weather_code']);

            }
            //    print_r($result);exit;
               return json_encode($result); 
        }
}
    
/*获取天气图标
 * 
 */    
function getWeatherIcon($num){
    return "http://".$_SERVER['HTTP_HOST']."/weatherico/".$num.".png";
}

/*获取极光推送id
 * 
 */
function getJpushid(){
    $str="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ5321497860";
    $length =strlen($str);
    return $str[mt_rand(0, $length-1)].$str[mt_rand(0, $length-1)].$str[mt_rand(0, $length-1)].$str[mt_rand(0, $length-1)].$str[mt_rand(0, $length-1)].$str[mt_rand(0, $length-1)];
}

function jpush_send($member_id,$alias_arr,$title,$content,$code,$data=[]){
    include_once  EXTEND_PATH."jpush".DS."autoload.php";
    $app_key = '49444cb44e14fdc04fbdf438';
    $master_secret = '46886dbc9c00d32f8c336308';
    
    $thecontent['code']=$code;
    $thecontent['title']=$title;
    $thecontent['content']=$content;
    $thecontent['data']=$data;
    $thecontents =json_encode($thecontent);
    $iosnotify['extras'] =$thecontent;
    $iosnotify['content-available'] =true;
    
    $client = new JPush\Client($app_key, $master_secret);
    
try {
    $result =$client->push()
    //->setOptions(null, null, null, false)
    ->setPlatform('all')
    ->addAlias($alias_arr)
    ->setNotificationAlert($content)
    ->addAndroidNotification($content,$title,null,$thecontent)
    ->iosNotification($content,$iosnotify)
    ->send();
} catch (JPush\Exceptions\APIConnectionException $e) {
    return false;
    // try something here
   // print $e;
} catch (JPush\Exceptions\APIRequestException $e) {
    // try something here
    return false;
}
//
//    $result =$client->push()
//    ->setPlatform('all')
//    ->addAlias($alias_arr)
//    ->setNotificationAlert($thecontent)
//    ->send();
  //  print_r($result);
    if(isset($result['http_code']) && $result['http_code']=='200'){
        
        
        if(intval($member_id)>0){
            
            if($code==4 || $code==5 || $code==6){
                $code=2;
            }
            //插入
            $data['type']=$code;
            $data['title']=$title;
            $data['content']=$content;
            $data['member_id']=$member_id;
            $data['create_time']=time();
            db('Message')->insert($data);
        }
        return true;
    }else{
        return false;
    }
}


/*上传图片、文件 单图
 * @param int $type 1img 2file
 */
    function myUploads($file,$type){
        
        $ext=[
            1=>'jpg,jpeg,png,gif',
            2=>'xls,doc,txt',
        ];
        
        $info = $file->validate(['size'=>2*1024*1024,'ext'=>$ext[$type]])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
                $save_name =$info->getSaveName();
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path ='/uploads/'.$save_name;
                $back['code']=1;
                $back['data']=$file_path;
                return $back;
        }else{
                $back['code']=0;
                $back['msg']=$file->getError();
                return $back;
        }
    }
    
    function getQnyTokens(){
        cache('qny_upToken',NULL);
        $upToken=cache('qny_upToken');
        if(empty($upToken)){
            $config =config('QNY_CLOUD');
            include EXTEND_PATH."qny".DS."autoload.php";
            $qny = new \Qiniu\Auth($config['ak'], $config['sk']);
            $bucket = 'water';
            $upToken = $qny->uploadToken($bucket);
            cache('qny_upToken',$upToken);
        }
        return $upToken;
        
    }
    
    
    
/*添加/删除设备
 * 
 */
function add_del_pip($equip_code,$nums){
    $base_array=[];
    if(empty($nums)){
         return "000000";
    }
    for($i=0;$i<24;$i++){
        $base_array[]=0;
    };
    $num_array =explode(',',$nums);
    foreach($num_array as $k=>$v){
        $base_array[$v-1]=1;
    };
    
    $ope_num1=$ope_num2=$ope_num3="";
    foreach($base_array as $k=>$v){
        
        if($k<=7 ){
            $ope_num1=$v.$ope_num1;
        }elseif($k>7 && $k<=15){
            $ope_num2=$v.$ope_num2;
        }elseif($k>15 ){
            $ope_num3=$v.$ope_num3;
        }
    };
    $ope_num1 =get_str(base_convert($ope_num1,2,16));
    $ope_num2 =get_str(base_convert($ope_num2,2,16));
    $ope_num3 =get_str(base_convert($ope_num3,2,16));
    
    $datastr =$ope_num1.$ope_num2.$ope_num3;
    return $datastr;
}


/*批量开启/关闭设备
 * 
 */
function batch_open_close_pip($equip_code,$nums){
    $base_array=[];
    for($i=0;$i<24;$i++){
        $base_array[]=0;
    };
    
    if(empty($nums)){
         return "000000";
    }
    
    $num_array =explode(',',$nums);
    foreach($num_array as $k=>$v){
        $base_array[$v-1]=1;
    };
    
    $ope_num1=$ope_num2=$ope_num3="";
    foreach($base_array as $k=>$v){
        
        if($k<=7 ){
            $ope_num1=$v.$ope_num1;
        }elseif($k>7 && $k<=15){
            $ope_num2=$v.$ope_num2;
        }elseif($k>15 ){
            $ope_num3=$v.$ope_num3;
        }
    };
    $ope_num1 =get_str(base_convert($ope_num1,2,16));
    $ope_num2 =get_str(base_convert($ope_num2,2,16));
    $ope_num3 =get_str(base_convert($ope_num3,2,16));
    
    $datastr =$ope_num1.$ope_num2.$ope_num3;
    //echo $ope_num1;exit;
  //   $str = encode_message_new($equip_code,31,$datastr);
    return $datastr;
}


/* 布/撤 防
 * @param string $equip_code 设备码
 * @param string $type 1布2撤
 */
function set_del_warnning($warn_status,$type){
    
    switch($type){
        case 1:
            $ope_num1 =($warn_status>2)?"03":"01";
            break;
        case 2:
             $ope_num1 =($warn_status>2)?"02":"00";
            break;
    }
    
  //   $str = encode_message_new($equip_code,50,$ope_num1);
    return $ope_num1;
}

/*电流阔值设定
 * 
 */
function set_kz($elec_kz){
    $elec_kz =$elec_kz*10;
    if($elec_kz<10){
        return "000".$elec_kz;
    }else if($elec_kz<100){
        return "00".$elec_kz;
    }else if($elec_kz<1000){
        return "0".$elec_kz;
    }else{
        return $elec_kz;
    }
}

function set_jg_time($time){
    if($time<10){
        return "0".$time;
    }else{
        return $time;
    }
}


/*在英文前面添加0
 * 
 */
function get_str($str){
    if(strlen($str)==1){
        return "0".strtoupper($str);
    }else{
        return strtoupper($str);
    }
}

/*第三次文档
 * @param string $quip_code 设备号
 * @param string code 指令码
 * @param string $result 操作结果
 * @param string $time 时间设定
 * @param string $bcf 布撤防
 * @param string $elec_setting 电流设置
 * @param string equip_manage 设备管理
 * @param string equip_ope 设备操作 
 */
function encode_message_v3($thecode,$equip_code,$code,$result,$time="00",$bcf="00",$elec_setting="0000",$equip_manage="000000",$equip_ope="000000"){
    $redis = initRedis();
    $title ='FAFB';
    if($result!='00' && $result!='80'){
        $totalstr =$result.$time.$bcf.$elec_setting.$equip_manage.$equip_ope;
        $len =get_str(dechex(10+intval(strlen($totalstr)/2)));
        $str=$len.$equip_code.$code.$totalstr;
        
    }else{
        $len=get_str(dechex(10+intval(strlen($result)/2)));
        $str =$len.$equip_code.$code.$result;
    }
    $sign =generalCrc($str);
    $str= $title.$str.$sign;
   // echo $str;
    $str = strtolower($str);
//    echo $str;exit;
   // $str = UnsetFromHexString($str);
    
    
   // echo $str;exit;
    //这里存入队列
    $eq_name =$equip_code.'_equip_ope_queue';
    //这里保证队列里只有一条
    $redis->delete($eq_name);
    $redis->delete($thecode."_".$equip_code."_count");
    $redis->delete($equip_code."_precontent");
    //$redis->zDelete('equip_cr_zlist','equip_cr_'.$thecode.'_'.$equip_code);
    return $redis->lpush($eq_name,$str);
}


/*解析设备管理
 * 
 */
function decode_equip_manage($message){
    $first  = substr($message,0,2);
    $second = substr($message,2,2);
    $three  = substr($message,4,2);
    
    $first  = trans16to2($first,8);
    $second = trans16to2($second,8);
    $three  = trans16to2($three,8);
    
    $arr1 =decode2toarr($first);
    $arr2 =decode2toarr($second,2);
    $arr3 =decode2toarr($three,3);
    $arr = array_merge($arr1,$arr2);
    $arr = array_merge($arr,$arr3);
    return $arr;
}
/*生成CRC
 * 
 */
function generalCrc($str){
    $s = pack('H*', $str);
    $t = crc16($s);
    $u =sprintf('%02x%02x', floor($t/256), $t%256);
    return strtoupper($u);
}

/*CRC16算法
 * 
 */
function crc16($string) {
  $crc = 0xFFFF;
  for ($x = 0; $x < strlen ($string); $x++) {
    $crc = $crc ^ ord($string[$x]);
    for ($y = 0; $y < 8; $y++) {
      if (($crc & 0x0001) == 0x0001) {
        $crc = (($crc >> 1) ^ 0xA001);
      } else { $crc = $crc >> 1; }
    }
  }
  return $crc;
}


/*16转2，不足数量加0
 * 
 */
function trans16to2($hex,$count){
    $hex =base_convert($hex,16,2);
    $total_len =strlen($hex);
    if($count>$total_len){
        for($i=0;$i<$count-$total_len;$i++){
            $hex="0".$hex;
        }
    };
    return $hex;
}

/*解析二进制字符串
 * 
 */
function decode2toarr($str,$level=1){
    $arr =[];
    $thecount =$level*8;
    for($i=0;$i<8;$i++){
        if($str[$i]==1){
            $arr[]= $thecount-$i;
        }
    }
    return $arr;
}

/*解析硬件返回的报文 返回都是44个字符
 * 
 */
function decode_message($message){
    
    $return['msg_type']=  substr($message, 0,4);
    $return['last_length']=  substr($message, 4,2);
    $return['equip_code']= (substr($message, 6,12));
    $return['request_type']=  substr($message, 18,2);
    $return['thetime']=substr($message, 20,2);
    $return['bcf']=substr($message, 22,2);
    $return['elec']=substr($message, 24,4);
    $return['elec_setting']=substr($message, 28,4);
    $return['equip_manage']=substr($message, 32,6);
    $return['equip_ope']=substr($message, 38,6);
    $return['equip_status']=substr($message, 44,2);
    
    
    //从机管理
    $return['slave_management']=substr($message, 46,16);
    
    //从机状态
    $return['slave_status']=substr($message, 62,2);
    
    //保留字
    $return['retain']=substr($message, 64,14);
    
    
    //传感器数
    $return['collector_count']=substr($message, 78,2);
    
    $thecount =intval($return['collector_count']);
    
    if($thecount>0){
    //传感器数据
    $return['collector_data']=substr($message, 80,$thecount*24);
    }else{
        $return['collector_data']="";
    }
    $start =80+$thecount*24;
    $return['sign']=substr($message, $start,4);
   return $return;
}

/*解析传感数据
 * 
 */
function decode_collector_data($data_str,$count){
    if($count==0){
        return [];
    }
    $the_data=[];
    for($i=0;$i<$count;$i++){
        $s =$i*24;
        $e =24;
        $str =substr($data_str,$s,$e);
        
        //获取序号
        $the_num =substr($str,0,2);
        $the_num =intval($the_num);
        //获取ph值
        $the_ph =substr($str,2,4);
        $the_ph =intval($the_ph)/100;
        //获取水温
        $the_water =substr($str,6,4);
        $the_water =intval($the_water)/100;
        //获取溶解氧
        $the_rong =substr($str,10,4);
        $the_rong =intval($the_rong)/100;
        
        $the_data[$the_num]['num']=$the_num;
        $the_data[$the_num]['ph']=$the_ph;
        $the_data[$the_num]['temperature']=$the_water;
        $the_data[$the_num]['oxygen']=$the_rong;
    }
    return $the_data;
}


function SingleDecToHex($dec)
{
    $tmp="";
    $dec=$dec%16;
    if($dec<10)
        return $tmp.$dec;
    $arr=array("a","b","c","d","e","f");
    return $tmp.$arr[$dec-10];
}
function SingleHexToDec($hex)
{
    $v=ord($hex);
    if(47<$v&&$v<58)
        return $v-48;
    if(96<$v&&$v<103)
        return $v-87;
}


/*16进制转字符串
 * 
 */
function SetToHexString($str)
{
    if(!$str)return false;
    $tmp="";
    for($i=0;$i<strlen($str);$i++)
    {
        $ord=ord($str[$i]);
        $tmp.=SingleDecToHex(($ord-$ord%16)/16);
        $tmp.=SingleDecToHex($ord%16);
    }
    return $tmp;
}

/*字符串转16进制
 * 
 */
function UnsetFromHexString($str)
{
    if(!$str)return false;
    $tmp="";
    for($i=0;$i<strlen($str);$i+=2)
    {
        $tmp.=chr(SingleHexToDec(substr($str,$i,1))*16+SingleHexToDec(substr($str,$i+1,1)));
    }
    return $tmp;
}


/*获取时间
 * @param int $type 1一个月，2 三个月，3 一年 4全部
 */
function getTypeTime($type){
    switch($type){
        case 1:
            $back['first'] =date('Y-m')."-01";
           // $back['last'] =date('Y-m-t');
            $back['last'] =date('Y-m-d',time());
            return $back;
            break;
        case 2:
           
            $back['first'] =date("Y-m-d", strtotime("-2 month"));  
          //  $back['last'] =date('Y-m-t');
            $back['last'] =date('Y-m-d',time());
              return $back;
            break;
        case 3:
            $back['first'] =date("Y-m-d", strtotime("-1 year"));  
            $back['last'] =date('Y-m-d',time());
              return $back;
            break;
        case 4:
            return 1;
            break;
        default:
            return 0;
            break;
    }
}
/*检验是否是时间格式
 * 
 */
function checkTimeFormat($str,$format="Y-m-d H:i:s"){
    $str =(string)$str;
    $unixTime=strtotime($str);
    if($unixTime===false){
        return false;
    }
    $checkDate= date($format, $unixTime);
    if($checkDate!=$str){
        return false;
    }
    return true;
}
/*检验电话格式
 * 
 */
function check_phone($num){
    if(preg_match("/^1[34578]{1}\d{9}$/",$num)){  
        return true;  
    }else{  
         return false;  
    } 
} 



/*curl get请求
 * 
 */

function http_get($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

/*curl post请求
 * 
 */

function http_post($url,$data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ($data));
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


/*录入坐标
 * @param string $lng 经度
 * @param string $lat 纬度
 */
function setPoint($name,$lng,$lat){
    
    $url ="http://yuntuapi.amap.com/datamanage/data/create";
    $yun_config=config('GD_YUNTU');
    
    $data1=[
            '_name'=>$name,
            '_location'=>$lng.','.$lat,
    ];
    
    $post_data=[
        'key'=>$yun_config['key'],
        'tableid'=>$yun_config['tableid'],
        'loctype'=>1,
        'data'=>json_encode($data1),
    ];
    
    $result =http_post($url,$post_data);
    $result = json_decode($result,true);
    return $result;
  //  print_r($result);
    
}


/*录入坐标失败存储
 * 
 */
function setPointLog($data){
    $redis_key_name="gd_insert_fail";
    $redis =initRedis();
    $redis->lpush($redis_key_name,json_encode($data));
    return true;
}

/*删除坐标
 * 
 */
function delPoint($ids){
    $url ="http://yuntuapi.amap.com/datamanage/data/delete";
    $yun_config=config('GD_YUNTU');
    
    $post_data=[
        'key'=>$yun_config['key'],
        'tableid'=>$yun_config['tableid'],
        'ids'=>$ids,
     //   'data'=>json_encode($data1),
    ];
    
    $result =http_post($url,$post_data);
    $result = json_decode($result,true);
    return $result;
}


/*记录下发
 * 
 */
function send_down($agent_id,$data){
    
    $redis = initRedis();
    $redis_name ="agent_".$agent_id."_senddown";

    $results =$redis->sAdd($redis_name,json_encode($data));
    $redis->expire($redis_name,600);
//    if(!$redis->exists($redis_name)){
//     //   $redis->
//        $results =$redis->sAdd($redis_name,json_encode($data));
//        $redis->expire($redis_name,600);
//    }else{
//        $results =$redis->sAdd($redis_name,json_encode($data));
//    }
    return $results;
}


/*获取下发
 * 
 */
function get_send_down_list($agent_id){
    $redis = initRedis();
    $redis_name ="agent_".$agent_id."_senddown";
    if(!$redis->exists($redis_name)){
        return[];
    }
    $redis_list =$redis->sMembers($redis_name);

    $list=[];
    if(!empty($redis_list)){

        foreach($redis_list as $k=>$v){
            $arr =json_decode($v,true);
            $list[$k]=$arr;
        }
    }
    
    return $list;
    
}

function in_zero($str,$len=6){
    $thelen =strlen($str);
    if($thelen>=$len){
        return $str;
    }
    $dec_len =$len -$thelen;
    for($i=1;$i<=$dec_len;$i++){
        $str="0".$str;
    };
    return $str;
}


/*生成订单
 * @param string type_name 类型
 */
function create_sn($type_name=''){
    $redis = initRedis();
    $redis_name ="the_".date('Ymd')."_".$type_name."_id";
    $yesday =date('Ymd',strtotime("-1 day"));
    $yes_name ="the_".$yesday."_".$type_name."_id";
    if($redis->exists($yes_name)){
        $redis->delete($yes_name);
    }
    
    
    if(!$redis->exists($redis_name)){
        $redis->set($redis_name,1);
        return $type_name.date('Ymd').in_zero(1,4);
    }else{
        $incr_num =$redis->incr($redis_name);
        return $type_name.date('Ymd').in_zero($incr_num,4);
    }
    
}

/*log记录
 * 
 */
function save_log($path,$content){
    Log::init(['type'=>'file','path'=>APP_PATH."redis/"]);

    Log::write($content,'notice');
    return ;
}

/*计算两天的相差天数
 * 
 */
function ceil_days($date){
    $now =time();
    $now =strtotime('2019-01-04 00:00:00');
    $thedate =strtotime($date." 23:59:59");
    $dif=ceil(($thedate-$now)/86400); //60s*60min*24h   
    return $dif;
}

function get_slave_status($equip_code){
    
}
/*获取菜单
 * 
 */
function getTree($data, $pId,$select=[]){
    $tree = [];
    foreach($data as $k => $v)
    {
        $v['select']="";
        if(!empty($select) && in_array($v['id'], $select)){
            $v['select']="checked";
        }
       if($v['pid'] == $pId)
       {         //父亲找到儿子
        $v['childs'] = getTree($data, $v['id'],$select);
        $tree[] = $v;
        //unset($data[$k]);
       }
    }
    //print_r($tree);exit;
    return $tree;
}
/**
 * 功能：生成二维码
 * @param string $qrData 手机扫描后要跳转的网址
 * @param string $qrLevel 默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
 * @param string $qrSize 二维码图大小，1－10可选，数字越大图片尺寸越大
 * @param string $savePath 图片存储路径
 * @param string $savePrefix 图片名称前缀
 */
function createQRcode($savePath, $qrData = 'PHP QR Code :)', $qrLevel = 'L', $qrSize = 4, $savePrefix = 'qrcode')
{
    vendor("phpqrcode.phpqrcode");
    $QRcode = new \QRcode();
    if (!isset($savePath)) return '';
    //设置生成png图片的路径
    $PNG_TEMP_DIR = $savePath;

    //检测并创建生成文件夹
    if (!file_exists($PNG_TEMP_DIR)) {
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR . 'test.png';
    $errorCorrectionLevel = 'L';
    if (isset($qrLevel) && in_array($qrLevel, ['L', 'M', 'Q', 'H'])) {
        $errorCorrectionLevel = $qrLevel;
    }
    $matrixPointSize = 4;
    if (isset($qrSize)) {
        $matrixPointSize = min(max((int)$qrSize, 1), 10);
    }
    if (isset($qrData)) {
        if (trim($qrData) == '') {
            die('data cannot be empty!');
        }
        //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
        $filename = $PNG_TEMP_DIR . $savePrefix . md5($qrData . '|' . $errorCorrectionLevel . '|' . $matrixPointSize) . '.png';
        //开始生成
        $QRcode->png($qrData, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    } else {
        //默认生成
        $QRcode->png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
//    if (file_exists($PNG_TEMP_DIR . basename($filename)))
    return basename($filename);
//    else
//        return FALSE;
}
