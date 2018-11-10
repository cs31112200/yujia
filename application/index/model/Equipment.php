<?php
namespace app\index\model;

use app\admin\model\Base;

class Equipment extends Base{
    
//自动填充时间    
protected $autoWriteTimestamp = false;


/*根据二维码查找
 * @param string $qrcode 二维码号
 * @param string $type_num 类型编号
 */  
    public function findEquipByQr($qrcode){
        
        $qrnum =getNumInString($qrcode);
        //echo $qrnum;exit;
        if(empty($qrnum) || intval($qrnum)<=0){
            $this->setError('该二维码无效，找不到设备');
            return false;
        };
        $result =$this->getDetailByCode($qrnum);
        if(empty($result)){
            $this->setError('该二维码无效，找不到设备');
            return false;
        }
        return $result;    
        
    }
    
/*根据code查找
 * 
 */    
    public function getDetailByCode($equip_code){
        $data['a.equip_code']=$equip_code;
        $prex =config('database.prefix');
        $result =db($this->getTheTable())->alias('a')
                ->join($prex.'product_price b','a.product_id=b.id','LEFT')
                ->field('a.id,a.qrcode,a.type,a.equip_code,a.electric,a.status,b.init_count,b.annual_fee,a.end_time')
                ->where($data)->find();
        return $result;
    }
   
/*修改状态
 * 
 */
    public function changeEquipStatus($equip_id,$status){
        $save_data =[
            'status'=>$status,
            'update_time'=>time()
        ];
        $this->where('id ='.$equip_id)->update($save_data);
        return true;
    }
    
}