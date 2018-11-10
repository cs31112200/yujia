<?php
namespace app\admin\validate;
use think\Validate;
class Question extends Validate
{
    protected $rule = [
        'title'=>'require',
        'content'=>'require',
//       'address'=>'require',
//    //    'ico'=>'require',
    ];
    protected $message = [
        'title.require'  =>  '请输入标题',
//        'username.max' =>  '用户名称不能超过20个字符',
        'content.require'  =>  '请输入回答内容',
//        'address.require' =>  '请输入详细地址',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
