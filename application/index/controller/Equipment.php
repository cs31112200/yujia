<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;


class Equipment extends Base
{

    public function get_equipcode_list(){
        $card_num=input('card_num');
        $data['card_num']=$card_num;
        $data['status']=1;
        $result =db('Equipment')->where($data)->field('equip_code,qrcode_pic')->find();
        if(empty($result)){
            $this->returnJson(0, '暂无更多数据');
        }
        
        $result['qrcode_pic']= generalImg($result['qrcode_pic']);
        $this->returnJsonData(1, '获取成功', $result);
    }
}
