<?php
namespace app\index\validate;
use think\Validate;
class FeedType extends Validate
{
    protected $rule = [
        'name'  =>  'require|chsDash|max:20',
    ];
    protected  $msg=[
        'name.require' => '饲料类型必填',
        'name.chs' => '饲料类型只能是汉字、字母、数字和下划线_及破折号-',
        'name.max' => '饲料类型最多只能20个字符',
    ];
    

    
}
