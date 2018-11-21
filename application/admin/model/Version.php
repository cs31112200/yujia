<?php
namespace app\admin\model;

use app\admin\model\Base;

class Version extends Base{

//自动填充时间    
    protected $autoWriteTimestamp = false;

    /*筛选
     *
     */
    public function getSelect($param=null) {
        $map =[];
        if(isset($param['city_name'])){
            $map['city_name']=$param['city_name'];
        }
        return $map;
    }
    public function getListData($param=null){
        $map =[];
        $page =(isset($param['page']) && intval($param['page'])>0)?intval($param['page']):1;
        $page_size=(isset($param['limit']) && intval($param['limit'])>0)?intval($param['limit']):10;

        if(isset($param['type']) && !empty($param['type'])){
            $map['type']=$param['type'];
        }
        //   print_r($param);exit;
        $count =$this->where($map)->count();
        $sql =$this->where($map)->limit(($page-1)*$page_size.','.$page_size)->order('realversion desc')->buildSql();
        $result =$this->query($sql);
        return $this->generalResult($result,$count);
    }
    public function __formatList($list = null) {
        if(!empty($list)){
            foreach($list as $k=>$v){
                $list[$k]['status']=$v['status']==1?"正常":"失效";
                $list[$k]['is_force']=$v['is_force']==1?"是":"不是";
                $list[$k]['qrcode']=generalQnyImg($list[$k]['qrcode']);
                if($list[$k]['type']==1){
                    $list[$k]['type']='android';
                }
                if($list[$k]['type']==2){
                    $list[$k]['type']='ios';
                }

                if(!empty($list[$k]['create_time'])){
                    $list[$k]['create_time']=date('Y-m-d H:m:s',$list[$k]['create_time']);
                }
            }
        }

        return $list;
    }

    public function __formatEdit($data = null) {
        $data['down_url']=isset($data['down_url'])?generalQnyImg($data['down_url']):"";
        return $data;
    }


    public function __my_before_insert(&$data){

        if(!isset($data['bversion']) || intval($data['bversion'])<=0){
            $this->setError('大版本号格式有误');
            return false;
        }

        if(!isset($data['mversion']) || intval($data['mversion'])<0){
            $this->setError('中版本号格式有误');
            return false;
        }


        if(!isset($data['sversion']) || intval($data['sversion'])<0){
            $this->setError('小版本号格式有误');
            return false;
        }
        $data['bversion']=intval($data['bversion']);
        $data['mversion']=intval($data['mversion']);
        $data['sversion']=intval($data['sversion']);
        $data['realversion']=($data['bversion']).".".($data['mversion']).".".($data['sversion']);

        $data1['type']=$data['type'];
        $data1['realversion']=$data['realversion'];
        $result =db($this->getTheTable())->where($data1)->find();
        if(!empty($result)){
            $this->setError('该版本已经存在');
            return false;
        }
        $data['status']=1;
        $data['create_time']=time();
        return true;
    }

    public function __my_before_update(&$data){


        $resultss =db($this->getTheTable())->find($data['id']);
        if(empty($resultss)){
            $this->setError('找不到该id对应数据');return false;
        }

        if(!isset($data['bversion']) || intval($data['bversion'])<=0){
            $this->setError('大版本号格式有误');
            return false;
        }

        if(!isset($data['mversion']) || intval($data['mversion'])<0){
            $this->setError('中版本号格式有误');
            return false;
        }


        if(!isset($data['sversion']) || intval($data['sversion'])<0){
            $this->setError('小版本号格式有误');
            return false;
        }
        $data['bversion']=intval($data['bversion']);
        $data['mversion']=intval($data['mversion']);
        $data['sversion']=intval($data['sversion']);


        $data['realversion']=($data['bversion']).".".($data['mversion']).".".($data['sversion']);

        if($resultss['type']!=$data['type'] && $resultss['realversion']!=$data['realversion']){
            $data1['type']=$data['type'];
            $data1['realversion']=$data['realversion'];
            $result =db($this->getTheTable())->where($data1)->find();
            if(!empty($result)){
                $this->setError('该版本已经存在');
                return false;
            }
        }
        if(empty($data['qrcode'])){
            $data['qrcode']=$resultss['qrcode'];
        }
        if(empty($data['down_url'])){
            $data['down_url']=$resultss['down_url'];
        }
        return true;
    }


    public function getNewVersion(){
        $result =db($this->getTheTable())->where('status=1')->order('bversion desc,mversion desc,sversion desc')->select();
        $return =[];
        if(!empty($result)){

            // 区分ios 跟android
            foreach($result as $k=>$v){
                $return1[$v['type']][]=$v;
            }
            foreach($return1 as $k=>$v){
                $v[0]['down_url'] = generalImg($v[0]['down_url']);
                $return[$k]=$v[0];
            }

        }



        return $return;
    }
    public function setqrcode($id){
        $version=db('Version')->where('id',$id)->find();
        if(!empty($version['down_url'])){
            $url= generalQnyImg($version['down_url']);
            $savePath = APP_PATH . '../public/qrcode/';
            $webPath = '/qrcode/';
            $qrData = $url;
            $qrLevel = 'H';
            $qrSize = '10';
            $savePrefix = 'Yujia';
            if($filename = createQRcodeToQny($savePath, $qrData, $qrLevel, $qrSize, $savePrefix)){
                $pic = $filename;
            }
            $data['qrcode']=$pic;
            $result=db('Version')->where('id',$id)->update($data);
            if($result){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    public function getTypeList(){
        $map=[];
        $result = db($this->getTheTable())->where($map)->field('type')->group('type')->select();
        return $result;
    }

}