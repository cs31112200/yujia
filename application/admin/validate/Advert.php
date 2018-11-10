<?php
namespace app\admin\validate;
use think\Validate;
class Advert extends Validate
{
    protected $rule = [
        'name'=>'require',
        'tourl'=>'require',
       'img'=>'require',
//    //    'ico'=>'require',
    ];
    protected $message = [
        'name.require'  =>  '请输入广告名称',
//        'username.max' =>  '用户名称不能超过20个字符',
        'tourl.require'  =>  '请输入跳转地址',
        'img.require' =>  '请上传图片',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
