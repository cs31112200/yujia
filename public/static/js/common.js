
/*重写ajax-post
 * 
 */
function doAjaxPost(obj){
    var target,query,form;
    var target_form = obj.attr('target-form');
    var that = obj;
    var nead_confirm=false;

    if( (obj.attr('type')=='button') || (target = obj.attr('href')) || (target = obj.attr('url')) ){
        form = $('.'+target_form);
        if (obj.attr('hide-data') === 'true'){//无数据时也可以使用的功能
            form = $('.hide-data');
            query = form.serialize();
        }else if (form.get(0)==undefined){
            return false;
        }else if ( form.get(0).nodeName=='FORM' ){
            if ( obj.hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            if(obj.attr('url') !== undefined){
                    target = obj.attr('url');
            }else{
                    target = form.get(0).action;
            }
            query = form.serialize();
        }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
            form.each(function(k,v){
                if(v.type=='checkbox' && v.checked==true){
                    nead_confirm = true;
                }
            })
            if ( nead_confirm && obj.hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.serialize();
        }else{
            if ( obj.hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.find('input,select,textarea').serialize();
        }
        loading(1);
        $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
        $.post(target,query).success(function(data){
            alertMsg(data.code,data.msg,data.url);
            console.log(data);
            $(that).removeClass('disabled').prop('disabled',false);
            loading(2);
        });
    }else{}
    return false;
}



/*修改状态函数
 * 
 */
function change_status(obj){
    var is_ok=0;var warm_content='';
    layui.use('layer', function(){
        
    var ope =obj.attr('ope');
    if(ope=="" || typeof(ope)=="undefined"){
        alertMsg(4,'您未配置ope');
         return false;
    }
    if(ope=='open'){
        warm_content="是否要启用选中的数据";
    }else if(ope=='close'){
        warm_content="是否要禁用选中的数据";
    }else if(ope=='delete'){
         warm_content="是否要删除选中的数据";
    }else if(ope=='bf'){
        warm_content="是否要报废选中的数据";
    }else{
        alertMsg(4,'您ope配置有误');
         return false;
    }
    
    var layer = layui.layer;
    layer.confirm(warm_content, function(index){


    var url =obj.attr('url');
    if(url=="" || typeof(url)=="undefined"){
        alertMsg(4,'您未配置url');
         return false;
    }
    var model =obj.attr('model');
    if(model=="" || typeof(model)=="undefined"){
        alertMsg(4,'您未配置model');
         return false;
    }

    console.log("ch:"+thechoose);
    if(thechoose==""){
        alertMsg(5,'暂无选择的数据');
        return false;
    }
    var para ="model="+model+"&ope="+ope+"&id="+thechoose;
    $.ajax({
            type: "POST",
            url: url,
            data: para,
            dataType: 'json',
            timeout: 5000,
            success: function(rs){
                if(rs.code==1){
                    var thehref=window.location.href;
                   // console.log(thehref);
                    alertMsg(1,'操作成功',rs.url);
                   // window.location
                    thechoose="";
                }else{
                    alertMsg(0,rs.msg);
                }
            },
            error: function(xhr, type){
                    alertMsg(5,'对不起，网络开小差了!');
            }
    });
            })
     })
       
        
}
   

/*
 * 
 */
function alertMsg(code,msg,url){
        
        //这里写code对应icon
        if(code==1){
            icon=1;
        }else if(code==0){
            icon=2;
        }else if(code==5){
            icon=5;
        }else{
            icon=-1;
        }
        layui.use('layer', function(){
            var layer = layui.layer;
         
            layer.open({
            'type':0,
            'title':'消息窗口',
            'content':msg,
            'icon':icon,
            time: 2000,
            shade: [0.8, '#333333'],
            end: function(){
              //do something
              if(url!='' && typeof(url)!="undefined"){
                  window.location.href=url;
              }
            }
            });
        });   

}

;$(function(){

//搜索功能
$("#search").click(function(){
    var url = $(this).attr('url');
    var query  = $('.search-form').find('input,select').serialize();
    query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g,'');
    query = query.replace(/^&/g,'');
    if( url.indexOf('?')>0 ){
        url += '&' + query;
    }else{
        url += '?' + query;
    }
    console.log(url);
            window.location.href = url;
});

//全选的实现
$(".check-all").click(function(){
        $(".ids").prop("checked", this.checked);
});
$(".ids").click(function(){
        var option = $(".ids");
        option.each(function(i){
                if(!this.checked){
                        $(".check-all").prop("checked", false);
                        return false;
                }else{
                        $(".check-all").prop("checked", true);
                }
        });
});

//ajax get请求
$('.ajax-get').click(function(){
    console.log(111);
    var target;
    var that = this;
    if ( $(this).hasClass('confirm') ) {
        if(!confirm('确认要执行该操作吗？')){
            return false;
        }
    }
    if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
        $.get(target).success(function(data){
            alertMsg(data.code,data.msg,data.url);
            $(that).removeClass('disabled').prop('disabled',false);
        });

    }
    return false;
});

//ajax post submit请求
$('.ajax-post').click(function(){
    var target,query,form;
    var target_form = $(this).attr('target-form');
    var that = this;
    var nead_confirm=false;
    if( ($(this).attr('type')=='button') || (target = $(this).attr('href')) || (target = $(this).attr('url')) ){
        form = $('.'+target_form);
        if ($(this).attr('hide-data') === 'true'){//无数据时也可以使用的功能
            form = $('.hide-data');
            query = form.serialize();
        }else if (form.get(0)==undefined){
            return false;
        }else if ( form.get(0).nodeName=='FORM' ){
            if ( $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            if($(this).attr('url') !== undefined){
                    target = $(this).attr('url');
            }else{
                    target = form.get(0).action;
            }
            query = form.serialize();
        }else if( form.get(0).nodeName=='INPUT' || form.get(0).nodeName=='SELECT' || form.get(0).nodeName=='TEXTAREA') {
            form.each(function(k,v){
                if(v.type=='checkbox' && v.checked==true){
                    nead_confirm = true;
                }
            })
            if ( nead_confirm && $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.serialize();
        }else{
            if ( $(this).hasClass('confirm') ) {
                if(!confirm('确认要执行该操作吗?')){
                    return false;
                }
            }
            query = form.find('input,select,textarea').serialize();
        }
        loading(1);
        $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
        $.post(target,query).success(function(data){
            alertMsg(data.code,data.msg,data.url);
            console.log(data);
            $(that).removeClass('disabled').prop('disabled',false);
            loading(2);
        });
    }else{}
    return false;
});

    // 独立域表单获取焦点样式
    $(".text").focus(function(){
        $(this).addClass("focus");
    }).blur(function(){
        $(this).removeClass('focus');
    });
    $("textarea").focus(function(){
        $(this).closest(".textarea").addClass("focus");
    }).blur(function(){
        $(this).closest(".textarea").removeClass("focus");
    });
});



//导航高亮
/*@param string target  第一个值是要执行active的id
 * @param string target2 第二个值是要执行active open 可不填
 */
function highlight_subnav(fatarget,target,target2){
      $("#"+fatarget).addClass('layui-this');
     
      $(".menu_switch").hide();
      $("#se_"+fatarget).show();
      $("#"+target).addClass('layui-this');
      $("#"+target2).addClass('layui-nav-itemed');
      
      
//      $("#leaders").children().remove();
//      var lst='';
//     // var homeurl ="<?php echo url('Index/index'); ?>";
//      if(target2){
//          
//          //初始化leader条
//          var first =$("#"+target2).children().find('.menu-text').html();
//          var first_url =$("#"+target2).children("a:nth-child(1)").attr('href');
//          $("#leaders").append("<li><i class='icon-home menu-ri-home'></i> <a href='/Index/index' >首页</a></li><li><a href='"+first_url+"'>"+first+"</a></li>");
//          var second =$("#"+target).children().find('span').html();
//          var second_url =$("#"+target).children("a:nth-child(1)").attr('href');
//          $("#leaders").append("<li><a href='"+second_url+"'>"+second+"</a></li>");
//          lst=second;
//       //   var three =$(".page-header").children("h1").html();
//        //  $("#leaders").append("<li><a class='active' href='#'>"+three+"</a></li>");
//          
//      }else{
//          //初始化leader条
//          var first =$("#"+target).children().find('.menu-text').html();
//          var first_url =$("#"+target).children("a:nth-child(1)").attr('href');
//          if(first!='首页')
//            $("#leaders").append("<li><i class='icon-home menu-ri-home'></i> <a href='/Index/index' >首页</a></li><li><a href='"+first_url+"'>"+first+"</a></li>");
//          else
//              $("#leaders").append("<li><i class='icon-home  menu-ri-home'></i> <a href='"+first_url+"'>"+first+"</a></li>");
//          var three =$(".page-header").children("h1").html();
//          $("#leaders").append("<li><a class='active' href='#'>"+three+"</a></li>");
//          lst=three;
//      }
   //   $(".page-header").children("h1").html(lst);
      
      
}

function getApiData(url, para, callback){
	$.ajax({
		type: "POST",
		url: url,
		data: para,
		dataType: 'json',
		timeout: 5000,
		success: function(rs){

                        callback(rs);
			
		},
		error: function(xhr, type){
			alert('对不起，网络开小差了!');
		}
	});
}



    
/*上传加载显示隐藏
 * 
 */    
var index;
function loading(type){
    layui.use('layer', function(){
        var layer = layui.layer;
    
            if(type==1){
                layer.open({
                'type':3,
                'title':'消息窗口',
              //  'content':'6666',
                'icon':1,
              //  time: 2000,
                shade: [0.3, '#333333'],
                });
              //  layer.load(2);
              //  setTimeout("layer.close(layer.index)",5000)
            }else{
               layer.closeAll('loading'); 
            }
    })
    
}

//loading(2);
/*uploadify 封装
 * 
 */
function doUploadFile(file_upload,target_save,upload_url,is_disable,fileQueue,limit,ext){
    
    $('#'+file_upload).uploadify({

        'swf'      : '/dist/uploadify.swf',
        'uploader' : 'uploadify.php',
        'queueID': fileQueue,  
        'buttonText':'上传',
        'uploader': upload_url,
        'multi':false,
        'fileObjName':'file',
        'fileSizeLimit': limit,
        'overrideEvents':['onDialogClose'],
        'progressData':'speed',
        'fileTypeExts':ext,
      //  'buttonClass':'upfile-btn btn btn-purple',
        'auto': true,
        'onSWFReady' : function() {
            if(is_disable==1){
                $('#'+file_upload).uploadify('disable', true);
            }
        },
        'onSelectError': function (file, errorCode, errorMsg) {  
            switch (errorCode) {  
                case -100:  
                    alertMsg(0,"上传的文件数量已经超出系统限制的" + $('#'+file_upload).uploadify('settings', 'uploadLimit') + "个文件！");  
                    return;
                    break;  
                case -110:  
                    alertMsg(0,"文件 [" + file.name + "] 大小超出系统限制的" + $('#'+file_upload).uploadify('settings', 'fileSizeLimit') + "大小！");  
                    return;
                    break;  
                case -120:  
                    alertMsg(0,"文件 [" + file.name + "] 大小异常！");  
                    return;
                    break;  
                case -130:  
                    alertMsg(0,"文件 [" + file.name + "] 类型不正确！");  
                    return;
                    break;  
            } 
           // console.log(errorCode);
          //  alert(errorCode);  
        },
        'onUploadSuccess':function (file, rs, response) {
            rs =JSON.parse(rs);
         //   console.log(rs);
            if(rs.code!=1){
                alertMsg(0,file.name+rs.msg);
            }else{
                $('#'+file_upload).uploadify('disable', true);
                if(rs.data.object_type==1){
                    object_str  = '<div class="pic-upload"><span class="pic-upload-close btn btn-danger btn-sm" onclick="delImgByupload(\''+file_upload+'\',\''+target_save+'\',\''+rs.data.save_url+'\')"><i class="ace-icon fa fa-trash-o bigger-110"></i></span><a class="fancybox-piclayer" href="'+rs.data.show_url+'"><img src="'+rs.data.show_url+'"></a></div>';
                }else{
                    object_str= '<div class="pic-upload"><span class="pic-upload-close btn btn-danger btn-sm" onclick="delImgByupload(\''+file_upload+'\',\''+target_save+'\',\''+rs.data.save_url+'\')"><i class="ace-icon fa fa-trash-o bigger-110"></i></span><a target="_blank" href="'+rs.data.show_url+'"><img src="/img/files.jpg"></a></div>';
                }
                $('#'+file_upload).next().append(object_str);
                var old =$("#"+target_save).val();
                if(old!='')
                    old=old+','+rs.data.save_url;
                else 
                    old =rs.data.save_url;
                $("#"+target_save).val(old);
                        $(".fancybox-piclayer").fancybox({
                                openEffect  : 'none',
                                closeEffect	: 'none',
                                helpers : {
                                        title : {
                                                type : 'over'
                                        }
                                }
                        });
                
                
             }
        }
        
    });    
}

//判断空
function checkEmpty(str){
    if(str=='' || typeof(str)=='undefined'){
        return true;
    }
    return false;
}


