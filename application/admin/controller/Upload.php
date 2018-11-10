<?php
namespace app\admin\controller;
use think\Db;
use think\Config;
use think\Controller;

class Upload extends Controller{

    
    protected function _initialize(){

        $result = is_login();
        if( !$result || !is_array($result)){// 还没登录 跳转到登录页面
            $back['code']=0;
            $back['msg']='非法上传';
           return json_encode($back);
        };
    }
    
/*图片上传阿里云
 * 
 */

    public function pictureUpload(){
        

        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];$k=0;
        foreach($file as $item){
            $info = $item->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $oss_config =config('UPLOAD_OSS_CONFIG');   
                import('oss.oss',EXTEND_PATH);
                $save_name =$info->getSaveName();
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path =UPLOAD_PATH.'/uploads/'.$save_name;
                $oss = new \ALIOSS();
                $result =$oss->upload_file_by_file($oss_config['bucket'], $save_name, $file_path);
                $return[$k]['show_url'] =$oss_config['url'].$save_name;
                $return[$k]['save_url'] =$save_name;
                @unlink($file_path);
            }else{
                // 上传失败获取错误信息
                $back['code']=0;
                $back['msg']=$item->getError();
                $back['data']='';
                return ($back);
            }
            $k++;
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
    
/*本地
 * 
 */    
    public function pictureUploadLocal(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];$k=0;
        foreach($file as $item){
            $info = $item->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $save_name =$info->getSaveName();
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path ='/uploads/'.$save_name;
                $return[$k]['show_url'] =$file_path;
                $return[$k]['save_url'] =$file_path;
            }else{
                // 上传失败获取错误信息
                $back['code']=0;
                $back['msg']=$item->getError();
                $back['data']='';
                return ($back);
            }
            $k++;
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }

    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];

        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');

        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $return['show_url'] =$file_path;
            $return['save_url'] =$file_path;
        }else{
            // 上传失败获取错误信息
            $back['code']=0;
            $back['msg']=$file->getError();
            $back['data']='';
            return ($back);
        }


        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
