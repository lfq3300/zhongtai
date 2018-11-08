<?php
namespace Admin\Controller;
use Think\Controller;
use Common\Wx\WXBizMsgCrypt;
use Admin\Builder\AdminConfigBuilder;

class WxController extends Controller
{

    public function index(){
        echo "success";
    }
    public function savData(){
        M()->execute("UPDATE mc_app set synchron = 1");
        M()->execute("delete from mc_app_data");
        M()->execute("delete from mc_app_fans");
    }
    /*
     * 每隔10分钟 微信回调并且更新 ticket  存入数据库
     */
    public function socket()
    {
        $encodingAesKey = C('ZTENCODINGAESKEY');
        $token = C('ZTTOKEN');
        $appId = C('ZTAPPID');
        $timeStamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $msg_sign = $_GET['msg_signature'];
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
            M("ticket")->where(array("id" => 1))->save(['ticket' => $component_verify_ticket, 'create_time' => date("Y-m-d H:i:s")]);
        }
        echo 'success';
    }

    //扫码授权回调函数
    public function AuthorizeCallback()
    {
        $auth_code = $_GET["auth_code"];
        $accountid = $_GET["accountid"];
        if ($auth_code) {
            $Auth = new AuthorizeController();
            $component_access_token = $Auth->getAccessToken();

            $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=$component_access_token";
            $data = array(
                "component_appid" => C('ZTAPPID'),
                "authorization_code" => $auth_code
            );
            $send_result = curl_get_https($url, json_encode($data));
            $send_result = json_decode($send_result, true);
            $authorization_info = $send_result['authorization_info'];
            $authorizer_appid = $authorization_info['authorizer_appid'];
            $authorizer_refresh_token = $authorization_info['authorizer_refresh_token'];
            $url1 = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=" . $component_access_token;
            $data1 = array(
                "component_appid" => C('ZTAPPID'),
                "authorizer_appid" => $authorizer_appid,
            );
            $appInfo = curl_get_https($url1, json_encode($data1, true));
            $appInfo = json_decode($appInfo, true);
            $authorizer_info = $appInfo['authorizer_info'];
            $userInfo = D("account")->getAccountNameInfo($accountid);
            $appData = [
                'nick_name' => $authorizer_info['nick_name'],
                'appid' => $authorizer_appid,
                'authorizer_refresh_token' => $authorizer_refresh_token,
                'create_time' => date("Y-m-d H:i:s"),
                'head_img' => $authorizer_info['head_img'],
                'service_type_info' => $authorizer_info['service_type_info']['id'],   //	授权方公众号类型，0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
                'verify_type_info' => $authorizer_info['verify_type_info']['id'],  // verify_type_info -1代表未认证，0代表微信认证， ，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证
                'user_name' => $authorizer_info['user_name'], //授权方公众号的原始ID
                'qrcode_url' => $authorizer_info['qrcode_url'],
                'principal_name' => $authorizer_info['principal_name'],
                'alias' => $authorizer_info['alias'],
                'account_id' => $accountid,  //
                'responsible'=>$userInfo['nick_name'],
                'position'=>$userInfo['position']
            ];
            if (D("Admin/App")->create($appData, 1)) {
                $ret = D("Admin/App")->addApp($appData);
                if ($ret) {
                    $url = U("App/index");
                    echo "<script> window.location.href = '$url'</script>";
                } else {
                    echo D("Admin/App")->getError();
                }
            } else {
                echo D("Admin/App")->getError();
            }
        }
    }


    public function resData(){
        $token = I("get.token");
        if($token == "sniStyziyMXwl0pG"){
            M()->execute("update mc_app set day_synchron = 2");
        }
    }

    // 定时任务
    // 每天一点  获取 最新文章 的阅读量  所以不需要减去昨日阅读的人数
    public function getRead()
    {
        $token = I("get.token");
        if (C(READTOKEN) == $token){
            $appList = D("App")->getEffeList();
            foreach ($appList as $key => $val){
                $Auth = new AuthorizeController();
                $access_token = $Auth->refreshAccessToken($val["appid"], $val["authorizer_refresh_token"]);
                $url = "https://api.weixin.qq.com/datacube/getuserread?access_token=$access_token";
                $time = C(YESTERDAY);
                $data = array(
                    "begin_date" =>$time,
                    "end_date" =>$time
                );
                $send_result = curl_get_https($url, json_encode($data, true));
                D("AppData")->addHisData($send_result,$val["appid"],$time);
                D("App")->savaData($val["appid"]);
            }
        }
    }

    //每天一点 15分钟  获取粉丝数量信息

    public function getFans()
    {
        $token = I("get.token");
        if (C(FANSTOKEN) == $token){
            $appList = D("App")->getEffeList();
            foreach ($appList as $key => $val){
                $Auth = new AuthorizeController();
                $access_token = $Auth->refreshAccessToken($val["appid"], $val["authorizer_refresh_token"]);
                $url = "https://api.weixin.qq.com/datacube/getusersummary?access_token=$access_token";
                $url2 = "https://api.weixin.qq.com/datacube/getusercumulate?access_token=$access_token";
                $time =  C(YESTERDAY);
                $data = array(
                    "begin_date" => $time,
                    "end_date" =>$time
                );
                D("App")->DelData($val["appid"],$time);
                $send_result = curl_get_https($url, json_encode($data, true));
                $send_result2 = curl_get_https($url2, json_encode($data, true));
                D("AppFans")->addFans($send_result,$send_result2,$val["appid"],$time,$access_token);
            }
        }
    }

    public function synchronHistoryFans(){
        $token =I("get.token");
        if (C(HISFANS) == $token){
            $hisday = C(HISDAY);
            $time = strtotime($hisday);
            $thisday = strtotime(date("Y-m-d",strtotime("-1 day")));
            $day = ($thisday-$time)/86400;
            $appList = D("App")->getHisList();
            foreach ($appList as $key => $val){
                for ($i = 0;$i<$day;$i++){
                    $Auth = new AuthorizeController();
                    $access_token = $Auth->refreshAccessToken($val["appid"], $val["authorizer_refresh_token"]);
                    $url = "https://api.weixin.qq.com/datacube/getusersummary?access_token=$access_token";
                    $url2 = "https://api.weixin.qq.com/datacube/getusercumulate?access_token=$access_token";
                    $data = array(
                        "begin_date" => date("Y-m-d",strtotime("$hisday +$i day")),
                        "end_date" => date("Y-m-d",strtotime("$hisday +$i day"))
                    );
                    $send_result = curl_get_https($url, json_encode($data, true));
                    $send_result2 = curl_get_https($url2, json_encode($data, true));
                    D("AppFans")->addFans($send_result,$send_result2,$val["appid"],date("Y-m-d",strtotime("$hisday +$i day")));
                }
           }
        }
    }

    //同步历史记录  今年历史3月份开始  必须先确保之前的定时任务完成  才执行
    public function  synchronHistoryData(){
        $token =I("get.token");
        if (C(HISDATA) == $token){
            $hisday = C(HISDAY);
            $time = strtotime($hisday);
            $thisday = strtotime(date("Y-m-d",strtotime("-1 day")));
            $day = ($thisday-$time)/86400;
            $appList = D("App")->getHisList();
            foreach ($appList as $key => $val){
               for ($i = 0;$i<$day;$i++){
                    $Auth = new AuthorizeController();
                    $access_token = $Auth->refreshAccessToken($val["appid"], $val["authorizer_refresh_token"]);
                    $url = "https://api.weixin.qq.com/datacube/getuserread?access_token=$access_token";
                    $data = array(
                        "begin_date" =>date("Y-m-d",strtotime("$hisday +$i day")),
                        "end_date" =>date("Y-m-d",strtotime("$hisday+$i day"))
                    );
                    $send_result = curl_get_https($url, json_encode($data, true));
                    D("AppData")->addHisData($send_result,$val["appid"],date("Y-m-d",strtotime("$hisday +$i day")));
                }
                D("App")->saveSynchron($val["appid"]);
            }
        }
    }

}
?>
