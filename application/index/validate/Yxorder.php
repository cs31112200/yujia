<?php
namespace app\index\validate;
use think\Validate;
class Yxorder extends Validate
{
    protected $rule = [
        'name'  =>  'require|chs|max:10',
      //  'province'  =>  'require|chs',
     //   'city'  =>  'require|chs',
    //    'area'  =>  'require|chs',
        'province'  =>  'chs',
        'city'  =>  'chs',
        'area'  =>  'chs',
        'address'  =>  'require|max:255',
    ];
    protected  $msg=[
        'name.require' => '姓名必填',
        'name.chs' => '姓名必须是汉字',
        'name.max' => '姓名最多只能10个字符',
        
        'province.require' => '省份必填',
        'province.chs' => '省份必须是汉字',
        
        'city.require' => '市必填',
        'city.chs' => '市必须是汉字',
        
        'area.require' => '区必填',
        'area.chs' => '区必须是汉字',
        
        'address.require' => '详细地址必填',
        'address.max' => '详细地址最大字符255',
    ];
    

    
}
