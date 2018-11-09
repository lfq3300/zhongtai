<?php

function cookieEncrypt($txt,$key='zpYxSJjSEZhYiD97'){
    //对后台的cookie进行加密
    $chars = "6h93bxskFBHJwYOydjvEmf728Tpz4WinSQDNUVRtPGqaZrue1CKglI0ALX5oMc-=+*/&%#@!";
    $nh = rand(0,71);
    $ch = $chars[$nh];
    $mdKey = md5($key.$ch);
    $mdKey = substr($mdKey,$nh%18, $nh%18+17);
    $txt = base64_encode($txt);
    $tmp = '';
    $i=0;$j=0;$k = 0;
    for ($i=0; $i<strlen($txt); $i++){
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = ($nh+strpos($chars,$txt[$i])+ord($mdKey[$k++]))%71;
        $tmp .= $chars[$j];
    }
    return urlencode($ch.$tmp);
}

function cookieDecrypt($txt,$key='zpYxSJjSEZhYiD97'){
    $txt = urldecode($txt);
    $chars = "6h93bxskFBHJwYOydjvEmf728Tpz4WinSQDNUVRtPGqaZrue1CKglI0ALX5oMc-=+*/&%#@!";
    $ch = $txt[0];
    $nh = strpos($chars,$ch);
    $mdKey = md5($key.$ch);
    $mdKey = substr($mdKey,$nh%18, $nh%18+17);
    $txt = substr($txt,1);
    $tmp = '';
    $i=0;$j=0; $k = 0;
    for ($i=0; $i<strlen($txt); $i++) {
        $k = $k == strlen($mdKey) ? 0 : $k;
        $j = strpos($chars,$txt[$i])-$nh - ord($mdKey[$k++]);
        while ($j<0) $j+=71;
        $tmp .= $chars[$j];
    }
    return base64_decode($tmp);
}

function returnJson($ret = 0,$msg = '',$data=null)
{
    $result = array();
    $result['ret'] = $ret;
    $result['msg'] = $msg;

    if($data!==null){
      /*
        需要注意一下这种情况在PHP中的处理，在php中它们都是数组
        $arr = array('a'=>'123','b'=>'456'); //json_encode之后，会变成一个对象
        $arr = array(0=>'123',1=>'456'); //json_encode之后，会变成一个数组
      */
      $result['data'] = (object)$data; //只要data有返回，它一定是一个对象，参考群文件API规范文档
    }
    header("Content-Type:application/json; charset=utf-8");
    $return = json_encode($result);
    //log return
    if(false){
      $time = date("Y-m-d H:i:s");
      $uri = $_SERVER['REQUEST_URI'];
      $postData = json_encode($_POST,JSON_UNESCAPED_UNICODE);
      $log = "{time:".$time."}{uri:".$uri."}{postData:$postData}{returnData:$return}"."\r\n";
      $logFileFile = "./Runtime/Logs/".date('Y-m-d').'.returnlog.php';
      if(!file_exists($logFileFile))@file_put_contents($logFileFile,'<?php if(!isset($_GET["passss"]) ||  $_GET["passss"]!="meicooliveabcq12123"){exit;} ?>');
      file_put_contents($logFileFile,$log,FILE_APPEND);
    }

    echo $return;
    exit;
}

function time_format($time = NULL, $format = 'Y-m-d H:i:s')
{
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

function randomArray($array){
    $len = count($array) -1;
    $index = rand(0,$len);
    return $array[$index];
}

function getIP($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

//不同环境下获取真实的IP
function get_ip(){
    //判断服务器是否允许$_SERVER
    if(isset($_SERVER)){
        if(isset($_SERVER[HTTP_X_FORWARDED_FOR])){
            $realip = $_SERVER[HTTP_X_FORWARDED_FOR];
        }elseif(isset($_SERVER[HTTP_CLIENT_IP])) {
            $realip = $_SERVER[HTTP_CLIENT_IP];
        }else{
            $realip = $_SERVER[REMOTE_ADDR];
        }
    }else{
        //不允许就使用getenv获取
        if(getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv( "HTTP_X_FORWARDED_FOR");
        }elseif(getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        }else{
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

function curl_get_https($url,$data = [])
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    if(!empty($data)){
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);  // 跳过检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 跳过检查
    $tmpInfo = curl_exec($curl);
    curl_close($curl);
    return $tmpInfo;   //返回json对象
}

function writeLog($a,$b){
    $log = "{time:".date("Y-m-d H:i:s")." log:$b}";
    file_put_contents("./Log/$a.txt",$log."\r\n",FILE_APPEND);
}

function AddactionLog($text,$account_id = ''){
    if (empty($account_id)){
        M("action_log")->add(array("create_time"=>date("Y-m-d H:i:s"),"account_id"=>cookieDecrypt(cookie('account_id')),"level"=>cookieDecrypt(cookie("level")),"action"=>$text));
    }else{
        $account_id = M("account")->where(array("account_id"=>$account_id))->find();
        M("action_log")->add(array("create_time"=>date("Y-m-d H:i:s"),"account_id"=>$account_id["id"],"level"=>$account_id["level"],"action"=>$text));
    }
}

function AddLoginActionLog($text){
    M("action_log")->add(array("state"=>1,"create_time"=>date("Y-m-d H:i:s"),"account_id"=>cookieDecrypt(cookie('account_id')),"level"=>cookieDecrypt(cookie("level")),"action"=>$text));
}