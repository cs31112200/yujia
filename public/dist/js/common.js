//新增、删除表格
tableDataEls();
function tableDataEls(){
	$("td").delegate(".table-data-add","click",function(){
	  var strAddHtml = '<tr>'
				+'<td><input type="text" name="account" class="model-table-input col-xs-12 col-sm-11" value="经度" size="40"></td>'
				+'<td><input type="text" name="account" class="model-table-input col-xs-12 col-sm-11" value="56" size="40"></td>'
				+'<td>'
				+'<a href="javascript:;" class="btn btn-link table-data-del">'
				+'<i class="ace-icon fa fa-minus-circle bigger-120 red align-middle"></i>删除'
				+'</a>'
				+'</td>'
				+'</tr>';
	  $(this).parent().parent().parent().prev().append(strAddHtml);
	  $("td").delegate(".table-data-del","click",function(){	  
		  $(this).parent().parent().remove();
		});
	});
	$("td").delegate(".table-data-del","click",function(){	  
	  $(this).parent().parent().remove();
	});
}



function uploadEls(obj){	
	$(obj).siblings().click();
        
}
$(function(){
	$(".pic-upload").delegate(".pic-upload-close","click",function(){
	  $(this).parent().remove();
	});
	$(".pic-upload").delegate("a","click",function(){
		$(".fancybox-piclayer").fancybox({
			openEffect  : 'none',
			closeEffect	: 'none',
			helpers : {
				title : {
					type : 'over'
				}
			}
		});
	});

});
//上传图片函数增加结束--小谢0113

/*删除图片
 * 
 */
    function delImg(save_target,target){

        var targets =$("#"+save_target).val();
        var thearr =targets.split(',');
        var newarr =new Array();
        k=0;
        for(i=0;i<thearr.length;i++){
            if(thearr[i]!=target){
                newarr[k]=thearr[i];
                k++;
            }
        };
       var str =newarr.join(',');
       $("#"+save_target).val(str);
    }
    
    function delImgByupload(file_upload,save_target,target){
	$(".pic-upload").delegate(".pic-upload-close","click",function(){
	  $(this).parent().remove();
	});
        var targets =$("#"+save_target).val();
        var thearr =targets.split(',');
        var newarr =new Array();
        k=0;
        for(i=0;i<thearr.length;i++){
            if(thearr[i]!=target){
                newarr[k]=thearr[i];
                k++;
            }
        };
       var str =newarr.join(',');
       $("#"+save_target).val(str);
       $('#'+file_upload).uploadify('disable', false);
    }


//上传图片
function myUploadImg(obj){
    var url,query,form,is_mut,testvalue;
        save_string='';
    is_mut =$(obj).attr('is_mut');
    //赋值本身
    var tself =$(obj);
    url = $(obj).attr('url');
    save_target = $(obj).attr('target-id');
    var old =$("#"+save_target).val();

    if(!url){
        alertMsg(0,'没有设置URL');
        return;
    };
var formData = new FormData();
/*判断是否9张*/
if(is_mut==2 || is_mut==4){
    var upload_num_now =$(obj)[0].files.length;
    check_arr =old.split(',');
  //  console.log(old);return;
    var old_num =check_arr.length;
    if(upload_num_now+old_num>9){
        alertMsg(0,'图片最多设置9张');return;
    }
    
}
for(i=0;i<$(obj)[0].files.length;i++){
    formData.append('file[]', $(obj)[0].files[i]);
}
    
$.ajax({
    url: url,
    type: 'POST',
    cache: false,
    data: formData,
    processData: false,
    contentType: false
}).done(function(rs) {
    alertMsg(rs.code,rs.msg);
    if(rs.code==1){
	var length = rs.data.length;
	var strHtml="";
        var save_url =new Array();
	for(var i=0;i<length;i++){
		strHtml += '<div class="pic-upload"><span class="pic-upload-close btn btn-danger btn-sm" onclick="delImg(\''+save_target+'\',\''+rs.data[i]['save_url']+'\')"><i class="ace-icon fa fa-trash-o bigger-110"></i></span><a class="fancybox-piclayer" href="'+rs.data[i]['show_url']+'"><img src="'+rs.data[i]['show_url']+'"></a></div>';
                save_url[i]=rs.data[i]['save_url'];
	};
        save_string =save_url.join(',');
        
        //赋值
        if(old!='')
            old=old+','+save_string;
        else 
            old =save_string;
        $("#"+save_target).val(old);
        if(is_mut!=3 &&is_mut!=1){
	$(strHtml).insertAfter(tself);
        
        }else{
         
          $("#"+save_target).parent().find('.pic-upload').remove();
            $("#"+save_target).val(save_string);
            $(strHtml).insertAfter(tself);
        }
        
        console.log( $("#"+save_target).val());
	$(".pic-upload").delegate(".pic-upload-close","click",function(){
	  $(this).parent().remove();
	});
	$(".pic-upload").delegate("a","click",function(){
           
		$(".fancybox-piclayer").fancybox({
			openEffect  : 'none',
			closeEffect	: 'none',
			helpers : {
				title : {
					type : 'over'
				}
			}
		});
	});
    
}
    
}).fail(function(rs) {
    alertMsg(rs.code,rs.msg);
});
    
};







