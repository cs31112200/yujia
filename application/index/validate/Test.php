<?php
namespace app\index\validate;
use think\Validate;
class Test extends Validate
{
    protected $rule = [
        'name'  =>  'require|chsDash|min:4|max:20',
        'tel' =>  'require|number|max:11',
        'start_time' =>  'require|date',
        'end_time' =>  'require|date',
    ];
    protected  $msg=[
        'name.require' => '名称必须',
        'name.max'     => '名称最多不能超过20个字符',
        'name.min'     => '名称最多不能小于4个字符',
        'tel.require'   => '电话必须',
        'tel.number'  => '只能是数字',
        'tel.max'     => '电话最多不能超过11个字符',
        'start_time.require' =>'开始时间必须',
        'end_time.require' =>'结束时间必须',
        'end_time.date' =>'结束时间格式必须为日期格式',
        'start_time.date' =>'开始时间格式必须为日期格式',
    ];
    
    protected  $scene = [
        'edit'  =>  ['start_time','end_time'],
    ];
    
}
