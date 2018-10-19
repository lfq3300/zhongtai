<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta http-equiv="Access-Control-Allow-Origin" content="*/*">
	<title>Admin---<?php echo ($title); ?></title>
    <link href="/Application/Admin/Static/css/toast.css" rel="stylesheet" type="text/css"/>
    <link href="/Application/Admin/Static/css/animate.css" rel="stylesheet" type="text/css"/>
	<link href="/Application/Admin/Static/css/amazeui.min.css" rel="stylesheet" type="text/css"/>
	<link href="/Application/Admin/Static/css/amazeui.reset.css" rel="stylesheet" type="text/css"/>
	<link href="/Application/Admin/Static/css/app.css" rel="stylesheet" type="text/css"/>
    <script src="/Application/Admin/Static/js/jquery.min.js" type="text/javascript" ></script>
    <script src="/Application/Admin/Static/js/jquery.form.min.js" type="text/javascript" ></script>
    <script src="/Application/Admin/Static/js/webuploader.js" type="text/javascript" ></script>
    <script src="/Application/Admin/Static/js/amazeui.min.js" type="text/javascript" ></script>
    <script type="text/javascript" src="/Public/plugs/ueditor/ueditor.config.js"></script>
    <script type="text/javascript" src="/Public/plugs/ueditor/ueditor.all.min.js"></script>
    <script type="text/javascript" src="/Public/plugs/ueditor/zh-cn.js"></script>
</head>
<body>
    <?php $account = cookieDecrypt($_COOKIE['account']); $level = cookieDecrypt($_COOKIE['level']); $root_level = C(ROOT_LEVEL); ?>
	<div class="am-g">
		<!--头部-->
        <div style="height: 57px;width: 100%;"></div>
		<div class="header-box">
            <header>
                <div class="am-fl tpl-header-logo">
                    <a href="<?php echo U('Admin/admin');?>">
                        <!--<img src="/Application/Admin/Static/img/logo.png" alt="">-->
                    </a>
                </div>
                <div class="tpl-header-fluid">
                    <div class="am-fl tpl-header-switch-button am-icon-list">
                        <span></span>
                        </div>
                        <div class="am-fl tpl-header-navbar tpl-header-navbar-admin">
                        <?php if(($level) == $root_level): ?><ul >
                                <li class="am-text-sm am-dropdown tpl-dropdown" data-am-dropdown>
                                    <a href="javascript:void(0);" class="am-dropdown-toggle tpl-dropdown-toggle" data-am-dropdown-toggle>后台设置 <i class="am-icon-chevron-down"></i></a>
                                    <div class="am-dropdown-content tpl-dropdown-content am-g admin">
                                        <ul class="am-u-sm-4">
                                            <li>
                                                <a>Rbac设置</a>
                                            </li>
                                            <li>
                                                <a href="<?php echo U('Rbac/index');?>">组权限设置</a>
                                            </li>
                                            <li>
                                                <a href="<?php echo U('Rbac/node');?>">菜单设置</a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul><?php endif; ?>
                    </div>

                    <div class="am-fr tpl-header-navbar">
                        <ul>
                            <li class="am-text-sm tpl-header-navbar-welcome">

                                <a href="javascript:void(0);">欢迎你, <span><?php echo ($account); ?></span> </a>
                            </li>
                            <li class="am-text-sm"  onclick="exitlogin()" >
                                <a href="javascript:void(0);">
                                    <span class="am-icon-sign-out"></span> 退出
                                </a>
                            </li>
                            <li class="am-text-sm">
                                <a  href="<?php echo U('Admin/setMyPwd');?>">
                                    <span class="am-icon-tree"></span> 修改密码
                                </a>
                            </li>
                            <?php if(($level) == "-100000000"): ?><li class="am-text-sm" >
                                    <a  href="/cc.php" target="_blank">
                                        <span class="am-icon-birthday-cake"> 清空缓存</span>
                                    </a>
                                </li><?php endif; ?>
                        </ul>
                        <script type="text/javascript">
                            function exitlogin(){
                              window.location.href ="<?php echo U('Admin/Index/index');?>";
                            }
                        </script>
                    </div>
                </div>
            </header>
        </div>
		<!--左侧-->
		<div class="left-sidebar">
            <?php
 $pid = $_COOKIE['pid']; $cid = $_COOKIE['cid']; ?>
			<ul class="sidebar-nav" >
				<?php if(is_array($menus)): $i = 0; $__LIST__ = $menus;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuItem): $mod = ($i % 2 );++$i; $active = $menuItem['id'] == $pid?"active":""; $style = $menuItem['id'] == $pid?"display:block":""; $icon = $menuItem['id'] == $pid?"sidebar-nav-sub-ico-rotate":""; ?>
                    <li class="sidebar-nav-link " id="menu-<?php echo ($menuItem["id"]); ?>">
                        <a href="javascript:void(0);" class="sidebar-nav-sub-title <?php echo ($active); ?>">
                            <i class="am-icon-table sidebar-nav-link-logo"></i>
                            <?php echo ($menuItem["title"]); ?>
                            <span class="am-icon-chevron-down am-fr am-margin-right-sm sidebar-nav-sub-ico <?php echo ($icon); ?>"></span>
                        </a>
                        <ul class="sidebar-nav sidebar-nav-sub" style="<?php echo ($style); ?>">
                            <?php if(is_array($menuChildrens)): $i = 0; $__LIST__ = $menuChildrens;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuChildren): $mod = ($i % 2 );++$i; if($menuChildren['p_id'] == $menuItem['id']): $cActive = $menuChildren['id'] == $cid?"active":""; ?>
                                    <li class="sidebar-nav-link" id="menu-children-<?php echo ($menuChildren["id"]); ?>">
                                        <a href="###" class="<?php echo ($cActive); ?>" onclick='thisMenuIndex(<?php echo ($menuItem["id"]); ?>,<?php echo ($menuChildren["id"]); ?>,"<?php echo ($adminlUrl); echo ($menuChildren['url']); ?>")'>
                                            <span class="am-icon-angle-right sidebar-nav-link-logo"></span> <?php echo ($menuChildren["title"]); ?>
                                        </a>
                                    </li><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </ul>
                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</div>
		<!--内容区域-->
		<div class="tpl-content-wrapper clearfix">
			
   
   <block name="body">
      <h1 style="margin-top: 20px;margin-left: 20px">欢迎管理后台</h1>
   
		</div>
	</div>
    <script src="/Application/Admin/Static/js/toast.js" type="text/javascript" ></script>
    <script src="/Application/Admin/Static/js/toast.class.js" type="text/javascript" ></script>
    <script src="/Application/Admin/Static/js/app.js" type="text/javascript" ></script>
    <script>
        function  thisMenuIndex(pid,cid,url) {
                console.log(cid);
            $.ajax({
                type:"post",
                data:{"pid":pid,"cid":cid},
                url:"<?php echo U('Admin/Index/menuIndex');?>",
                success:function(){
                    window.location.href = url;
                },
                error:function(){
                    window.location.href = url;
                }
            })
        }
//        console.log("%c四溢满孤舟",'font-size:12px;color:red');
//        console.log("%c点点坠穷楼",'font-size:12px;color:red');
//        console.log("%c化雨踏空去",'font-size:12px;color:red');
//        console.log("%c虚空步神州",'font-size:12px;color:red');
//        console.log("%c祥云桥上望",'font-size:12px;color:red');
//        console.log("%c青伞伫桥头",'font-size:12px;color:red;');
    </script>
</body>
</html>