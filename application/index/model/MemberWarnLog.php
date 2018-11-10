<?php
namespace app\index\model;

use app\admin\model\Base;

class MemberWarnLog extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;

/*获取最近一条信息
 * 
 */
    public function getMemberWeather($mwarn_id){
        $data['a.mwarn_id']=$mwarn_id;
       $prex =config('database.prefix');
        $result= db($this->getTheTable())->alias('a')
               ->join($prex.'weather_log b','a.weather_id=b.id','LEFT')
                ->field('b.province,b.city,b.issueContent,b.issueTime,b.signalLevel,b.signalType,a.id')
                ->where($data)->order('a.id desc')->find();
        return empty($result)?null:$result;
        
    }
}