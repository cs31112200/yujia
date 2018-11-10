<?php
namespace app\index\validate;
use think\Validate;
class MemberEquip extends Validate
{
    protected $rule = [
        'water_name'  =>  'require|max:60',
        'equip_id'  =>  'require',
        'warm_phone' =>  'require',
     //   'spare_phone' =>  'require'
    ];
    protected  $msg=[
        'water_name.require' => '水池名称必须填写',
        'water_name.max'     => '水池名称最多不能超过20个字符',
        'equip_id.require'=>'设备id必须填写',
        'warm_phone.require'=>'首选报警号码必填',
    //    'spare_phone.require'=>'备选报警号码必填'
    ];

    
}
