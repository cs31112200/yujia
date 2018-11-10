<?php
namespace app\index\validate;
use think\Validate;
class MemberAera extends Validate
{
    protected $rule = [
        'num'  =>  'require|number',
        'equip_id'  =>  'require',
        'member_id' =>  'require',
        'remark' =>  'max:200',
        'name' =>  'require|max:20'
    ];
    protected  $msg=[
        'num.require' => '通道号必选',
        'name.require' => '名称必填',
        'num.number'     => '通道号必须是数字',
        'equip_id.require'=>'设备id必须填写',
        'member_id.require'=>'用户id必填',
        'remark.max'=>'备注最大字符数不超过200',
        'name.max'=>'名称最大字符数不超过20',
    ];

    protected $scene = [
        'edit'  =>  ['name','remark'],
    ];
}
