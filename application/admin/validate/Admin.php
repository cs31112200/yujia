<?php
namespace app\admin\validate;
use think\Validate;
class Admin extends Validate
{
    protected $rule = [
        'username'=>'require|max:20',
        'account'=>'require|unique:admin',
    //   'password'=>'require',
    //    'ico'=>'require',
    ];
    protected $message = [
        'username.require'  =>  '用户名称必填',
        'username.max' =>  '用户名称不能超过20个字符',
        'account.require'  =>  '帐号必填',
    //    'password.require' =>  '密码必填',
      //  'ico.require'  =>  '图标必填',
        'account.unique'  =>  '已经存在该帐号',

    ];
    protected $scene = [
        'edit'  =>  ['password'],
    ];
    
}
