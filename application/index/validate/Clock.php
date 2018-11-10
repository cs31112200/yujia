<?php
namespace app\index\validate;
use think\Validate;
class Clock extends Validate
{
    protected $rule = [
        'area_id'  =>  'require|number',
        'is_open' =>  'require|number',
        'open_time' =>  'require|date'
    ];
    protected  $msg=[
        'area_id.require' => '您没有关联增氧机id',
        'area_id.number'     => '增氧机id格式有误',
        'is_open.require'     => '请选择操作，打开还是关闭',
        'is_open.number'   => '操作格式有误',
        'open_time.require'     => '请选择操作规定的时间',
        'open_time.date' =>'操作时间格式不正确'
    ];
    

    
}
