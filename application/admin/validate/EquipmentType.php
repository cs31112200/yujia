<?php
namespace app\admin\validate;
use think\Validate;
class EquipmentType extends Validate
{
    protected $rule = [
        'type_name'=>'require',
        'bref'=>'require',
       'type_img'=>'require',
//        'ico'=>'require',
    ];
    protected $message = [
        'type_name.require'  =>  '请输入类型名称',
//        'username.max' =>  '用户名称不能超过20个字符',
        'bref.require'  =>  '请输入类型首字母简称',
        'type_img.require' =>  '请上传类型图片',
//      //  'ico.require'  =>  '图标必填',
//        'account.unique'  =>  '已经存在该帐号',
//
    ];
//    protected $scene = [
//        'edit'  =>  ['password'],
//    ];
    
}
