<?php
namespace app\admin\validate;
use think\Validate;
class Feedback extends Validate
{
    protected $rule = [
        'visit_telephone'=>'require',
        'visit_name'=>'require',
        'content'=>'require',

    ];
    protected $message = [
        'visit_telephone.require'  =>  '请输入来访电话',
        'visit_name.require'  =>  '请输入来访名称',
        'content.require'  =>  '请输入来访内容',
    ];

    
}
