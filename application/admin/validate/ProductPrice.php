<?php
namespace app\admin\validate;
use think\Validate;
class ProductPrice extends Validate
{
    protected $rule = [
        'name'=>'require',
        'fee'=>'require',
       'annual_fee'=>'require',
        'cut_fee_first'=>'require',
        'cut_fee_normal'=>'require',
    ];
    protected $message = [
        'name.require'  =>  '请输入产品名称',
        'fee.require'  =>  '请输入产品售价',
        'annual_fee.require' =>  '请输入年费',
        'cut_fee_first.require'  =>  '请输入第一年抽成',
        'cut_fee_normal.require'  =>  '请输入往后每年抽成',

    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
