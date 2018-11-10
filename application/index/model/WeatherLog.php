<?php
namespace app\Index\model;

use app\admin\model\Base;

class WeatherLog extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;



    
 /*新增预警
  * 
  */   
    
    public function addWeatherLog($province,$city,$issueTime,$signalLevel,$signalType,$issueContent){
            
        $result =$this->getBeforeData($province, $city, 10);
        
        $is_insert=1;
        if(!empty($result)){
            foreach($result as $k=>$v){
                if($v['issueTime']==$issueTime && $v['signalLevel']==$signalLevel && $v['signalType']==$signalType){
                    $is_insert=0;
                }
            }
        }
        
        if($is_insert==1){
            $data=[
                'province'=>$province,
                'city'=>$city,
                'issueTime'=>$issueTime,
                'signalLevel'=>$signalLevel,
                'signalType'=>$signalType,
                'issueContent'=>$issueContent,
                'create_time'=>time()
            ];
            $this->insert($data);
        }
        
        return $is_insert;
    }
    
/*取早10天的数据
 * 
 */  
    public function getBeforeData($province,$city,$num){
        $time =time();
        $data['province']=$province;
        $data['city']=$city;
        $data['issueTime']=['gt',date('Y-m-d H:i:s',($time-$num*86400))];
        
        $result =db($this->getTheTable())->where($data)->select();
        return $result;
        
    }
    

    
    
}