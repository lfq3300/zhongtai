<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Wx\WXBizMsgCrypt;
use Admin\Builder\AdminConfigBuilder;

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
       $encryptMsg = file_get_contents('php://input');
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
           //存入数据库
            M("ticket")->where(array("id"=>1))->save(['ticket'=>$component_verify_ticket,'create_time'=>date("Y-m-d H:i:s")]);
           echo 'success';
       } else {
           print($errCode . "\n");
       }
       echo  'success';
   }

   //扫码授权回调函数
   public function AuthorizeCallback(){
        $auth_code = $_GET["auth_code"];
        if($auth_code){
            $component_access_token = S("component_access_token");
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=$component_access_token";
            $data = array(
                "component_appid"=>C('ZTAPPID'),
                "authorization_code"=>$auth_code
            );
            $send_result = curl_get_https($url, json_encode($data));
            $send_result = json_decode($send_result,true);
            print_r($send_result);
            exit;
            $authorization_info = $send_result['authorization_info'];
            $authorizer_appid = $authorization_info['authorizer_appid'];
            $authorizer_access_token = $authorization_info['authorizer_access_token'];
            $url1 = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=".$component_access_token;
            $data1 = array(
                "component_appid"=>C('ZTAPPID'),
                "authorization_code"=>$authorizer_appid,
            );
            $appInfo = curl_get_https ($url1,json_encode($data1,true));
            $appInfo = json_decode($appInfo,true);
            print_r($appInfo);
            $authorizer_info = $appInfo['authorizer_info'];
            print_r($authorizer_info);
            //缓存公众号access_token
            S($authorizer_appid."access_token",$authorizer_access_token,7200);
            $appData = [
                'nick_name'=> $authorizer_info['nick_name'],
                'appid'=>$authorizer_appid,
                'authorizer_refresh_token'=>$authorizer_info['authorizer_refresh_token'],
                'create_time'=>date("Y-m-d H:i:s"),
                'head_img'=> $authorizer_info['head_img'],
                'service_type_info'=> $authorizer_info['service_type_info']['id'],   //	授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
                'verify_type_info'=> $authorizer_info['verify_type_info']['id'],  // verify_type_info -1代表未认证，0代表微信认证， ，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证
                'user_name'=>$authorizer_info['user_name'], //授权方公众号的原始ID
                'qrcode_url'=>$authorizer_info['qrcode_url'],
                'principal_name'=>$authorizer_info['principal_name'],
                'alias'=> $authorizer_info['alias'],
                'group_id' => 1, // 1 是 默认分组
                'admin_id'=>cookieDecrypt(cookie('account_id'))  //
            ];
            print_r($appData);
//            if (D("Admin/App")->create($appData,1)){
//                $ret = D("Admin/App")->addApp($appData);
//                if($ret){
//                    $url = U("App/index");
//                    echo "<script> window.location.href = '$url'</script>";
//                }else{
//                    echo  D("Admin/App")->getError();
//                }
//            }else{
//                echo  D("Admin/App")->getError();
//            }
        }
   }


   public function demo(){
       echo S("wxf825ca9817d90977access_token");
   }

   // 定时任务
   // 每天一点  获取阅读量
   public function getRead(){
       $token  = C(READTOKEN);I("get.token");
       if(C(READTOKEN) == $token){
           // 查询 全部公众号 然后请求 公众号数据
            $appList = M()->query(" SELECT id,appid,authorizer_refresh_token FROM mc_app ");

       }
   }

    //重新刷新公众号token
    public function refreshAccessToken($appid = '',$authorizer_refresh_token = ''){
        if(empty($authorizer_refresh_token)){
            list($appInfo) = M()->query("SELECT authorizer_refresh_token FROM mc_app WHERE appid = '$appid' limit 1");
            $authorizer_refresh_token = $appInfo['authorizer_refresh_token'];
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$authorizer_refresh_token";
     //   $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$authorizer_refresh_token";
        print_r($url);
        $send_result = curl_get_https($url);
        $send_result = json_decode($send_result,true);
        print_r($send_result);

    }

   //每天一点 15分钟  获取粉丝数量信息

    public function getFans(){
        $token  = C(FANSTOKEN);I("get.token");
        if(C(FANSTOKEN) == $token){
            if(S("applist")){
                $appList = S("applist");
            }else{
                // 查询 全部公众号 然后请求 公众号数据
                $appList = M()->query(" SELECT id,appid,authorizer_refresh_token FROM mc_app");
                //缓存4小时
                S("applist",$appList,14400);
            }
            foreach ($appList as $key => $val){

                if(S($val['appid']."access_token")){
                    $access_token = S($val['appid']."access_token");
                }else{
                    $access_token = $this->refreshAccessToken($val['appid'],$val['authorizer_refresh_token']);
                }
//                $url = "https://api.weixin.qq.com/datacube/getusersummary?access_token=$access_token";
//               $appInfo = curl_get_https ($url);
            }

        }
    }


}
?>
