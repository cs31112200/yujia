<?php
namespace app\admin\validate;
use think\Validate;
class City extends Validate
{
    protected $rule = [
        'city_name'=>'require',
//        'contact'=>'require',
//       'address'=>'require',
//    //    'ico'=>'require',
    ];
    protected $message = [
        'city_name.require'  =>  '请输入城市名称',
//        'username.max' =>  '用户名称不能超过20个字符',
//        'contact.require'  =>  '请输入联系方式',
//        'address.require' =>  '请输入详细地址',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
