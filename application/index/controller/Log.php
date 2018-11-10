<?php
namespace app\index\controller;
use think\Controller;
use think\Config;
use think\Db;


class Log extends ApiBase
{

    
/*日志首页轮播图
 * 
 */ 
    public function getLb(){
         $return=model('admin/Advert')->getAdvertBySpace(2);
         $this->returnJsonData(1,'请求成功',$return);
    }
    
/*创建/修改 项目
 * 
 */    
    
    public function opeProject(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id,name,remark,start_time,end_time',$this->input);
        extract($this->input);
        $data=$this->input;;
        $model_memberequip=model('MemberEquip');
        $equip_result =$model_memberequip->getDetail($member_id,$equip_id);
        if(empty($equip_result)){
            $this->returnJson(0,'找不到该设备');
        }
        
        $data['member_id']=$member_id;
        $back =model('EquipProject')->__msave($data,'EquipProject');
        $this->returnJson($back['code'],$back['msg']);
    }
    
/*删除项目
 * 
 * 
 */
    public function delProject(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id',$this->input);
        extract($this->input);
        
        //检验该id
        
        $result =db('EquipProject')->find($id);
        if(empty($result)){
            $this->returnJson(0,'id有误');
        }
        
        if($result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }
        
        
        //删除关联的
        Db::startTrans();
        $result =db('EquipProject')->where('id='.$id)->delete();
        $result1 =db('EquipFeed')->where('project_id='.$id)->delete();
        
        if($result ){
             Db::commit();
             $this->returnJson(1,'删除成功');
        }else{
            Db::rollback();
            $this->returnJson(0, '删除失败');
        }
        
    }
    
/*项目列表
 * 
 * 
 */    
    public function getProjectList(){
        $member_id =$this->verifyUser();
        $this->__checkParam('equip_id',$this->input);
        extract($this->input);
        $page= intval($page);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }
        $model_memberequip=model('MemberEquip');
        $equip_result =$model_memberequip->getDetail($member_id,$equip_id);
        if(empty($equip_result)){
            $this->returnJson(0,'找不到该设备');
        }
        
        $page_size=10;
        $data['member_id']=$member_id;
        $data['equip_id']=$equip_id;
        $result =db('EquipProject')->where($data)->limit(($page-1)*$page_size.",".$page_size)->order('id desc')->select();
        if(empty($result)){
            $this->returnJson(0,'已无更多内容');
        }
        
        foreach($result as $k=>$v){
            $result[$k]['start_time']=date('Y-m-d',$v['start_time']);
            $result[$k]['end_time']=!empty($v['end_time'])?date('Y-m-d',$v['end_time']):"";
        }
        $this->returnJsonData(1,'请求成功',$result);

    }
    
    
    
