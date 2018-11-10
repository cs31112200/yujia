<?php
namespace app\index\controller;
use think\Db;
use think\Config;


class Api extends Base
{
/*获取日志列表
 * 
 */    
    public function getDailyList(){
        $this->checkPageAuth('to_suyuan');
        $product_id =session('activity_'.$this->activity_id.".product_id");
        if($product_id<=0)
            return $this->returnBack (-1, '参数非法');
        
        $result =model('merchant/SyDaily')->getDailyList($product_id);
        if(empty($result))
            return $this->returnJson (0, '暂无查询结果');

           return $this->returnJsonData (1, '成功', $result);
        
    }
    
    
/*首页接口
 * 
 */    
   public function  getIndexList(){
       $this->checkPageAuth('to_suyuan');
       $product_id =session('activity_'.$this->activity_id.".product_id");
       $mid =session('activity_'.$this->activity_id.".mid");
       $sn =session('activity_'.$this->activity_id.".sn");
       $isstatic =session('activity_'.$this->activity_id.".isstatic");
       if($product_id<=0)
           $this->returnBack (-1,'参数非法');
       $result =model('merchant/SySetting')->getSetting($product_id);
       
       if($isstatic==1){
           $result['outtime']['value']=0;
           $result['scan']['value']=0;
       }
       
       
       $return['mainlist']=$result;
       //下发广告
       $return['commonadlist']=model('AdvertSend')->getAdvertList($product_id,1,1);
       
       // 企业信息
       $return['merdetail']=model('merchant/merchant')->getMerchantDetail($mid);
       
       //日志列表
       $return['dailylist']=model('merchant/SyDaily')->getDailyList($product_id);
       
       //扫码次数
       $return['other']['scan_count']=($isstatic==0)?model('merchant/QrcodeScanLog')->getScanCount($sn):0;
    //   $return['other']['scan_count']=1;
       
      
       //剩余天数
       $return['other']['product_date']=($isstatic==0)?session('activity_'.$this->activity_id.".product_date"):'';
       $return['other']['effect_day']  =($isstatic==0)?session('activity_'.$this->activity_id.".effect_day"):0;
       
       
       $settinglist =model('merchant/Config')->getSetting();
       $oss_config =config('UPLOAD_OSS_CONFIG');  
       $return['other']['company_logo']=$oss_config['url'].$settinglist['PICTURE_LOGO_COMPANY']['value'];
       $return['other']['qa_logo']=$oss_config['url'].$settinglist['PICTURE_LOGO_QA']['value'];
       $return['other']['team_picture']=$oss_config['url'].$settinglist['PICTURE_TEAM']['value'];
       $return['other']['wechat_picture']=$oss_config['url'].$settinglist['PICTURE_WEIXIN']['value'];
       $return['other']['copy_right']=$settinglist['WEB_SITE_COPYRIGHT']['value'];
       $return['other']['icp']=$settinglist['WEB_SITE_ICP']['value'];
       $return['other']['towexin']=$settinglist['WEIXIN_URL']['value'];
       
       
       
       //友情链接
       $return['friendlink']=model('merchant/FriendlyLink')->getFriendLinkList(2);
       
       
       
       
    //   print_r($result);exit;
       if(empty($result))
           $this->returnBack (0, '暂无查询结果');
       else 
           $this->returnBack (1, '成功', $return);
   }
   
   
   public function getScanLogList(){
       $this->checkPageAuth('to_suyuan');
       
      
       $sn =session('activity_'.$this->activity_id.".sn");
    //   $sn ='104';
       $result =model('merchant/QrcodeScanLog')->getScanList($sn);
       if(empty($result))
           $this->returnBack (0, '暂无查询结果');
       else 
           $this->returnBack (1, '成功', $result);
   }
   
   public function getWeather(){
       $this->checkPageAuth('to_suyuan');
       $weather_config =config('ALI_WEATHER');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $weather_config['appcode']);
        
        
        $area =input('city'); $time =time(); $day =date('Ym',$time);
        $url =$weather_config['url'].$weather_config['api']."?area=".$area;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$weather_config['url'], "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        $weather =json_decode((curl_exec($curl)),true);
        print_r($weather);exit;
        $result =(isset($weather['showapi_res_body']['now']) && $weather['showapi_res_body']['ret_code']==0)?$weather['showapi_res_body']['now']:[];
        $return =[];
        if(empty($result)){
            $this->returnBack(0,'查询失败');
        }else{
            $this->returnBack(1,'请求成功',$result);
        }
        
   }
   
   public function test(){
    // 建立socket连接到内部推送端口
    $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
    // 推送的数据，包含uid字段，表示是给这个uid推送
    $data = array('uid'=>'uid1', 'percent'=>'88%');
    // 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
    fwrite($client, json_encode($data)."\n");
    // 读取推送结果
    echo fread($client, 8192);
   }

}
