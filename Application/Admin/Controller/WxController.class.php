<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Wx\WXBizMsgCrypt;

class WxController extends Controller{

   /*
    * 每隔10分钟 微信回调并且更新 ticket  存入数据库
    */
   public function socket(){
       $encodingAesKey = C('ZTENCODINGAESKEY');
       $token = C('ZTTOKEN');
       $appId = C('ZTAPPID');
       print_r($encodingAesKey.'--'.$token.'--'.$appId);
       $timeStamp  = $_GET['timestamp'];
       $nonce      = $_GET['nonce'];
       $msg_sign   = $_GET['msg_signature'];
       $get = json_encode($_GET);
       $log = "{time:".date("Y-m-d H:i:s")."}{last:'post'}{result:$get}";
       file_put_contents("./Runtime/getData.log",$log."\r\n",FILE_APPEND);
       $encryptMsg = file_get_contents('php://input');
       $log = "{time:".date("Y-m-d H:i:s")."}{last:'post'}{result:$encryptMsg}";
       file_put_contents("./Runtime/input.log",$log."\r\n",FILE_APPEND);
       $pc = new WXBizMsgCrypt();
       $pc->WXBizMsgCrypt($token, $encodingAesKey, $appId);
       $xml_tree = new \DOMDocument();
       $xml_tree->loadXML($encryptMsg);
       $array_e = $xml_tree->getElementsByTagName('Encrypt');
       $encrypt = $array_e->item(0)->nodeValue;
       $format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
       $from_xml = sprintf($format, $encrypt);
       $msg = '';
       $errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
       if ($errCode == 0) {
           $xml = new \DOMDocument();
           $xml->loadXML($msg);
           $array_e = $xml->getElementsByTagName('ComponentVerifyTicket');
           $component_verify_ticket = $array_e->item(0)->nodeValue;
           $log = "{time:".date("Y-m-d H:i:s")."}{last:'解密后的component_verify_ticket是'}{result:$component_verify_ticket}";
           file_put_contents("./Runtime/jmticket.log",$log."\r\n",FILE_APPEND);
           //存入数据库
            M("ticket")->add(['ticket'=>$component_verify_ticket,'create_time'=>date("Y-m-d H:i:s")]);
           echo 'success';
       } else {
           print($errCode . "\n");
       }
       $postArr = json_decode($encryptMsg,true);
       $log = "{time:".date("Y-m-d H:i:s")."}{last:'post'}{result:$postArr}";
       file_put_contents("./Runtime/log.txt",$log."\r\n",FILE_APPEND);
       echo  'success';
   }
   //
   public function appcallback(){
       print_r($_GET);
   }

   //扫码授权回调函数
   public function AuthorizeCallback(){
        $auth_code = $_GET["auth_code"];
        $component_access_token = S("component_access_token");
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=$component_access_token";
        $data = array(
            "component_appid"=>C('ZTAPPID'),
            "authorization_code"=>$auth_code
        );
       //queryauthcode@@@PlycPyimJLNJoI4mllBD4-V9xZB_qL5FVu0xeelRBvaJm7JJNp4Jf2oW5jEW9sDz4FzFAvrVd0Z2_RAFQmprIg
       $send_result = curl_get_https($url, json_encode($data));
       $send_result = json_decode($send_result,true);
       $authorization_info = $send_result['authorization_info'];
       $authorizer_appid = $authorization_info['authorizer_appid'];
       $authorizer_access_token = $authorization_info['authorizer_access_token'];
       $authorizer_refresh_token = $authorization_info['authorizer_refresh_token'];
       //存入数据库
       // M("app")->add(['appid'=>$authorizer_appid,'refresh_token'=>$authorizer_access_token,'create_time'=>date("Y-m-d H:i:s")]);

       $url1 = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=".$component_access_token;
       $data1 = array(
           "component_appid"=>C('ZTAPPID'),
           "authorizer_appid"=>$authorizer_appid,
       );
       $appInfo = curl_get_https ($url1,json_encode($data1,true));
       $authorizer_info = $appInfo['authorizer_info'];
       $nick_name = $appInfo['nick_name'];
       $head_img = $appInfo['head_img'];
       //service_type_info
       $service_type_info =  $appInfo['service_type_info']['id'];
       $verify_type_info = $appInfo['verify_type_info']['id'];
       $user_name = $appInfo['user_name'];
       $alias = $appInfo['alias'];
       $qrcode_url  = $appInfo['qrcode_url'];
       print_r($appInfo);
   }


}
?>
