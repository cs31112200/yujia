<?php
namespace app\admin\validate;
use think\Validate;
class Group extends Validate
{
    protected $rule = [
        'name'=>'require|max:20',
        'description'=>'max:255',
    //    'ico'=>'require',
    ];
    protected $message = [
        'name.require'  =>  '分组名称必填',
        'name.max' =>  '分组名称不能超过20个字符',
        'description.max'  =>  '描述最大不超过255个字符',
    ];
}
