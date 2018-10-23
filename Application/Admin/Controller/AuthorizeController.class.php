<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/18
 * Time: 15:05
 */
namespace Admin\Controller;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Think\Controller;

class AuthorizeController extends AdminController {

    //第三方平台token
    public function getAccessToken(){
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
        S("component_access_token",$send_result['component_access_token'],7200);
        return $send_result['component_access_token'];
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
    public function refreshAccessToken($appid = 'wxf825ca9817d90977',$authorizer_refresh_token = ''){
        if(empty($authorizer_refresh_token)){
            list($appInfo) = M()->query("SELECT authorizer_refresh_token FROM mc_app WHERE appid = '$appid' limit 1");
            $refresh_token = $appInfo['authorizer_refresh_token'];
        }
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$refresh_token";
        print_r($url);
        $send_result = curl_get_https($url);
        $send_result = json_decode($send_result,true);
        print_r($send_result);
    }
}