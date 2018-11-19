<?php
namespace app\admin\model;

use app\admin\model\Base;

class Advert extends Base{
//
////自动填充时间
//protected $autoWriteTimestamp = false;
//
//
//
/*获取广告位置
 *
 */
    public function getAdPlaceName(){
        return [
            [
                'id'=>1,
                'name'=>'天气'
            ],
            [
                'id'=>2,
                'name'=>'日志'
            ]
        ];
    }

    public function getAdPlaceNameTwo(){
           $space_name =$this->getAdPlaceName();
           $space_name1=[];
           foreach($space_name as $k=>$v){
               $space_name1[$v['id']]=$v['name'];
           }
           return $space_name1;
    }
//
///*筛选
// *
// */
//    public function getSelect($param=null) {
//        $map =[];
//        if(isset($param['ad_space'])){
//            $map['ad_space']=$param['ad_space'];
//        }
//        return $map;
//    }
//
//    public function __formatList($list = null) {
//       if(!empty($list)){
//           $space_name1 =$this->getAdPlaceNameTwo();
//           foreach($list as $k=>$v){
//               $list[$k]['ad_space_name']=$space_name1[$v['ad_space']];
//               $list[$k]['tourl_type_name']=($v['tourl_type']==1)?'内网':'外网';
//           }
//       }
//       return $list;
//    }
//
//    public function __formatEdit($data = null) {
//        $data['imgs']=isset($data['img'])?generalImg($data['img']):"";
//        return $data;
//    }
//
/*按位置获取广告
 *
 */
   public function  getAdvertBySpace($ad_place){
       $data['ad_space']=$ad_place;
       $data['status']=1;
       $result =db($this->getTheTable())->where($data)->field('img,tourl,tourl_type')->select();
       if(!empty($result)){
           foreach($result as $k=>$v){
               $result[$k]['img']=  generalQnyImg($v['img']);
           }
       }
       return $result;
   }
//}

//自动填充时间
    protected $autoWriteTimestamp = false;



    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['ad_space']) && !empty($param['ad_space'])){
            $map['ad_space']=$param['ad_space'];
        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['status']=$v['status']==1?"正常":"失效";
                $list[$k]['tourl_type']=$v['tourl_type']==1?"内网":"外网";
                $list[$k]['ad_space']=$v['ad_space']==1?"天气":"日志";
                $list[$k]['imgs']=isset($v['img'])?generalQnyImg($v['img']):"";
                if(!empty($list[$k]['create_time'])){
                    $list[$k]['create_time']=date('Y-m-d H:m:s',$list[$k]['create_time']);
                }
            }
            return $list;
        }
    }
    /*处理编辑初始化
 *
 */
    public function __formatEdit($data=null){
        $data['img']=empty($data['img'])?'':generalQnyImg($data['img']);
        return $data;
    }

    /*更新之前
     *
     */
    public function __my_before_update(&$data){
//        dump($data);exit();
        $picture=db('Advert')->where('id',$data['id'])->find();
        if(empty($data['img'])){
            $data['img']=$picture['img'];
        }
//        if(empty($data['url'])){
//            $data['url']=$data['down_url'];
//        }
//        unset($data['down_url']);
        return true;
    }

    /*插入之前
     *
     */

    public function __my_before_insert(&$data){
//        dump($data);exit();
//        if(isset($data['down_url'])){
//            unset($data['down_url']);
//        }
        $data['status']=1;
        $data['create_time']=time();
        return TRUE;
    }
}