/*上传图片 
 * @param int is_mut 1为添加单张，2为添加多张，3为修改单张，4为修改多张
 */


;$(function(){
/*图片放大
 * 
 */    
$(".fancybox-piclayer").fancybox({
        openEffect  : 'none',
        closeEffect	: 'none',
        helpers : {
                title : {
                        type : 'over'
                }
        }
});
    
    
//上传图片
$(".upload_img").change(function(){
    var url,query,form,is_mut;
    is_mut =$(this).attr('is_mut');
    //赋值本身
    var tself =$(this);
    url = $(this).attr('url');
    save_target = $(this).attr('target-id');
    var old =$("#"+save_target).val();
    
    
    if(!url){
        alertMsg(0,'没有设置URL');
        return;
    };
var formData = new FormData();

/*判断是否9张*/
if(is_mut==2 || is_mut==4){
    var upload_num_now =$(this)[0].files.length;
    check_arr =old.split(',');
    var old_num =check_arr.length;
    if(upload_num_now+old_num>9){
        alertMsg(0,'图片最多设置9张');return;
    }
    
}
for(i=0;i<$(this)[0].files.length;i++){
    formData.append('file[]', $(this)[0].files[i]);
}
    
$.ajax({
    url: url,
    type: 'POST',
    cache: false,
    data: formData,
    processData: false,
    contentType: false,
    beforeSend:function(){
        loading(1);
    },
    complete:function(){
        loading(2);
    }
}).done(function(rs) {
    alertMsg(rs.code,rs.msg);
      loading(2);
    if(rs.code==1){
    
	var length = rs.data.length;
	var strHtml=object_str="";
        var save_url =new Array();
	for(var i=0;i<length;i++){
                if(rs.data[i]['object_type']==1){
                    object_str  = '<div class="pic-upload"><span class="pic-upload-close btn btn-danger btn-sm" onclick="delImg(\''+save_target+'\',\''+rs.data[i]['save_url']+'\')"><i class="ace-icon fa fa-trash-o bigger-110"></i></span><a class="fancybox-piclayer" href="'+rs.data[i]['show_url']+'"><img src="'+rs.data[i]['show_url']+'"></a></div>';
                }else{
                    object_str= '<div class="pic-upload"><a target="_blank" href="'+rs.data[i]['show_url']+'">'+rs.data[i]['file_name']+'</a></div>';
                }
                strHtml+=object_str;
                save_url[i]=rs.data[i]['save_url'];
	};
        save_string =save_url.join(',');
        
        //赋值
        if(old!='')
            old=old+','+save_string;
        else 
            old =save_string;
        $("#"+save_target).val(old);
	//console.log(resultObj);
        if(is_mut!=3 && is_mut!=1){
	$(strHtml).insertAfter(tself);
        }else{
           // $(".pic-upload").remove();
           $("#"+save_target).siblings().find('.pic-upload').remove();
            $("#"+save_target).val(save_string);
            $(strHtml).insertAfter(tself);
        }
	$(".pic-upload").delegate(".pic-upload-close","click",function(){
	  $(this).parent().remove();
	});
	$(".pic-upload").delegate("a","click",function(){
		$(".fancybox-piclayer").fancybox({
			openEffect  : 'none',
			closeEffect	: 'none',
			helpers : {
				title : {
					type : 'over'
				}
			}
		});
	});
    }
    
    
}).fail(function(rs) {
    alertMsg(rs.code,rs.msg);
});
})
//上传文件
$(".upload_file").change(function(){
    var url,query,form,is_mut;
    is_mut =$(this).attr('is_mut');
    //赋值本身
    var tself =$(this);
    url = $(this).attr('url');
    save_target = $(this).attr('target-id');
    var old =$("#"+save_target).val();
    
    
    if(!url){
        alertMsg(0,'没有设置URL');
        return;
    };
var formData = new FormData();

for(i=0;i<$(this)[0].files.length;i++){
    formData.append('file[]', $(this)[0].files[i]);
}
    
$.ajax({
    url: url,
    type: 'POST',
    cache: false,
    data: formData,
    processData: false,
    contentType: false,
    beforeSend:function(){
        loading(1);
    },
    complete:function(){
        loading(2);
    }
}).done(function(rs) {
    alertMsg(rs.code,rs.msg);
    loading(2);
    if(rs.code==1){
    
	var length = rs.data.length;
	var strHtml="";
        var save_url =new Array();
	for(var i=0;i<length;i++){
                if(rs.data[i]['object_type']==1){
                    object_str  = '<div class="pic-upload"><span class="pic-upload-close btn btn-danger btn-sm" onclick="delImg(\''+save_target+'\',\''+rs.data[i]['save_url']+'\')"><i class="ace-icon fa fa-trash-o bigger-110"></i></span><a class="fancybox-piclayer" href="'+rs.data[i]['show_url']+'"><img src="'+rs.data[i]['show_url']+'"></a></div>';
                }else{
                    object_str= '<div class="pic-upload"><a target="_blank" href="'+rs.data[i]['show_url']+'">'+rs.data[i]['file_name']+'</a></div>';
                }
                strHtml+=object_str;
                save_url[i]=rs.data[i]['save_url'];
	};
        save_string =save_url.join(',');
        
        //赋值
        if(old!='')
            old=old+','+save_string;
        else 
            old =save_string;
        $("#"+save_target).val(old);
	//console.log(resultObj);
        if(is_mut!=3 && is_mut!=1){
	$(strHtml).insertAfter(tself);
        }else{
           // $(".pic-upload").remove();
           $("#"+save_target).siblings().find('.pic-upload').remove();
            $("#"+save_target).val(save_string);
            $(strHtml).insertAfter(tself);
        }
	$(".pic-upload").delegate(".pic-upload-close","click",function(){
	  $(this).parent().remove();
	});
	$(".pic-upload").delegate("a","click",function(){
		$(".fancybox-piclayer").fancybox({
			openEffect  : 'none',
			closeEffect	: 'none',
			helpers : {
				title : {
					type : 'over'
				}
			}
		});
	});
    }
    
    
}).fail(function(rs) {
    alertMsg(rs.code,rs.msg);
});
})


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
        var target;
        var that = this;
        if ( $(this).hasClass('confirm') ) {
            if(!confirm('确认要执行该操作吗?')){
                return false;
            }
        }
        if ( (target = $(this).attr('href')) || (target = $(this).attr('url')) ) {
            $.get(target).success(function(data){
                alertMsg(data.code,data.msg,data.url);
                $(that).removeClass('disabled').prop('disabled',false);
             /*   if (data.status==1) {
                    if (data.url) {
                        alertMsg(data.info + ' 页面即将自动跳转~','alert-success');
                    }else{
                        updateAlert(data.info,'alert-success');
                    }
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else if( $(that).hasClass('no-refresh')){
                            $('#top-alert').find('button').click();
                        }else{
                            location.reload();
                        }
                    },1500);
                }else{
                    updateAlert(data.info);
                    setTimeout(function(){
                        if (data.url) {
                            location.href=data.url;
                        }else{
                            $('#top-alert').find('button').click();
                        }
                    },1500);
                }*/
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
            $(that).addClass('disabled').attr('autocomplete','off').prop('disabled',true);
            $.post(target,query).success(function(data){
                alertMsg(data.code,data.msg,data.url);
                console.log(data);
                $(that).removeClass('disabled').prop('disabled',false);
//                if (data.code==1) {
//                    alertMsg(data.code,data.msg,data.url);
//                    setTimeout(function(){
//                        if (data.url) {
//                            location.href=data.url;
//                        }else if( $(that).hasClass('no-refresh')){
//                            $('#top-alert').find('button').click();
//                            $(that).removeClass('disabled').prop('disabled',false);
//                        }else{
//                            location.reload();
//                        }
//                    },1500);
//                }else{
//                    updateAlert(data.info);
//                    setTimeout(function(){
//                        if (data.url) {
//                            location.href=data.url;
//                        }else{
//                            $('#top-alert').find('button').click();
//                            $(that).removeClass('disabled').prop('disabled',false);
//                        }
//                    },1500);
//                }
            });
        }else{}
        return false;
    });

	/**顶部警告栏*/
	var content = $('#main');
	var top_alert = $('#top-alert');
	top_alert.find('.close').on('click', function () {
		top_alert.removeClass('block').slideUp(200);
		// content.animate({paddingTop:'-=55'},200);
	});

    window.updateAlert = function (text,c) {
		text = text||'default';
		c = c||false;
		if ( text!='default' ) {
            top_alert.find('.alert-content').text(text);
			if (top_alert.hasClass('block')) {
			} else {
				top_alert.addClass('block').slideDown(200);
				// content.animate({paddingTop:'+=55'},200);
			}
		} else {
			if (top_alert.hasClass('block')) {
				top_alert.removeClass('block').slideUp(200);
				// content.animate({paddingTop:'-=55'},200);
			}
		}
		if ( c!=false ) {
            top_alert.removeClass('alert-error alert-warn alert-info alert-success').addClass(c);
		}
	};

    //按钮组
    (function(){
        //按钮组(鼠标悬浮显示)
        $(".btn-group").mouseenter(function(){
            var userMenu = $(this).children(".dropdown ");
            var icon = $(this).find(".btn i");
            icon.addClass("btn-arrowup").removeClass("btn-arrowdown");
            userMenu.show();
            clearTimeout(userMenu.data("timeout"));
        }).mouseleave(function(){
            var userMenu = $(this).children(".dropdown");
            var icon = $(this).find(".btn i");
            icon.removeClass("btn-arrowup").addClass("btn-arrowdown");
            userMenu.data("timeout") && clearTimeout(userMenu.data("timeout"));
            userMenu.data("timeout", setTimeout(function(){userMenu.hide()}, 100));
        });

        //按钮组(鼠标点击显示)
        // $(".btn-group-click .btn").click(function(){
        //     var userMenu = $(this).next(".dropdown ");
        //     var icon = $(this).find("i");
        //     icon.toggleClass("btn-arrowup");
        //     userMenu.toggleClass("block");
        // });
        $(".btn-group-click .btn").click(function(e){
            if ($(this).next(".dropdown").is(":hidden")) {
                $(this).next(".dropdown").show();
                $(this).find("i").addClass("btn-arrowup");
                e.stopPropagation();
            }else{
                $(this).find("i").removeClass("btn-arrowup");
            }
        })
        $(".dropdown").click(function(e) {
            e.stopPropagation();
        });
        $(document).click(function() {
            $(".dropdown").hide();
            $(".btn-group-click .btn").find("i").removeClass("btn-arrowup");
        });
    })();

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

/* 上传图片预览弹出层 */
$(function(){
    $(window).resize(function(){
        var winW = $(window).width();
        var winH = $(window).height();
        $(".upload-img-box").click(function(){
        	//如果没有图片则不显示
        	if($(this).find('img').attr('src') === undefined){
        		return false;
        	}
            // 创建弹出框以及获取弹出图片
            var imgPopup = "<div id=\"uploadPop\" class=\"upload-img-popup\"></div>"
            var imgItem = $(this).find(".upload-pre-item").html();

            //如果弹出层存在，则不能再弹出
            var popupLen = $(".upload-img-popup").length;
            if( popupLen < 1 ) {
                $(imgPopup).appendTo("body");
                $(".upload-img-popup").html(
                    imgItem + "<a class=\"close-pop\" href=\"javascript:;\" title=\"关闭\"></a>"
                );
            }

            // 弹出层定位
            var uploadImg = $("#uploadPop").find("img");
            var popW = uploadImg.width();
            var popH = uploadImg.height();
            var left = (winW -popW)/2;
            var top = (winH - popH)/2 + 50;
            $(".upload-img-popup").css({
                "max-width" : winW * 0.9,
                "left": left,
                "top": top
            });
        });

        // 关闭弹出层
        $("body").on("click", "#uploadPop .close-pop", function(){
            $(this).parent().remove();
        });
    }).resize();

    // 缩放图片
    function resizeImg(node,isSmall){
        if(!isSmall){
            $(node).height($(node).height()*1.2);
        } else {
            $(node).height($(node).height()*0.8);
        }
    }
})

//标签页切换(无下一步)
function showTab() {
    $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();
}

//标签页切换(有下一步)
function nextTab() {
     $(".tab-nav li").click(function(){
        var self = $(this), target = self.data("tab");
        self.addClass("current").siblings(".current").removeClass("current");
        window.location.hash = "#" + target.substr(3);
        $(".tab-pane.in").removeClass("in");
        $("." + target).addClass("in");
        showBtn();
    }).filter("[data-tab=tab" + window.location.hash.substr(1) + "]").click();

    $("#submit-next").click(function(){
        $(".tab-nav li.current").next().click();
        showBtn();
    });
}

// 下一步按钮切换
function showBtn() {
    var lastTabItem = $(".tab-nav li:last");
    if( lastTabItem.hasClass("current") ) {
        $("#submit").removeClass("hidden");
        $("#submit-next").addClass("hidden");
    } else {
        $("#submit").addClass("hidden");
        $("#submit-next").removeClass("hidden");
    }
}

//导航高亮
/*@param string target  第一个值是要执行active的id
 * @param string target2 第二个值是要执行active open 可不填
 */
function highlight_subnav(target,target2){
      $("#"+target).addClass('active');
      $("#"+target2).addClass('active open');
      
      $("#leaders").children().remove();
      var lst='';
     // var homeurl ="<?php echo url('Index/index'); ?>";
      if(target2){
          
          //初始化leader条
          var first =$("#"+target2).children().find('.menu-text').html();
          var first_url =$("#"+target2).children("a:nth-child(1)").attr('href');
          $("#leaders").append("<li><i class='icon-home menu-ri-home'></i> <a href='/Index/index' >首页</a></li><li><a href='"+first_url+"'>"+first+"</a></li>");
          var second =$("#"+target).children().find('span').html();
          var second_url =$("#"+target).children("a:nth-child(1)").attr('href');
          $("#leaders").append("<li><a href='"+second_url+"'>"+second+"</a></li>");
          lst=second;
       //   var three =$(".page-header").children("h1").html();
        //  $("#leaders").append("<li><a class='active' href='#'>"+three+"</a></li>");
          
      }else{
          //初始化leader条
          var first =$("#"+target).children().find('.menu-text').html();
          var first_url =$("#"+target).children("a:nth-child(1)").attr('href');
          if(first!='首页')
            $("#leaders").append("<li><i class='icon-home menu-ri-home'></i> <a href='/Index/index' >首页</a></li><li><a href='"+first_url+"'>"+first+"</a></li>");
          else
              $("#leaders").append("<li><i class='icon-home  menu-ri-home'></i> <a href='"+first_url+"'>"+first+"</a></li>");
          var three =$(".page-header").children("h1").html();
          $("#leaders").append("<li><a class='active' href='#'>"+three+"</a></li>");
          lst=three;
      }
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



/*弹出框2
 * 
 */
    function alertMsg(code,msg,url){
        alert(msg);
        setTimeout(function(){
                if(url){
                    window.location = url;
                }
        }, 1500);
        return;
		var dialog = bootbox.dialog({
			message: msg,
			closeButton: true,						
		});
		setTimeout(function(){
			dialog.modal('hide');
                        if(url){
                            window.location = url;
                        }
		}, 1500);
    }
    
    
/*上传加载显示隐藏
 * 
 */    
function loading(type){
    if(type==1){
        $("#loading").show();
    }else{
         $("#loading").hide();
    }
    
}

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


function showHide(){
    $(".chargebox").toggle();
    if($('.chargebox').css('display')=='block'){
        $('.chargeboxhide').find('img').attr('src','/img/showback.png');
        $('.chargeboxhide').find('span').html("<br>关<br>闭<br>审<br>核<br>结<br>果");
        
    }else{
        $('.chargeboxhide').find('img').attr('src','/img/show.png');
         $('.chargeboxhide').find('span').html("<br>查<br>看<br>审<br>核<br>结<br>果");
    }
}
