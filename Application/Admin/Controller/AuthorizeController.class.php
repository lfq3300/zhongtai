<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/18
 * Time: 15:05
 */
namespace Admin\Controller;
use Think\Controller;

class AuthorizeController extends Controller{

    //第三方平台token
    public function getAccessToken(){
        if(S("component_access_token")){
            return S("component_access_token");
        }else{
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
            list($ticket) = M()->query("SELECT ticket FROM mc_ticket ORDER BY id DESC limit 1");
            $ticket = $ticket['ticket'];
            $data = array(
                "component_appid"=>C('ZTAPPID'),
                "component_appsecret"=>C('ZTSECRET'),
                "component_verify_ticket"=>$ticket
            );
            $send_result = curl_get_https($url, json_encode($data));
            $send_result = json_decode($send_result,true);
            print_r($send_result);
            print_r("4444<br/>");
            S("component_access_token",$send_result["component_access_token"],$send_result['expires_in']);
            return $send_result["component_access_token"];
        }
    }
    //预授权码
    public function getPreAuthCode(){
       $access_token =  $this->getAccessToken();
       $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=$access_token";
       $data = array(
            "component_appid"=>C('ZTAPPID'),
        );
        $send_result = curl_get_https($url, json_encode($data));
        $send_result = json_decode($send_result,true);
        return $send_result['pre_auth_code'];
    }

    //重新刷新公众号token
    public function refreshAccessToken($appid = '', $authorizer_refresh_token = '')
    {
        if(S($appid ."access_token")){
            return S($appid ."access_token");
        }else{
            if (empty($authorizer_refresh_token)){
                list($appInfo) = M()->query("SELECT authorizer_refresh_token FROM mc_app WHERE appid = '$appid' limit 1");
                $authorizer_refresh_token = $appInfo['authorizer_refresh_token'];
            }
            $component_access_token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=$component_access_token";
            $data = array(
                "component_appid" => C('ZTAPPID'),
                "authorizer_appid" => $appid,
                "authorizer_refresh_token" => $authorizer_refresh_token,
            );
            $send_result = curl_get_https($url,json_encode($data, true));
            $send_result = json_decode($send_result, true);
            print_r($send_result);
            print_r("2222222<br/>");
            $authorizer_access_token = $send_result['authorizer_access_token'];
            S($appid ."access_token", $authorizer_access_token, $send_result['expires_in']);
            return $authorizer_access_token;
        }
    }
}