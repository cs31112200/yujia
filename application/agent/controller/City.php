<?php
namespace app\agent\controller;
use think\Db;
use think\Config;
use think\Request;
use think\Controller;

class City extends Controller
{
    
    
    
/*获取市
 * 
 */
    public function getCityList(){
        $pro =input('province_name');
        if(empty($pro)){
            $this->error('');
        }

        $cityresult =model('admin/Area')->GetCityLists($pro);
        $this->success('请求成功', '', $cityresult);
    }
 
/*获取区
 * 
 */
    public function getAreaList(){
        $pro =input('city_name');

        $cityresult =model('admin/Area')->GetAreaLists($pro);
      //  print_r($cityresult);exit;
        if(empty($cityresult))
            $this->error('暂无数据');
         $this->success('请求成功', '', $cityresult);
    }  
    
}
