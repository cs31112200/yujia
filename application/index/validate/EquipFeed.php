<?php
namespace app\index\validate;
use think\Validate;
class EquipFeed extends Validate
{
    protected $rule = [
        'feed_name'  =>  'require|chsDash',
        'remark'  =>  'chsDash',
    ];
    protected  $msg=[
        'feed_name.require' => '饲料名称必填',
        'feed_name.chsDash'     => '饲料名称只能是汉字、字母、数字和下划线_及破折号-',
        'remark.chsDash'     => '备注只能是汉字、字母、数字和下划线_及破折号-',
    ];
    

    
}
