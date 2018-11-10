<?php
namespace app\admin\validate;
use think\Validate;
class CollectorType extends Validate
{
    protected $rule = [
        'name'=>'require',
        'price'=>'require',
//       'href'=>'require',
//    //    'ico'=>'require',
    ];
    protected $message = [
        'name.require'  =>  '请输入采集器名称',
//        'username.max' =>  '用户名称不能超过20个字符',
        'price.require'  =>  '请输入采集器价格',
//        'href.require' =>  '请输入下载地址',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
