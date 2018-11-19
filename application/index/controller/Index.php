<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;


class Index extends Controller
{
    
    public function index(){
        echo generalQrcode(111,false);
    }
    
    public function batch_set_equip_msg(){
        
    }
    
    
    public function tuisong_test(){
        for($i=1;$i<=20;$i++){
            $time =date('Y-m-d H:i:s',time());
            jpush_send(0, ['141fe1da9e8e5896ab9'],"title:".$i ,'number:    thetime:'.$time, 2);
            sleep(10);
        }
    }
    
/*开通、删除通道
 * @param string ope 操作码
 * @param string sig 签名
 * 
 * 
 */    
    public function reflash_operation(){
        $app_key =config('APP_KEY');
        $sig=input('sig','','trim');
        $code_str =input('ope','','trim');
        
        if(empty($sig) || empty($code_str)){
            return ;
        }
        
        //验证校验码
        $sig_check = md5($code_str.$app_key);
        if($sig_check!=$sig){
      //      return ;
        }
        //判断长度
        if(strlen($code_str)!=46){
            return false;
        };

        //equip_code
        $equip_code =substr($code_str,6,12);
        //操作码
        $ope_code =substr($code_str,20,2);
        
        
        //判断设备号
        $data['equip_code']=$equip_code;
        $equip_result=db('Equipment')->where($data)->find();
        if(empty($equip_result)){
            return false;
        }
        
        
//        //间隔
//      //  $intervals =substr($code_str,22,2);
//        //布撤防
//        $bcf =substr($code_str,24,2);
//        //电流阔值
//        $elect_range=substr($code_str,26,4);
//        //设备管理
//        $device_management=substr($code_str,30,6);
//        //设备操作
//        $device_operator=substr($code_str,36,6);
//
//        //解开操作码
        $ope_code_binary =trans16to2($ope_code,8);

        $ready_arr=[];
        for($i=0;$i<8;$i++){
            if($ope_code_binary[$i]==1 && $i!=7){
            $ready_arr[]=$i;
            }
        }
        if(empty($ready_arr)){
            return false;
        }
        foreach($ready_arr as $k=>$v){

            switch($v){
                //同步间隔
                case 1:
                    $intervals =substr($code_str,22,2);
                    $data11['equip_id']=$equip_result['id'];
                    $data12['jg_time']=intval($intervals);
                    db('MemberEquip')->where($data11)->update($data12);
                    break;

                //布撤防
                case 2:
                    $bcf =substr($code_str,24,2);
                    $bcf =intval($bcf);
                    if($bcf==0){
                        $data22['warn_status']=2;
                    }else if($bcf==1){
                        $data22['warn_status']=1;
                    }else if($bcf==2){
                        $data22['warn_status']=4;
                    }else{
                        $data22['warn_status']=3;
                    }
                     $data21['equip_id']=$equip_result['id'];
                     db('MemberEquip')->where($data21)->update($data22);
                    break;
                    
                //电流阔值    
                case 3:
                    $elect_range=substr($code_str,26,4);
                    $elect_range =intval($elect_range)/10;
                    $data31['equip_id']=$equip_result['id'];
                    $data32['elec_kz']=$elect_range;
                    db('MemberEquip')->where($data31)->update($data32);
                    break;
                //设备管理
                case 5:
                    $search['equip_id']=$equip_result['id'];
                    $member_equip_result =db('MemberEquip')->where($search)->find();
                    if(empty($member_equip_result)){
                        return;
                    }
                    $member_id =$member_equip_result['member_id'];
                    $equip_id =$search['equip_id'];
                    
                    $device_management=substr($code_str,30,6);
                    $target_nums =decode_equip_manage($device_management);

                    $model_memberaera =model('MemberAera');
                    $save_nums =$model_memberaera->getPipNormal($member_id,$equip_id);

                   // print_r($target_nums);
                  //  print_r($save_nums);exit;

                    //获取要新增得
                    $insert_array =array_diff($target_nums,$save_nums);
                    $delete_array =array_diff($save_nums,$target_nums);

                    //同步设备管理
                    $result =model('MemberAera')->openPip($member_id,$equip_id,$insert_array);
                    $result =model('MemberAera')->deletePip($member_id,$equip_id,$delete_array);
                    break;
                //设备操作    
                case 6:
                    $search['equip_id']=$equip_result['id'];
                    $member_equip_result =db('MemberEquip')->where($search)->find();
                    if(empty($member_equip_result)){
                        return;
                    }
                    $member_id =$member_equip_result['member_id'];
                    $equip_id =$search['equip_id'];
                    
                    $device_operator=substr($code_str,36,6);
                    $target_open_nums =decode_equip_manage($device_operator);

               //     print_r( $target_open_nums);echo "   ";

                    $datat['member_id']=$member_id;
                    $datat['equip_id']=$equip_id;
                    //同步设备操作
                    if(!empty($target_open_nums)){

                        $datat['num']=['in',$target_open_nums];
                        $data1['status']=1;
                        model('MemberAera')->where($datat)->update($data1);
                        $datat['num']=['not in',$target_open_nums];
                        $data1['status']=2;
                        model('MemberAera')->where($datat)->update($data1);
                    }else{
                        $data1['status']=2;
                        model('MemberAera')->where($datat)->update($data1);
                    }
                    
                    
                    break;
                default:
                    break;
            }
        }
        echo "success";
    }
    
    
    public function jpush_test(){
        $result =jpush_send(7, ['141fe1da9e8e5896ab9'], '666teet111', 'helloword', 2);
        print_r($result);
    }
}