/*上传文件
 * 
 */    
    
    public function fileUpload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];$k=0;
        foreach($file as $item){
            $info = $item->validate(['size'=>1024*1024*100,'ext'=>'pdf,doc,docx,xls,xlsx,zip,rar'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $oss_config =config('UPLOAD_OSS_CONFIG');   
                import('oss.oss',EXTEND_PATH);
                $save_name =$info->getSaveName();
             //   $save_name=$_FILES['file']['name'][$k];
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path =UPLOAD_PATH.'/uploads/'.$save_name;
                $oss = new \ALIOSS();
                $result =$oss->upload_file_by_file($oss_config['bucket'], $save_name, $file_path);
                $return[$k]['show_url'] =$oss_config['url'].$save_name;
                $return[$k]['save_url'] =$save_name;
                $return[$k]['file_name']=$_FILES['file']['name'][$k];
                @unlink($file_path);
            }else{
                // 上传失败获取错误信息
                $back['code']=0;
                $back['msg']=$item->getError();
                $back['data']='';
                return ($back);
            }
            $k++;
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
    
    public function fileUploadLocal(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
      //  print_r($_FILES);exit;
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];$k=0;
        foreach($file as $item){
            $info = $item->validate(['size'=>1024*1024*100,'ext'=>'pdf,doc,docx,xls,xlsx,zip,rar'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $oss_config =config('UPLOAD_OSS_CONFIG');   
                import('oss.oss',EXTEND_PATH);
                $save_name =$info->getSaveName();
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path ='/uploads/'.$save_name;
                $return[$k]['show_url'] =$file_path;
                $return[$k]['save_url'] =$file_path;
                $return[$k]['file_name']=$_FILES['file']['name'][$k];
            }else{
                // 上传失败获取错误信息
                $back['code']=0;
                $back['msg']=$item->getError();
                $back['data']='';
               return $back;
            }
            $k++;
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
    
    
/*综合
 * 
 */    
    
    public function bothUploadLocal(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        
        $img_ext ='jpg,png,gif,jpeg';
        $file_ext='doc,docx,xls,xlsx,zip,rar,pdf';
        
        $return =[];$k=0;
        foreach($file as $item){
            $info = $item->validate(['size'=>1024*1024*100,'ext'=>'jpg,png,gif,jpeg,pdf,doc,docx,xls,xlsx,zip,rar'])->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $save_name =$info->getSaveName();
                $save_name =  str_replace('\\', '/', $save_name);
                $file_path ='/uploads/'.$save_name;
                $return[$k]['show_url'] =$file_path;
                $return[$k]['save_url'] =$file_path;
                $return[$k]['file_name']=$_FILES['file']['name'][$k];
                $the_ext = $info->getExtension();
                $return[$k]['object_type']=(strpos($img_ext,$the_ext)!==false)?1:2;
            }else{
                // 上传失败获取错误信息
                $back['code']=0;
                $back['msg']=$item->getError();
                $back['data']='';
               return $back;
            }
            $k++;
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
    
    
/*控件上传
 * 
 */    
    public function kJupload(){
        $file = request()->file('imgFile');
        // 移动到框架应用根目录/public/uploads/ 目录下
    //    print_r($file);exit;
        $img_ext ='jpg,png,gif,jpeg';
        $file_ext='doc,docx,xls,xlsx,zip,rar,pdf';
        
        $return =[];
        $info = $file->validate(['size'=>1024*1024*100,'ext'=>'jpg,png,gif,jpeg,pdf,doc,docx,xls,xlsx,zip,rar'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $file_path = generalImg($file_path);
            $return['show_url'] =($file_path);
            $return['save_url'] =$file_path;
            $return['file_name']=$_FILES['imgFile']['name'];
            $the_ext = $info->getExtension();
            $return['object_type']=(strpos($img_ext,$the_ext)!==false)?1:2;
        }else{
            // 上传失败获取错误信息
            $back['error']=1;
            $back['message']=$file->getError();
           return json_encode($back);
        }
        $back['error']=0;
        $back['url']=$return['show_url'];
        return (json_encode($back));
    }
    
    

    public function kJUploadFile(){
        
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
     //   print_r($file);exit;
        $img_ext ='jpg,png,gif,jpeg';
        $file_ext='doc,docx,xls,xlsx,zip,rar,pdf';
        
        $return =[];
        $info = $file->validate(['size'=>1024*1024*100,'ext'=>'pdf,doc,docx,xls,xlsx,zip,rar'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $return['show_url'] =$file_path;
            $return['save_url'] =$file_path;
            $return['file_name']=$_FILES['file']['name'];
            $the_ext = $info->getExtension();
            $return['object_type']=(strpos($img_ext,$the_ext)!==false)?1:2;
        }else{
            // 上传失败获取错误信息
            $back['code']=0;
            $back['msg']=$file->getError();
            $back['data']='';
           return json_encode($back);
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return json_encode($back);
    }

    public function kJUploadPicture(){
        
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
     //   print_r($file);exit;
        $img_ext ='jpg,png,gif,jpeg';
     //   $file_ext='doc,docx,xls,xlsx,zip,rar,pdf';
        
        $return =[];
        $info = $file->validate(['size'=>1024*1024*100,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $return['show_url'] =$file_path;
            $return['save_url'] =$file_path;
            $return['file_name']=$_FILES['file']['name'];
            $the_ext = $info->getExtension();
            $return['object_type']=(strpos($img_ext,$the_ext)!==false)?1:2;
        }else{
            // 上传失败获取错误信息
            $back['code']=0;
            $back['msg']=$file->getError();
            $back['data']='';
           return json_encode($back);
        }
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return json_encode($back);
    }
    
/*kingeditor
 * 
 */    
    public function uploadEditer(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('imgFile');
        // 移动到框架应用根目录/public/uploads/ 目录下
        
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $oss_config =config('UPLOAD_OSS_CONFIG');   
            import('oss.oss',EXTEND_PATH);
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path =UPLOAD_PATH.'/uploads/'.$save_name;
            $oss = new \ALIOSS();
            $result =$oss->upload_file_by_file($oss_config['bucket'], $save_name, $file_path);
            $show_url =$oss_config['url'].$save_name;
            @unlink($file_path);
        }else{
            // 上传失败获取错误信息
            $back['error']=1;
            $back['message']=$file->getError();
            return (json_encode($back));
        }
        $back['error']=0;
        $back['url']=$show_url;
        return (json_encode($back));
    }
    
/*单图上传
 * 
 */
    public function siglePictureUpload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        
        // 移动到框架应用根目录/public/uploads/ 目录下
        $return =[];$k=0;
        $info = $file->validate(['ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $return['show_url'] =$file_path;
            $return['save_url'] =$file_path;
        }else{
            // 上传失败获取错误信息
            $back['code']=0;
            $back['msg']=$item->getError();
            $back['data']='';
            return ($back);
        }
        
        $back['code']=(!empty($return))?1:0;
        $back['data']=$return;
        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
        return ($back);
    }
    /*识别excel
 *
 */
    public function readExcel(){

        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        //   print_r($file);exit;

        $return =[];
        $info = $file->validate(['size'=>1024*1024*20,'ext'=>'xls,xlsx'])->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            $save_name =$info->getSaveName();
            $save_name =  str_replace('\\', '/', $save_name);
            $file_path ='/uploads/'.$save_name;
            $real_path =UPLOAD_PATH.'/uploads/'.$save_name;


            //这里读取文件
            import('phpexcel.PHPExcel.IOFactory',EXTEND_PATH);
            $objPHPExcel = \PHPExcel_IOFactory::load($real_path);
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,false);
            if(empty($sheetData)){
                $back['code']=0;
                $back['msg']='未获取到数据';
                $back['data']='';
                return ($back);
            }
            unset($sheetData[0]);
            $sheetData=array_values($sheetData);
            if(empty($sheetData)){
                $back['code']=0;
                $back['msg']='未获取到数据';
                $back['data']='';
                return ($back);
            }
//            dump($sheetData);exit();
            foreach($sheetData as $k=>$v){
//                $sheetData[$k][1]=substr($v[1],1);
                $sheetData[$k][2]=substr($v[2],3);
            }
            cache('sheet_data',$sheetData);
            $back['code']=1;
            $back['msg']='获取数据成功';
            $back['data']=$sheetData;
            return ($back);

            print_r($sheetData);


        }else{
            // 上传失败获取错误信息
            $back['code']=0;
            $back['msg']=$file->getError();
            $back['data']='';
            return ($back);
        }
//        $back['code']=(!empty($return))?1:0;
//        $back['data']=$return;
//        $back['msg'] =($back['code']==1)?"上传成功":'上传失败';
//        return ($back);
    }
}