/*录入投料
 * 
 */    
    public function writeFeedLog(){
        $member_id =$this->verifyUser();
        $this->__checkParam('project_id,feed_name,feed_count,feed_time',$this->input);
        extract($this->input);
        $data =$this->input;
        $data['member_id']=$member_id;
        $model_memberequip=model('MemberEquip');
        $now =time();
        $now_date =date('Y-m-d',$now);
        $feed_time =strtotime($feed_time);
        $feed_date =date('Y-m-d',$feed_time);
        
        
        //检验是否超过当前日期
        if($feed_date>$now_date){
            $this->returnJson(0,'您无法录入，投料日期已超过当前的时间');
        }
        
        $data['feed_count']= sprintf("%.2f",$feed_count);
        if($data['feed_count']<=0){
             $this->returnJson(0,'投料数量必须大于0');
        }
        
        
        $data['feed_time']=$feed_time;
        $data['feed_date']=$feed_date;
        
//        $result =$model_memberequip->getDetail($member_id,$equip_id);
//        if(empty($result)){
//            $this->returnJson(0,'找不到该设备');
//        }
        
        
        //检验project_id
        
        $pro_result =db('EquipProject')->find($project_id);
        if(empty($pro_result)){
            $this->returnJson(0,'非法的周期id');
        }
        if( $pro_result['member_id']!=$member_id){
            $this->returnJson(0,'非法操作');
        }
        
        if($feed_time<$pro_result['start_time'] || (!empty($pro_result['end_time']) && $pro_result['end_time']<$feed_time)){
            $this->returnJson(0,'您的投料时间必须在周期时间范围内，周期时间：'.date('Y-m-d',$pro_result['start_time'])."至".date('Y-m-d',$pro_result['end_time']));
        }
        
        
        
        
        if(isset($data['id']) && !empty($data['id'])){
            
            //检验该id
            $check_result =db('EquipFeed')->find($data['id']);
         //   print_r($check_result);
            if($check_result['member_id']!=$member_id ){
                $this->returnJson(0, '非法访问');
            }
            
            //检验时间  如果想要修改之前的数据也不行吗
         //   $check_time =date('Y-m-d',$check_result['create_time']);
            $check_time =$check_result['feed_date'];
            
            if($check_time != $now_date){
                $this->returnJson(0,'已超时，无法进行修改,只能修改当日的投料记录');
            }
            
        }
        
        $back =model('EquipFeed')->__msave($data);
        $this->returnJson($back['code'],$back['msg']);
     //   $back['url']=($back['code']==0)?'':$this->getCookie();
   //     $this->returnBack($back['code'],$back['msg'],'',$back['url']); 
        
        
        
        
    }
    
    
/*投料列表
 * 
 */    
    public function getFeedList(){
        $member_id =$this->verifyUser();
        $this->__checkParam('page,project_id',$this->input);
        extract($this->input);
        $page= intval($page);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }

//        if(empty($equip_result)){
//            $this->returnJson(0,'找不到该设备');
//        }
        
        //检验project_id
        
        $pro_result =db('EquipProject')->find($project_id);
        if(empty($pro_result)){
            $this->returnJson(0,'非法的周期id');
        }
        if( $pro_result['member_id']!=$member_id){
            $this->returnJson(0,'非法的周期id');
        }
        $model_memberequip=model('MemberEquip');
        $equip_result =$model_memberequip->getDetail($member_id,$pro_result['equip_id']);
        $page_size=10;
        $prex =config('database.prefix');
        $data['a.member_id']=$member_id;
        //$data['a.equip_id']=$equip_id;
        $data['a.project_id']=$project_id;
      //  $result =db('EquipFeed')->where($data)->limit(($page-1)*$page_size.",".$page_size)->select();
        $result =db('EquipFeed')->alias('a')
                ->join($prex.'equip_project b','a.project_id=b.id','left')
                ->field('a.member_id,a.equip_id,b.name as project_name,a.feed_name,a.feed_time,a.feed_count,a.id,a.remark')->where($data)->order('a.id desc')->limit(($page-1)*$page_size.",".$page_size)->select();
        
        
        if(empty($result)){
            $this->returnJson(0,'已无更多消息');
        }else{
            foreach($result as $k=>$v){
                $result[$k]['water_name']=$equip_result['water_name'];
                $result[$k]['feed_time']=date('Y-m-d H:i:s',$v['feed_time']);
            }
            $this->returnJsonData(1,'请求成功',$result);
        }
            
    }
    
