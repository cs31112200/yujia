<?php
namespace app\index\validate;
use think\Validate;
class EquipProject extends Validate
{
    protected $rule = [
        'name'  =>  'require|chsDash|max:20',
        'remark'  =>  'chsDash|max:255',
    ];
    protected  $msg=[
        'name.require' => '请填写名称',
        'name.chsDash'     => '名称格式有误',
        'name.max'     => '名称最大只能20个字',
        'remark.chsDash'     => '备注格式有误',
        'remark.max'     => '名称最大只能255个字'
    ];
    

    
}
