<?php
namespace app\index\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule = [
        'name'  =>  'chsAlphaNum|max:20',
        'telephone' =>  'require|number|max:11|unique:member',
        'account' =>  'require|unique:member',
        'passwd' =>  'require',
        'province'=>'chs',
        'city'=>'chs',
        'area'=>'chs',
        'sex'=>'chs',
    ];
    protected  $msg=[
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过20个字符',
        'name.chsAlphaNum'     => '名称只能是汉字，字母，数字',
        'telephone.require'   => '电话必须',
        'telephone.number'  => '电话只能是数字',
        'telephone.max'     => '电话最多不能超过11个字符',
        'telephone.unique'     => '该电话号码已经注册过',
        'account.require'   => '帐号必须',
        'account.unique'   => '该帐号已经注册过',
        'passwd.require' =>'密码必填',
    ];

    protected $scene=[
        'edit'=>['name','telephone','account','passwd'],
        'changemsg'=>['province','city','area','sex','name'],
        
    ];
    
}