/*投料图表
 * 
 */    
    public function getGridFeed(){
        $member_id =$this->verifyUser();
        $this->__checkParam('project_id',$this->input);
        extract($this->input);
        
        //检验project_id
        
        $pro_result =db('EquipProject')->find($project_id);
        if(empty($pro_result)){
            $this->returnJson(0,'非法的周期id');
        }
        if( $pro_result['member_id']!=$member_id){
            $this->returnJson(0,'非法访问');
        }
        
        
        $data['member_id']=$member_id;
        $data['project_id']=$project_id;
        
       $result =db('EquipFeed')->where($data)->field('feed_name,feed_date,sum(feed_count) as count')->group('feed_name,feed_date')->order('feed_date asc')->select();
   //    print_r($result);exit;
       if(empty($result)){
            $this->returnJson(0,'无数据');
        }

        $return=$thx=$thx_arr =[];$i=$t=0;
        
        foreach($result as $k=>$v){
            $the_feed_date =date('m-d',strtotime($v['feed_date']));
            if(!in_array($the_feed_date,$thx)) {
                $thx[]=$the_feed_date;
                $thx_arr[$t]['x']=$the_feed_date;
                $thx_arr[$t]['y']=0;
                $t++;
            }
        }
        
        foreach($result as $k=>$v){
            $the_feed_date =date('m-d',strtotime($v['feed_date']));
            $new_arr =[
                'x'=>$the_feed_date,
                'y'=>0,
            ];     
            
            if(!isset($return[$v['feed_name']])){
                $newthx =$thx_arr;
                $search_id = array_search($new_arr, $thx_arr);
                
                $newthx[$search_id]['y']=sprintf("%.2f",$v['count']);
                $return[$v['feed_name']]= $newthx;
            }else{
                $newthx =$return[$v['feed_name']];
                $search_id = array_search($new_arr, $newthx);
                
                $newthx[$search_id]['y']=sprintf("%.2f",$v['count']);
                
                $return[$v['feed_name']]= $newthx;
            }
        }

        $thedata=[];
        foreach($return as $k=>$value){
            $thedata[$i]['feed_name']=$k;
            $thedata[$i]['values']=$value;
            $i++;
        }
        $thedata1['grid_data']=$thedata;
        $thedata1['grid_x']=$thx;
        
        $this->returnJsonData(1,'请求成功',$thedata1);

    }
    
    
/*常见问题列表
 * 
 */    
    public function getQuestionList(){
        $data['status']=1;
        $result =db('Question')->where($data)->select();
        if(empty($result)){
            $this->returnJson(0,'暂无数据');
        }
        $this->returnJsonData(1,'请求成功',$result);
    }
    
/*报警记录
 * @param int type 1当月2近三月3一年
 */   
    
    public function getWarnningLog(){
        $member_id =$this->verifyUser();
        $this->__checkParam('page,type',$this->input);
        extract($this->input);
        $page= intval($page);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }
