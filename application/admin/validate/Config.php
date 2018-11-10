<?php
namespace app\admin\validate;
use think\Validate;
class Config extends Validate
{

    protected $rule = [
        'key'=>'require|unique:config',
        'key_name'=>'require|max:60',
        'the_value'=>'require',
    //    'ico'=>'require',
    ];
    protected $message = [
        'key.require'  =>  'KEY值必填',
        'key_name.require'  =>  '配置名称必填',
        'the_value.require'  =>  'value值必须填写',
      //  'ico.require'  =>  '图标必填',
        'key.unique'  =>  'KEY值已经存在',
        'key_name.max'  =>  '配置名称不能超过20个字符',

    ];
}
