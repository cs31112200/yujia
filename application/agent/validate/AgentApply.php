<?php
namespace app\agent\validate;
use think\Validate;
class AgentApply extends Validate
{
    protected $rule = [
        'name'  =>  'require|chs|max:10',
        'province'  =>  'require|gt:0',
        'city'  =>  'require|gt:0',
        'area'  =>  'require|gt:0',
        'address'  =>  'require|max:255',
    ];
    protected  $message=[
        'name.require' => '姓名必填',
        'name.chs' => '姓名必须是汉字',
        'name.max' => '姓名最多只能10个字符',
        
        'province.require' => '省份必填',
        'city.require' => '市必填',
        'area.require' => '区必填',
        
        'province.gt' => '省份必填',
        'city.gt' => '市必填',
        'area.gt' => '区必填',
        
        'address.require' => '详细地址必填',
        'address.max' => '详细地址最大字符255',
    ];
    

    
}