//        $model_memberequip=model('MemberEquip');
//        $equip_result =$model_memberequip->getDetail($member_id,$equip_id);
//        if(empty($equip_result)){
//            $this->returnJson(0,'找不到该设备');
//        }
        
        
        $data['a.member_id']=$member_id;
     //   $data['a.equip_id']=$equip_id;
        $data['a.type']=1;        
        //获取时间
        $timearr =getTypeTime($type);
        if(!is_array($timearr) && $timearr==0){
            
            $this->returnJson(0, '时间类型有误');
        
        }else{
            $first =$timearr['first'];
            $last =$timearr['last'];
            $data['a.send_date']=[['egt',$first],['elt',$last],'and'];
        }
        $page_size=10;
        $prex =config('database.prefix');
        
        $result =db('EquipWarn')->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','left')
                ->join($prex.'member_equip c','a.equip_id=c.equip_id','left')
                ->field('a.member_id,a.equip_id,b.equip_code,c.name,a.call_num,a.content,a.create_time')->where($data)->order('create_time desc')->limit(($page-1)*$page_size.",".$page_size)->select();
        
        if(empty($result)){
            $this->returnJson(0,'暂无更多内容');
        }
        
        foreach($result as $k=>$v){
            $result[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $this->returnJsonData(1, '请求成功',$result);
    }
    
/*录入饲料类型
 * 
 */    
    public function addFeedType(){
        $member_id =$this->verifyUser();
        $this->__checkParam('name',$this->input);
        extract($this->input);
        $data=$this->input;
        $data['create_time']=time();
        $data['member_id']=$member_id;
        $back =model('FeedType')->__msave($data,'FeedType');
        $this->returnJson($back['code'],$back['msg']);
        
    }
    
    public function getFeedTypeList(){
        $member_id =$this->verifyUser();
        
        $data['member_id']=$member_id;
        $result =db('FeedType')->where($data)->select();
        if(empty($result)){
            $this->returnJson(0,'暂无类型');
        }
        $this->returnJsonData(1,'请求成功',$result);
        
    }
    
    
    public function delFeedType(){
        $member_id =$this->verifyUser();
        $this->__checkParam('id',$this->input);
        extract($this->input);
        
        $result =db('FeedType')->find($id);
        if($result['member_id']!=$member_id){
            $this->returnJson(0, '非法操作');
        }
        
        db('FeedType')->where('id='.$id)->delete();
        $this->returnJson(1,'删除成功');
        
    }

/*天气预警日志
 * @param int type 1当月2近三月3一年
 */   
    
    public function getWeatherLog(){
        $member_id =$this->verifyUser();
        $this->__checkParam('page,type',$this->input);
        extract($this->input);
        $page= intval($page);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }
        
        $data['a.member_id']=$member_id;  
        //获取时间
        $timearr =getTypeTime($type);
        if(!is_array($timearr) && $timearr==0){
            
            $this->returnJson(0, '时间类型有误');
        
        }else{
            $first =strtotime($timearr['first']);
            $last =strtotime($timearr['last']." 23:59:59");
            $data['a.create_time']=[['egt',$first],['elt',$last],'and'];
        }
        $page_size=10;
        $prex =config('database.prefix');
        $thedb =db('MemberWarnLog');
        $result =$thedb->alias('a')
                ->join($prex.'weather_log b','a.weather_id=b.id','left')
                ->join($prex.'member_warn c','a.mwarn_id=c.id','left')
                ->field('a.create_time,c.city,b.issueContent')->where($data)->order('create_time desc')->limit(($page-1)*$page_size.",".$page_size)->select();
    //    echo $thedb->getLastSql();exit;
        if(empty($result)){
            $this->returnJson(0,'暂无更多内容');
        }
        
        foreach($result as $k=>$v){
            $result[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
        }
        $this->returnJsonData(1, '请求成功',$result);
    }
    
    
/*获取设备的操作记录
 *  *  param $type string 1添加通道2修改通道3打开4关闭通道5布防6撤防7硬件端操作
 */    
    
    public function getEquipOpeLog(){
        $member_id =$this->verifyUser();
        $this->__checkParam('page,type',$this->input);
        extract($this->input);
        if($page<=0){
            $this->returnJson(0,'页码格式有错');
        }
        
        $equip_id =isset($this->input['equip_id'])?$this->input['equip_id']:0;
        $data['a.member_id']=$member_id;  
        
        if($equip_id>0){
            $data['a.equip_id']=$equip_id;
        }
        //获取时间
        $timearr =getTypeTime($type);
        if(!is_array($timearr) && $timearr==0){
            
            $this->returnJson(0, '时间类型有误');
        
        }else{
            $first =strtotime($timearr['first']);
            $last =strtotime($timearr['last']." 23:59:59");
            $data['a.create_time']=[['egt',$first],['elt',$last],'and'];
        }
        $page_size=10;
        $prex =config('database.prefix');
        $thedb =db('EquipOpe');
        $result =$thedb->alias('a')
                ->join($prex.'equipment b','a.equip_id=b.id','left')
                ->join($prex.'member_equip c','a.equip_id=c.equip_id','left')
                ->field('a.create_time,c.name,b.equip_code,a.type,a.content')->where($data)->order('create_time desc')->limit(($page-1)*$page_size.",".$page_size)->select();
        
        if(empty($result)){
            $this->returnJson(0,'暂无更多内容');
        }
        
        
        $type_arr=model('index/EquipOpe')->get_type_name();
        foreach($result as $k=>$v){
            $result[$k]['create_time']=date('Y-m-d H:i:s',$v['create_time']);
            $result[$k]['type_name']=$type_arr[$v['type']];
        }
        $this->returnJsonData(1, '请求成功',$result);
        
        
    }
}
