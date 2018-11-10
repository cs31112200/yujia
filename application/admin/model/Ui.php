<?php
namespace app\admin\model;

use think\Model;

class Ui extends Model{


    /*根据Db获取select列表
     * @param string $db_name 数据库名称（去掉si_）
     * @param array $param 查询参数
     * @param string $key_name select控件的value
     * @param string $value_name select控件的html
     *  @param string $id_name select控件的id
     * @param string $select 选中的值 没有为空
     */
    public function getDbSelectList($db_name,$param,$key_name,$value_name,$id_name,$bind_func='',$select=''){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"  onchange="'.$bind_func.'();"   " class="col-xs-12 ">';

        $result =db($db_name)->where($param)->field("$key_name , $value_name")->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $v['select'] =($v[$key_name]==$select)?'selected':'';
                $str.="<option  ".$v['select']." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }
        return $str.="</select>";
    }


    /*根据Db获取Norselect列表
     * @param string $db_name 数据库名称（去掉si_）
     * @param array $param 查询参数
     * @param string $key_name select控件的value
     * @param string $value_name select控件的html
     *  @param string $id_name select控件的id
     * @param string $select 选中的值 没有为空
     */
    public function getDbSelectNorList($db_name,$param,$key_name,$value_name,$id_name,$controller_name,$bind_func='',$select=''){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"  onchange="'.$bind_func.'();"   "col-xs-12 l-xs-12 "><option  value="">['.$controller_name.']</option>';

        $result =db($db_name)->where($param)->field("$key_name , $value_name")->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $v['select'] =($v[$key_name]==$select)?'selected':'';
                $str.="<option  ".$v['select']." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }
        return $str.="</select>";
    }
    /*根据Db获取Norselect列表
         * @param string $db_name 数据库名称（去掉si_）
         * @param array $param 查询参数
         * @param string $key_name select控件的value
         * @param string $value_name select控件的html
         *  @param string $id_name select控件的id
         * @param string $select 选中的值 没有为空
         */
    public function getDbSelectNorListtwo($db_name,$param,$key_name,$value_name,$id_name,$controller_name,$bind_func='',$select='',$is_ignore=''){

        $theignore =$is_ignore==1?'lay-ignore':"";

        $str='<select  '.$theignore.' name="'.$id_name.'" id="'.$id_name.'"  onchange="'.$bind_func.'();"    class="layui-input" style="width:769px"><option  value="">['.$controller_name.']</option>';

        $result =db($db_name)->where($param)->field("$key_name , $value_name")->select();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $v['select'] =($v[$key_name]==$select)?'selected':'';
                $str.="<option  ".$v['select']." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }
        return $str.="</select>";
    }
    /*根据Model获取select列表
     * @param string $model_name model名称
     * @param string $function 函数名称
     * @param array $param 查询参数
     * @param string $key_name select控件的value
     * @param string $value_name select控件的html
     *  @param string $id_name select控件的id
     * @param string $select 选中的值 没有为空
     */
    public function getModelSelectList($model_name,$function,$param,$key_name,$value_name,$id_name,$select=''){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"  class="">';

        $result =model($model_name)->$function($param);
        if(!empty($result)){
            foreach($result as $k=>$v){
                $isselect =($v[$key_name]==$select)?'selected':'';
                $str.="<option ".$isselect." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }
        return $str.="</select>";
    }

    public function getModelSelectLists($model_name,$function,$param,$key_name,$value_name,$id_name,$control_name,$bind_func,$select=''){


        $onchange =empty($bind_func)?"":'onchange="'.$bind_func.'();"';

        if(!empty($control_name))
            $str='<select lay-filter="'.$id_name.'" name="'.$id_name.'" id="'.$id_name.'"  '.$onchange.'  ><option  value="">['.$control_name.']</option>';
        else
            $str='<select name="'.$id_name.'" id="'.$id_name.'"  '.$onchange.' >';


        $result =model($model_name)->$function($param);
        if(!empty($result)){
            foreach($result as $k=>$v){
                $isselect =($v[$key_name]==$select)?'selected':'';
                $str.="<option ".$isselect." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }

        return $str.="</select>";
    }
        public function getModelSelectListTwo($model_name,$function,$param,$key_name,$value_name,$id_name,$control_name,$bind_func,$select=''){


        $onchange =empty($bind_func)?"":'onchange="'.$bind_func.'();"';

        if(!empty($control_name))
            $str='<select lay-search lay-filter="'.$id_name.'" name="'.$id_name.'" id="'.$id_name.'"  '.$onchange.'  ><option  value="">['.$control_name.']</option>';
        else
            $str='<select name="'.$id_name.'" id="'.$id_name.'"  '.$onchange.' >';


        $result =model($model_name)->$function($param);
        if(!empty($result)){
            foreach($result as $k=>$v){
                $isselect =($v[$key_name]==$select)?'selected':'';
                $str.="<option ".$isselect." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }

        return $str.="</select>";
    }





    /*根据Model获取select列表 [0=>'不通过',1=>'通过']
     *
     *
     */
    public function getModelNorSelectList($model_name,$function,$control_name,$id_name,$bind_func,$select=''){

        //$onchange =empty($bind_func)?"":'onchange="'.$bind_func.'();"';
        $onchange="";
        if(!empty($control_name))
            $str='<select name="'.$id_name.'" id="'.$id_name.'" lay-filter="'.$id_name.'"'.$onchange.'  ><option  value="">['.$control_name.']</option>';
        else
            $str='<select name="'.$id_name.'" id="'.$id_name.'"  lay-filter="'.$id_name.'"'.$onchange.'  >';

        $result =model($model_name)->$function();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $selects=($select==$k)? 'selected':'';
                $str.="<option ".$selects." value='".$k."'>".$v."</option>";
            }
        }
        return $str.="</select>";
    }
    public function getModelNorSelectListChange($model_name,$function,$control_name,$id_name,$bind_func,$theid,$thetype,$select=''){

        $onchange =empty($bind_func)?"":'onchange="'.$bind_func.'($(this));"';

        if(!empty($control_name))
            $str='<select lay-ignore theid="'.$theid.'"  thetype="'.$thetype.'" name="'.$id_name.'" id="'.$id_name.'"'.$onchange.'  ><option  value="">['.$control_name.']</option>';
        else
            $str='<select lay-ignore theid="'.$theid.'"  thetype="'.$thetype.'" name="'.$id_name.'" id="'.$id_name.'" '.$onchange.'  >';

        $result =model($model_name)->$function();
        if(!empty($result)){
            foreach($result as $k=>$v){
                $selects=($select==$k)? 'selected':'';
                $str.="<option ".$selects." value='".$k."'>".$v."</option>";
            }
        }
        return $str.="</select>";
    }



    public function getModelNorSelectListWithParam($model_name,$function,$param,$control_name,$id_name,$bind_func,$select=''){

        //$onchange =empty($bind_func)?"":'onchange="'.$bind_func.'();"';
        $onchange="";
        if(!empty($control_name))
            $str='<select name="'.$id_name.'" id="'.$id_name.'" lay-filter="'.$id_name.'"'.$onchange.'  ><option  value="">['.$control_name.']</option>';
        else
            $str='<select name="'.$id_name.'" id="'.$id_name.'"  lay-filter="'.$id_name.'"'.$onchange.'  >';

        $result =model($model_name)->$function($param);
        if(!empty($result)){
            foreach($result as $k=>$v){
                $selects=($select==$k)? 'selected':'';
                $str.="<option ".$selects." value='".$k."'>".$v."</option>";
            }
        }
        return $str.="</select>";
    }




    /*层级关系
     * @param string $model_name model名称
     * @param string $function 函数名称
     * @param array $param 查询参数
     * @param string $key_name select控件的value
     * @param string $value_name select控件的html
     *  @param string $id_name select控件的id
     *  @param int $type 分层的级数 第一级:1第二级:2依次类推
     * @param string $select 选中的值 没有为空
     * @param string $control_name 此控件名称 没有为空
     * @param string $bind_func 控件绑定函数 没有为空
     */
    public function getRelationSelectList($model_name,$function,$param,$key_name,$value_name,$id_name,$type,$select='',$control_name='',$is_ignore='',$bind_func=''){

        $theignore =$is_ignore==1?'lay-ignore':"";

        if(!empty($bind_func))
            $str='<select  '.$theignore.' name="'.$id_name.'" id="'.$id_name.'"  onchange="'.$bind_func.'();"  class="col-xs-12 ">';
        else
            $str='<select '.$theignore.' name="'.$id_name.'" id="'.$id_name.'"  class="col-xs-12 ">';

        if(!empty($control_name)){
            $str.='<option value="0">['.$control_name.']</option>';
        }

        $result =model($model_name)->$function($param);

        if(!empty($result)){
            foreach($result as $k=>$v){
                $v['select']=($select==$v[$key_name])?'selected':'';
                $str.="<option ".$v['select']." value='".$v[$key_name]."'>".$v[$value_name]."</option>";
            }
        }
//        if(empty($result[0]['id'])){
//        return ;}
//        $mark=empty($select)?$result[0]['id']:$select;
        cookie($id_name.'_relation_'.$type,$select);
        return $str.="</select>";
    }


    /*获取菜单列表
     * @param mix $select 选中的值
     */
    public function getMenuList($id_name,$select=null){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"   " >';
        $str.='<option value="0">顶级菜单</option>';

        $result =model('Menu')->getAllMenu();
        // print_r($result);exit;
        if(!empty($result)){

            foreach($result as $k=>$v){
                $v['select']=$select==$v['id']?'selected':'';
                $str.="<option ".$v['select']." value='".$v['id']."'>"."&nbsp;".$v['name']."</option>";
                if(isset($v['childs']) && !empty($v['childs'])){
                    foreach($v['childs'] as $k1=>$v1){
                        $v1['select']=$select==$v1['id']?'selected':'';
                        $str.="<option ".$v1['select']." value='".$v1['id']."'>"."&nbsp;&nbsp;└".$v1['name']."</option>";
                        if(isset($v1['childs'])  && !empty($v1['childs'])){
                            // print_r($v1['childs']);
                            foreach($v1['childs'] as $k2=>$v2){
                                $v2['select']=$select==$v2['id']?'selected':'';
                                $str.="<option ".$v2['select']." value='".$v2['id']."'>"."&nbsp;&nbsp;&nbsp;&nbsp;└".$v2['name']."</option>";
                            }
                        }
                    }
                }


            }
            return $str.="</select>";

        }else{
            return "无菜单";
        }

    }
    public function getFinTypeList($id_name,$select=null,$type=1,$ptype=1){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"   " >';
        if($type==2){
            $str.='<option value="">请选择</option>';
        }
        $theselect="";
        if($select==0){ $theselect="selected";}
        $str.='<option value="0"  '.$theselect.'>顶级菜单</option>';

        $result =model('admin/FinanceType')->getAllTypeTree($ptype);
        // print_r($result);exit;
        if(!empty($result)){

            foreach($result as $k=>$v){
                $v['select']=$select==$v['id']?'selected':'';
                $str.="<option ".$v['select']." value='".$v['id']."'>"."&nbsp;".$v['name']."</option>";
                if(isset($v['childs']) && !empty($v['childs'])){
                    foreach($v['childs'] as $k1=>$v1){
                        $v1['select']=$select==$v1['id']?'selected':'';
                        $str.="<option ".$v1['select']." value='".$v1['id']."'>"."&nbsp;&nbsp;└".$v1['name']."</option>";
//                        if(isset($v1['childs'])  && !empty($v1['childs'])){
//                           // print_r($v1['childs']);
//                            foreach($v1['childs'] as $k2=>$v2){
//                                $v2['select']=$select==$v2['id']?'selected':'';
//                                $str.="<option ".$v2['select']." value='".$v2['id']."'>"."&nbsp;&nbsp;&nbsp;&nbsp;└".$v2['name']."</option>";
//                            }
//                        }
                    }
                }
            }
        }
        return $str.="</select>";
    }
    public function getPublicTypeListThree($id_name,$select=null,$type=1){

        $str='<select name="'.$id_name.'" id="'.$id_name.'"   " >';
        $str.='<option value="">请选择</option>';



        $result =model('admin/FinanceType')->getAllTypeTree($type);
        // print_r($result);exit;
        if(!empty($result)){

            foreach($result as $k=>$v){
                $v['select']=$select==$v['id']?'selected':'';
                $str.="<option ".$v['select']." value='".$v['id']."'>"."&nbsp;".$v['name']."</option>";
                if(isset($v['childs']) && !empty($v['childs'])){
                    foreach($v['childs'] as $k1=>$v1){
                        $v1['select']=$select==$v1['id']?'selected':'';
                        $str.="<option ".$v1['select']." value='".$v1['id']."'>"."&nbsp;&nbsp;└".$v1['name']."</option>";
                        if(isset($v1['childs'])  && !empty($v1['childs'])){
                            // print_r($v1['childs']);
                            foreach($v1['childs'] as $k2=>$v2){
                                $v2['select']=$select==$v2['id']?'selected':'';
                                $str.="<option ".$v2['select']." value='".$v2['id']."'>"."&nbsp;&nbsp;&nbsp;&nbsp;└".$v2['name']."</option>";
                            }
                        }
                    }
                }
            }
        }
        return $str.="</select>";
    }




    public function getMenuOptionList($select=null){
        $str='<select  multiple="multiple"  size="10" name="ids[]" id="duallist"   " class="col-xs-12 col-sm-4">';
        $result =db('Menu')->where(1)->field('id,name,pid,sort')->order('pid asc,sort desc')->select();
        $first =[];  //一级
        $second=[];
        $three=[];
        //对菜单进行处理
        if(!empty($result)){
            foreach($result as $k=>$v){

                $v['select']=$select==$v['id']?'selected':'';
                if($v['pid']==0){
                    $first[$v['id']]=$v;
                }else{

                    if(isset($first[$v['pid']]) ){
                        $second[$v['id']]=$v;
                    }else{
                        $three[$v['id']]=$v;
                    }

                }
            }
            foreach($three as $k=>$v){
                $second[$v['pid']]['childs'][$v['id']]=$v;
            }

            foreach($second as $k=>$v){
                $first[$v['pid']]['childs'][$v['id']]=$v;
            }

            //处理显示形式

            foreach($first as $k=>$v){
                $str.="<option ".$v['select']." value='".$v['id']."'>"."&nbsp;".$v['name']."</option>";
                if(isset($v['childs'])){
                    foreach($v['childs'] as $k1=>$v1){
                        $str.="<option ".$v1['select']." value='".$v1['id']."'>"."&nbsp;&nbsp;└".$v1['name']."</option>";
                        if(isset($v1['childs'])){
                            foreach($v1['childs'] as $k2=>$v2){
                                $str.="<option ".$v2['select']." value='".$v2['id']."'>"."&nbsp;&nbsp;&nbsp;&nbsp;└".$v2['name']."</option>";
                            }
                        }
                    }
                }
            }

            return $str.="</select><div class='hr hr-16 hr-dotted'></div>";

        }else{
            return "无菜单";
        }
    }


    /*获取评价列表
     *
        <div class='col-xs-12' style='text-align: center;font-size: 22px; line-height: 24px; padding: 15px;border-bottom: 1px solid #E2E2E2;'>
            审核结果共（1）次
        </div>
     *
        <div class='col-xs-12' style='text-align: center;font-size: 16px; line-height: 26px; padding: 15px;'>
            第（1）次：审核失败
        </div>
        <div class='col-xs-12' style='border-bottom: 1px solid #E2E2E2; '>
            失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容失败内容
        </div>
        <div class='col-xs-12' style='border-bottom: 1px solid #E2E2E2; '>

        </div>
     *
     * @param int $type 1基本信息2履约能力3良好行为4不良行为5黑名单
     */
    public function get_evaluate_list($merchant_id,$type,$id){
        $result =model('merchant/Evaluate')->getTypeContent($merchant_id,$type,$id);
        $str="<div class='col-xs-12' style='text-align: left;font-size: 16px; line-height: 12px; padding: 15px;      background-color: #337ab7;color: #fff;  border: 1px solid #337ab7; border-bottom-color: #BCD4E5;  '>审核结果共（".count($result)."）次<a></a></div>";
        if(!empty($result)){
            foreach($result as $k=>$v){
                $str.="<div class='col-xs-12' style='text-align: center;font-size: 16px; line-height: 26px; padding: 15px;'>第(".($k+1).")次：".$v['status_name']."</div>".
                    "<div class='col-xs-12'><b>原因：</b>".$v['content']."</div>".
                    "<div class='col-xs-12' style='border-bottom: 1px dashed #BCD4E5; margin-top:5px;  padding-bottom: 10px;'><p><b>审核人：</b>".$v['name']."</p>          <p><b>审核时间：</b>".$v['create_time']."</div></p>";
            }
        }
        return $str;
    }


    /*
        <div class="form-group">
            <label for="title" class="col-sm-1 control-label no-padding-right"><font class="red">*</font>密码密码密码密码密码密码</label>
            <div class="col-sm-11">
                <div class="clearfix">
                <input type="password" name="password" id="password" value="{$app.password|default=''}" size="40" class="col-xs-12 col-sm-2" /></div>
            </div>
        </div>
     */
    public function getPrList($pr_content){
        $pr_content = (!empty($pr_content))?unserialize($pr_content):[];
        $result =model('admin/Abpr')->getPrList();
        $str="";
        if(!empty($result)){
            foreach($result as $k=>$v){
                $value =(isset($pr_content["pr_".$v['id']]))?$pr_content["pr_".$v['id']]:"";
                $str.='<div class="form-group col-sm-6"><label for="title" class="col-sm-6 control-label"><font class="red">*</font>'.$v['name'].'</label><div class="col-sm-6">'.
                    '<div class="clearfix"><input type="text" name="pr_'.$v['id'].'" id="pr_'.$v['id'].'" value="'.$value.'" size="40" class="col-xs-12 col-sm-9" /><span style="margin-left:10px;font-size:14px !important;line-height: 25px;">(人)</span></div></div></div>';
            }
        }else{
            $str='<div class="form-group"><label for="title" class="col-sm-1 control-label"><font class="red">*</font>后台未设置相关项目</label><div class="col-sm-11"><div class="clearfix"></div></div></div>';
        }
        return $str;
    }

    public function getPrLists($pr_content){
        $pr_content = (!empty($pr_content))?unserialize($pr_content):[];
        $html = '';
        $tree =model('admin/Abpr')->getPrListOrder();
        return $this->procHtml($tree,$pr_content,0);
    }

    public function procHtml($tree,$pr_content,$count){
        $html = '';
        foreach($tree as $t){
            $value =(isset($pr_content["pr_".$t['id']]))?$pr_content["pr_".$t['id']]:"";
            $input =($t['is_write']==1)?'<input type="text" name="pr_'.$t['id'].'" id="pr_'.$t['id'].'" value="'.$value.'"  size=5 />人':"";
            if(empty($t['childs'])){
                $html .= '<li class="tree-item" role="treeitem"><div class="tree-branch-header"><span class="tree-branch-name"><span class="tree-label">'.$t['name'].$input.'</span></span></div></li>';
            }else{
                $html .= '<li class="tree-branch" role="treeitem" aria-expanded="false"><div class="tree-branch-header"><span class="tree-branch-name"><i class="icon-folder ace-icon tree-minus"></i><span class="tree-label">'.$t['name'].$input.'</span></span></div>';
                $count++;
                $html .= $this->procHtml($t['childs'],$pr_content,$count);
                $html = $html."</li>";
            }
        }
        if($count==1){
            return $html ? '<ul  class="tree">'.$html.'</ul>' : $html ;
        }else{
            return $html ? '<ul  class="tree-branch-children">'.$html.'</ul>' : $html ;
        }

    }


}