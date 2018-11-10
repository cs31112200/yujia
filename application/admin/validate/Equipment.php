<?php
namespace app\admin\validate;
use think\Validate;
class Equipment extends Validate
{
    protected $rule = [
        'batch_id'=>'require',
//        'type'=>'require',
       'equip_code'=>'require',
//        'product_id'=>'require',
    ];
    protected $message = [
        'batch_id.require'  =>  '请输入批次号',
//        'username.max' =>  '用户名称不能超过20个字符',
//        'type.require'  =>  '请选择设备类型',
        'equip_code.require' =>  '请输入设备码',
//        'product_id.require'  =>  '请选择关联产品',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
