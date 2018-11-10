<?php
namespace app\admin\validate;
use think\Validate;
class Menu extends Validate
{
    protected $rule = [
        'name'=>'require|max:20',
        'href'=>'require|max:255',
        'id_name'=>'require|unique:menu',
    //    'ico'=>'require',
    ];
    protected $message = [
        'name.require'  =>  '菜单名称必填',
        'name.max' =>  '菜单名称不能超过20个字符',
        'href.require'  =>  '跳转地址必填',
        'href.max' =>  '跳转地址不能超过255个字符',
        'id_name.require'  =>  'id名称必填',
      //  'ico.require'  =>  '图标必填',
        'id_name.unique'  =>  'id名称唯一',

    ];
}
