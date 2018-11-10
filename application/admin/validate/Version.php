<?php
namespace app\admin\validate;
use think\Validate;
class Version extends Validate
{
    protected $rule = [
        'bversion'=>'require',
        'mversion'=>'require',
        'sversion'=>'require',
        'content'=>'require',
        'uoload_url'=>'require',
//    //    'ico'=>'require',
    ];
    protected $message = [
        'bversion.require'  =>  '请输入大版本号',
        'mversion.require'  =>  '请输入中版本号',
        'sversion.require'  =>  '请输入小版本号',
//        'username.max' =>  '用户名称不能超过20个字符',
        'content.require'  =>  '请输入更新内容',
        'uoload_url.require' =>  '请输入下载地址',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];

}
