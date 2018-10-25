<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppDataModel extends CommonModel
{
    public function addData($send_result,$appid){
        $fans = D("AppFans")->getFansCount($appid);
        $send_result = json_decode($send_result, true);
        list($dataInfo) = $send_result["list"];
        if (empty($dataInfo)){
            return;
        }
        $ref_date =  $dataInfo["ref_date"];
        $msgId = $dataInfo["msgid"];
        $title = $dataInfo["title"];
        $dataInfo = $dataInfo["details"][0];
        $data = array(
            "msgid"=>$msgId,
            "title"=>$title,
            "ref_date"=>$ref_date,
            "int_page_read_user"=>$dataInfo["int_page_read_user"],
            "int_page_read_count"=>$dataInfo["int_page_read_count"],
            "ori_page_read_user"=>$dataInfo["ori_page_read_user"],
            "ori_page_read_count"=>$dataInfo["ori_page_read_count"],
            "share_user"=>$dataInfo["share_user"],
            "share_count"=>$dataInfo["share_count"],
            "add_to_fav_user"=>$dataInfo["add_to_fav_user"],
            "add_to_fav_count"=>$dataInfo["add_to_fav_count"],
            "int_page_from_session_read_user"=>$dataInfo["int_page_from_session_read_user"],
            "int_page_from_session_read_count"=>$dataInfo["int_page_from_session_read_count"],
            "int_page_from_hist_msg_read_user"=>$dataInfo["int_page_from_hist_msg_read_user"],
            "int_page_from_hist_msg_read_count"=>$dataInfo["int_page_from_hist_msg_read_count"],
            "int_page_from_feed_read_count"=>$dataInfo["int_page_from_feed_read_count"],
            "int_page_from_friends_read_user"=>$dataInfo["int_page_from_friends_read_user"],
            "int_page_from_feed_read_user"=>$dataInfo["int_page_from_feed_read_user"],
            "int_page_from_friends_read_count"=>$dataInfo["int_page_from_friends_read_count"],
            "int_page_from_other_read_user"=>$dataInfo["int_page_from_other_read_user"],
            "int_page_from_other_read_count"=>$dataInfo["int_page_from_other_read_count"],
            "target_user"=>$dataInfo["target_user"],
            "appid"=>$appid,
            "active_percent"=> $dataInfo["int_page_read_user"] / $fans * 100, //阅读量 / 总粉丝
             "share_percent"=>$dataInfo["share_user"] / $dataInfo['int_page_read_count'] * 100, // 分享转发量/总阅读量
             "conversation_percent"=> $dataInfo['int_page_from_session_read_user'] / $fans * 100,  //公众号会话 / 总粉丝
             "open_percent"=>$dataInfo["int_page_from_feed_read_user"] / $fans * 100, //朋友圈打开 /  总粉丝
             "creater_time"=>date("Y-m-d H:i:s")
        );
        $ret = M("app_data")->lock(true)->add($data);
        if (!$ret){
            writeLog('error',M()->getLastSql());
            writeLog('data',json_encode($data,true));
        }
    }

    public function addPastData($send_result,$val,$yesterInfo){
        $fans = D("AppFans")->getFansCount($val['appid']);
        $send_result = json_decode($send_result, true);
        list($dataInfo) = $send_result["list"];
        if (empty($dataInfo)){
            return;
        }
        if($dataInfo["msgid"] === $val["msgid"]){
            $msgId = $dataInfo["msgid"];
            $title = $dataInfo["title"];
            $dataInfo = $dataInfo["details"][7-$val["num"]];
            $int_page_read_user = $dataInfo["int_page_read_user"]- $yesterInfo["int_page_read_user"];
            $int_page_read_count  = $dataInfo["int_page_read_count"] -  $yesterInfo["int_page_read_count"];
            $ori_page_read_user = $dataInfo["ori_page_read_user"] - $yesterInfo["ori_page_read_user"];
            $ori_page_read_count = $dataInfo["ori_page_read_count"] - $yesterInfo["ori_page_read_count"];
            $int_page_from_session_read_user = $dataInfo["int_page_from_session_read_user"] - $yesterInfo["int_page_from_session_read_user"];
            $int_page_from_session_read_count = $dataInfo["int_page_from_session_read_count"] - $yesterInfo["int_page_from_session_read_count"];
            $int_page_from_feed_read_user = $dataInfo["int_page_from_feed_read_user"] - $yesterInfo["int_page_from_feed_read_user"];
            $int_page_from_feed_read_count = $dataInfo["int_page_from_feed_read_count"] - $yesterInfo["int_page_from_feed_read_count"];
            $int_page_from_friends_read_user = $dataInfo["int_page_from_friends_read_user"] - $yesterInfo["int_page_from_friends_read_user"];
            $int_page_from_friends_read_count = $dataInfo["int_page_from_friends_read_count"] - $yesterInfo["int_page_from_friends_read_count"];
            $share_user = $dataInfo["share_user"] - $yesterInfo["share_user"];
            $share_count = $dataInfo["share_count"] - $yesterInfo["share_count"];
            $add_to_fav_user = $dataInfo["add_to_fav_user"] - $yesterInfo["add_to_fav_user"];
            $add_to_fav_count = $dataInfo["add_to_fav_count"] - $yesterInfo["add_to_fav_count"];
            $data = array(
                "msgid"=>$msgId,
                "title"=>$title,
                "ref_date"=>$dataInfo['stat_date'],
                "int_page_read_user"=>$int_page_read_user,
                "int_page_read_count"=>$int_page_read_count,
                "ori_page_read_user"=>$ori_page_read_user,
                "ori_page_read_count"=>$ori_page_read_count,
                "share_user"=>$share_user,
                "share_count"=>$share_count,
                "add_to_fav_user"=>$add_to_fav_user,
                "add_to_fav_count"=>$add_to_fav_count,
                "int_page_from_session_read_user"=>$int_page_from_session_read_user,
                "int_page_from_session_read_count"=> $int_page_from_session_read_count,
                "int_page_from_feed_read_user"=>$int_page_from_feed_read_user,
                "int_page_from_feed_read_count"=>$int_page_from_feed_read_count,
                "int_page_from_friends_read_user"=>$int_page_from_friends_read_user,
                "int_page_from_friends_read_count"=>$int_page_from_friends_read_count,
                "target_user"=>$dataInfo["target_user"],
                "appid"=>$val['appid'],
                "active_percent"=>  $int_page_read_user / $fans * 100, //阅读总量 / 总粉丝
                "share_percent"=> $share_user / $int_page_read_count * 100, // 分享转发量/总阅读量
                "conversation_percent"=> $int_page_from_session_read_user / $fans * 100,  //公众号会话 / 总粉丝
                "open_percent"=>$int_page_from_feed_read_user / $fans * 100, //朋友圈打开 /  总粉丝
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $ret = M("app_data")->lock(true)->add($data);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($data,true));
            }
        }
    }

    public function yesterdayRead($msgId){
       list($info) =  M()->query("SELECT
              SUM(int_page_read_count) AS int_page_read_count,
              SUM(int_page_read_user) AS int_page_read_user,
              SUM(ori_page_read_count) AS ori_page_read_count,
              SUM(ori_page_read_user) AS ori_page_read_user,
              SUM(share_count) AS share_count,
              SUM(share_user) AS share_user,
              SUM(int_page_from_feed_read_count) AS int_page_from_feed_read_count,
              SUM(int_page_from_feed_read_user) AS int_page_from_feed_read_user,
              SUM(int_page_from_session_read_count) AS int_page_from_session_read_count,
              SUM(int_page_from_session_read_user) AS int_page_from_session_read_user,
              SUM(int_page_from_friends_read_count) AS int_page_from_friends_read_count,
              SUM(int_page_from_friends_read_user) AS int_page_from_friends_read_user,
              SUM(add_to_fav_count) AS add_to_fav_count,
              SUM(add_to_fav_user) AS add_to_fav_user
            FROM
              mc_app_data 
            WHERE msgid = '$msgId'");
       return $info;
    }
}