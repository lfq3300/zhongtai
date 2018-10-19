$(function(){
	autoLeftNav();
	$(window).resize(function() {
        autoLeftNav();
    });
})

// 侧边菜单
$('.sidebar-nav-sub-title').on('click', function() {
	$(this).toggleClass("active");
    $(this).siblings('.sidebar-nav-sub').slideToggle(80).end().find('.sidebar-nav-sub-ico')
    .toggleClass('sidebar-nav-sub-ico-rotate');
});
/*
	form表单提交
*/
var load_text = ", 页面即将跳转";
$(".ajax-post").click(function(){
	 var options = {
	 	 beforeSubmit: function(formData){
	 	 	$('.ajax-post').addClass('am-disabled');
	 	 },
	 	 success:function(ret){
	 	 	if(ret.status == 1){
	 	 		if(ret.url){
	 	 			toast.success(ret.info+load_text);
	 	 		}else{
	 	 			toast.success(ret.info);
	 	 		}
	 	 	}else{
	 	 		if(ret.url){
	 	 			toast.error(ret.info+load_text);
	 	 		}else{
	 	 			toast.error(ret.info);
	 	 		}
	 	 	}
	 		setTimeout(function() {
				if(ret.url) {
					location.href = ret.url;
				} else {
					$('.ajax-post').removeClass('am-disabled');
				}
			}, 1500);	 	 	
	 	 },
	 };
	 $(this).parents('form').ajaxSubmit(options); 
	 return false;
})


//全选
$("#builderAllCheckbox").click(function(){
	if($(this).is(":checked")){
		$(".checkbox").find(".ids").prop("checked",true);
	}else{
		$(".checkbox").find(".ids").prop("checked",false);
	}
})


//删除按钮操作
$(".delete-ajax-post").click(function(){
    var that = $(this);
    var target, query, form;
    var target_form = $(this).attr('target-form');
    form = $('.' + target_form);
    query = form.serialize();
    if(!query){
        toast.error("请勾选操作对象");
        return;
    }
    if(!confirm("删除行为不可逆,是否继续删除")){
        return false;
    }
    $(that).addClass('am-disabled')
    target = $(this).attr("href");
    $.post(target,query).success(function(ret){
        if(ret.status == 1){
            if(ret.url){
                toast.success(ret.info+load_text);
            }else{
                toast.success(ret.info);
            }
        }else{
            toast.error(ret.info);
        }
        setTimeout(function() {
            if(ret.url) {
                location.href = ret.url;
            } else {
                $(that).removeClass('am-disabled');
            }
        }, 1500);
    })
})



//一建操作按钮操作
$(".verify-ajax-post").click(function(){
    var that = $(this);
    var target, query, form;
    var target_form = $(this).attr('target-form');
    form = $('.' + target_form);
    query = form.serialize();
    if(!query){
        toast.error("请勾选操作对象");
        return;
    }
    $(that).addClass('am-disabled')
    target = $(this).attr("href");
    $.post(target,query).success(function(ret){
        if(ret.status == 1){
            if(ret.url){
                toast.success(ret.info+load_text);
            }else{
                toast.success(ret.info);
            }
        }else{
            toast.error(ret.info);
        }
        setTimeout(function() {
            if(ret.url) {
                location.href = ret.url;
            } else {
                $(that).removeClass('am-disabled');
            }
        }, 1500);
    })
});


function autoLeftNav() {
    $('.tpl-header-switch-button').on('click', function() {
        if ($('.left-sidebar').is('.active')) {
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').removeClass('active');
            }
            $('.left-sidebar').removeClass('active');
        } else {
            $('.left-sidebar').addClass('active');
            if ($(window).width() > 1024) {
                $('.tpl-content-wrapper').addClass('active');
            }
        }
    })
    if ($(window).width() < 1024) {
        $('.left-sidebar').addClass('active');
    } else {
        $('.left-sidebar').removeClass('active');
    }
}