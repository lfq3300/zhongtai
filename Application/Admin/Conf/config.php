<?php
return array(
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '127.0.0.1', // 服务器地址
    'DB_NAME'   => 'zt.ltthk.top', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => 'opKkwWsUgiUC0m3k',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'mc_', // 数据库表前缀


    'URL_CASE_INSENSITIVE' =>true, //不区分大小写

    //模板相关配置
  	'TMPL_PARSE_STRING' => array(
  	   '__CSS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/css',
  	   '__IMG__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/img',
  	   '__JS__' => __ROOT__ . '/Application/' . MODULE_NAME . '/Static/js',
         '__PUBLIC__'=> __ROOT__ . '/Public',
  	),


     /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes' => '', //允许上传的文件MiMe类型
        'maxSize' => 0, //上传的文件大小限制 (0-不做限制)
        'exts' => 'jpg,gif,png,jpeg,swf,webp', //允许上传的文件后缀
        'autoSub' => true, //自动子目录保存文件
        'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/', //保存根路径
        'dbPath' =>'/Uploads/',
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt' => '', //文件保存后缀，空则使用原后缀
        'replace' => false, //存在同名是否覆盖
        'hash' => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ),

	'ROOT_LEVEL'=>"-100000000",
	'ADMIN_URL'=>"http://".$_SERVER['SERVER_NAME']."/index.php/",
	'IMG_URL'=>"http://".$_SERVER['SERVER_NAME']."/",

	//第三方平台的 相关密钥
	'ZTTOKEN'=>"zhongtai",
	'ZTENCODINGAESKEY'=>"1614bc6a759aa92a28c3196208cac0bc96396203022",
	'ZTAPPID'=>"wx4136f97196624a87",
	'ZTSECRET'=>"256e5b2ad8ca1fde610c31b581b44ad1"
)
?>
