<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/18
 * Time: 15:05
 */
namespace Admin\Controller;
use Think\Controller;

class AuthorizeController extends AdminController {
    public function index(){
        $component_appid = C('ZTAPPID');
        $pre_auth_code = $this->getPreAuthCode();
        $this->assign('AuthorizeUrl', "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_appid&pre_auth_code=$pre_auth_code&redirect_uri=http://zt.ltthk.top/index.php/wx/AuthorizeCallback");
        $this->display();
    }
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

    //重新刷新token
    public function refreshAccessToken($appid = 'wxf825ca9817d90977'){
        list($appInfo) = M()->query("SELECT appid,refresh_token FROM mc_app WHERE appid = '$appid' limit 1");
        $appid = $appInfo['appid'];
        $refresh_token = $appInfo['refresh_token'];
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=$appid&grant_type=refresh_token&refresh_token=$refresh_token";
        print_r($url);
        $send_result = curl_get_https($url);
        $send_result = json_decode($send_result,true);
        print_r($send_result);
    }
}