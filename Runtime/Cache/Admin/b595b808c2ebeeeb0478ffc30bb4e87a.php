<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>登录</title>
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="apple-touch-icon-precomposed" href="assets/i/app-icon72x72@2x.png">
    <meta name="apple-mobile-web-app-title" content="Amaze UI" />
    <script src="/Application/Admin/Static/js/jquery.min.js"></script>
    <link href="/Application/Admin/Static/css/amazeui.min.css" rel="stylesheet" type="text/css"/>
    <link href="/Application/Admin/Static/css/amazeui.reset.css" rel="stylesheet" type="text/css"/>
    <link href="/Application/Admin/Static/css/amazeui.datatables.min.css" rel="stylesheet" type="text/css" />
    <link href="/Application/Admin/Static/css/app.css" rel="stylesheet" type="text/css"/>
    <script src="/Application/Admin/Static/js/amazeui.min.js"></script>
    <script type="text/javascript">
        function KeyloginFun(event){
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if (e.keyCode == 13 ) //回车键是13
            {
                loginFun();
            }
        }
        function loginFun(){
            var account = $("#account").val();
            var password = $("#password").val();
            $.ajax({
                type:"post",
                data:{"account":account,"password":password},
                url:"<?php echo U('Index/login');?>",
                success:function(e){
                    console.log(e);
                    if(e.ret != 1){
                        alert(e.msg);
                    }else{
                        window.location.href ="<?php echo U('Admin/Admin/admin');?>";
                    }
                }
            })
        }
    </script>
</head>
<body data-type="login" class="theme-white" onkeydown="KeyloginFun()">
    <div class="am-g tpl-g">
        <div class="tpl-login">
            <div class="tpl-login-content">
                <div class="tpl-login-logo">

                </div>
                <form class="am-form tpl-form-line-form">
                    <div class="am-form-group">
                        <input type="text" value="" class="tpl-form-input"  id="account" placeholder="请输入账号" >

                    </div>

                    <div class="am-form-group">
                        <input type="password" value="" class="tpl-form-input" id="password" placeholder="请输入密码">
                    </div>
                    <div class="am-form-group">
                        <button type="button" onclick="loginFun()"  class="am-btn am-btn-primary  am-btn-block tpl-btn-bg-color-success  tpl-login-btn">登录</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>