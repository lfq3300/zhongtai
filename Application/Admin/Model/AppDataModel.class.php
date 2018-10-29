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
        $send_result = json_decode($send_result, true);
        $data = $send_result["list"];
        foreach ($data as $key=>$b){
            $fans = D("AppFans")->getFansCount($appid,$b["ref_date"]);
            $ref_date =  $b["ref_date"];
            $msgId = $b["msgid"];
            $title = $b["title"];
            $dataInfo = $b["details"][0];

            if($fans == 0){
                $active_percent = 0;
                $conversation_percent = 0;
                $open_percent = 0;
            }else{
                $active_percent = $dataInfo["int_page_read_user"] / $fans * 100;
                $conversation_percent = $dataInfo['int_page_from_session_read_user'] / $fans * 100;
                $open_percent = $dataInfo["int_page_from_feed_read_user"] / $fans * 100;
            }
            $dataInfo['int_page_read_count'] == 0?$share_percent = 0:$share_percent = $dataInfo["share_user"] / $dataInfo['int_page_read_count'] * 100;
            $c = array(
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
                "active_percent"=> $active_percent, //阅读量 / 总粉丝
                "share_percent"=> $share_percent, // 分享转发量/总阅读量
                "conversation_percent"=> $conversation_percent,  //公众号会话 / 总粉丝
                "open_percent"=>$open_percent, //朋友圈打开 /  总粉丝
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $ret = M("app_data")->lock(true)->add($c);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($c,true));
            }
        }
    }


    public function addHisData($send_result,$appid){
        $send_result = json_decode($send_result, true);
        $data = $send_result["list"];
        foreach($data as $key=>$val){
            $details = $val['details'];
            foreach ($details as $k=>$dataInfo){
                $fans = D("AppFans")->getFansCount($appid,$dataInfo['stat_date']); // 43327
                $yesterInfo = D("AppData")->yesterdayRead($val["msgid"]);
                if(empty($yesterInfo)){
                    $yesterInfo = array(
                        "int_page_read_user"=>0,
                        "int_page_read_count"=>0,
                        "ori_page_read_user"=>0,
                        "ori_page_read_count"=>0,
                        "int_page_from_session_read_user"=>0,
                        "int_page_from_session_read_count"=>0,
                        "int_page_from_feed_read_user"=>0,
                        "int_page_from_feed_read_count"=>0,
                        "int_page_from_friends_read_user"=>0,
                        "int_page_from_friends_read_count"=>0,
                        "share_user"=>0,
                        "share_count"=>0,
                        "add_to_fav_user"=>0,
                        "add_to_fav_count"=>0,
                    );
                }
                $int_page_read_user = $dataInfo["int_page_read_user"] - $yesterInfo["int_page_read_user"];
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
                if($fans == 0){
                    $active_percent = 0;
                   $conversation_percent = 0;
                    $open_percent = 0;
                }else{
                    $active_percent = $int_page_read_user / $fans * 100;
                    $conversation_percent = $int_page_from_session_read_user / $fans * 100;
                    $open_percent = $int_page_from_feed_read_user / $fans * 100;
                }
                $int_page_read_count == 0?$share_percent = 0:$share_percent = $share_user / $int_page_read_count * 100;

                $c = array(
                    "msgid"=>$val["msgid"],
                    "title"=>$val["title"],
                    "ref_date"=> $dataInfo["stat_date"],
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
                    "appid"=>$appid,
                    "active_percent"=> $active_percent, //阅读总量 / 总粉丝
                    "share_percent"=>$share_percent, // 分享转发量/总阅读量
                    "conversation_percent"=> $conversation_percent,  //公众号会话 / 总粉丝
                    "open_percent"=>$open_percent, //朋友圈打开 /  总粉丝
                    "creater_time"=>date("Y-m-d H:i:s")
                );
                $ret = M("app_data")->lock(true)->add($c);
                if(!$ret){
                    writeLog('error',M()->getLastSql());
                    writeLog('data',json_encode($c,true));
                }
            }
        }
    }

    public function addPastData($send_result,$val){
        $send_result = json_decode($send_result, true);
        $data = $send_result["list"];
        foreach ($data as $key=>$dataInfo){
            $fans = D("AppFans")->getFansCount($val['appid'],$dataInfo['stat_date']);
            $yesterInfo = D("AppData")->yesterdayRead($dataInfo["msgid"]);
            $msgId = $dataInfo["msgid"];
            $title = $dataInfo["title"];
            $dataInfo = $dataInfo["details"][7-$val["num"]];
            $int_page_read_user = $dataInfo["int_page_read_user"] - $yesterInfo["int_page_read_user"];
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
            if($fans == 0){
                $active_percent = 0;
                $conversation_percent = 0;
                $open_percent = 0;
            }else{
                $active_percent = $int_page_read_user / $fans * 100;
                $conversation_percent = $int_page_from_session_read_user / $fans * 100;
                $open_percent = $int_page_from_feed_read_user / $fans * 100;
            }
            $int_page_read_count == 0?$share_percent = 0:$share_percent = $share_user / $int_page_read_count * 100;
            $c = array(
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
                "active_percent"=> $active_percent, //阅读总量 / 总粉丝
                "share_percent"=>$share_percent, // 分享转发量/总阅读量
                "conversation_percent"=> $conversation_percent,  //公众号会话 / 总粉丝
                "open_percent"=>$open_percent, //朋友圈打开 /  总粉丝
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $ret = M("app_data")->lock(true)->add($c);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($c,true));
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

    public function getAppData($id,$page,$r,$stime,$etime){
        $row = ($page-1) * $r;
        $where = "";
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        list($app) = M()->query("SELECT appid,responsible,`position` FROM mc_app where id = $id limit 1");
        $appid = $app["appid"];
        $responsible = $app["responsible"];
        $position = $app["position"];
        $info = M()->query(" SELECT A.title,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,'$responsible' as responsible,'$position' as `position`,
                             A.int_page_read_user,A.int_page_read_count,A.int_page_from_session_read_user,A.int_page_from_feed_read_user,A.share_user,A.active_percent,A.conversation_percent,A.open_percent,A.share_percent
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) 
                             WHERE A.appid = '$appid' $where ORDER  BY A.msgid desc,A.ref_date desc limit $row,$r");
        list($count) = M()->query(" SELECT count(*) AS len  FROM mc_app_data as A INNER JOIN  mc_app_fans as B on (A.appid = B.appid and A.ref_date = B.ref_date) WHERE A.appid = '$appid' $where ");
        return array($info,$count['len']);
    }

    public function excelAppData($id,$stime,$etime){
        $where = "";
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        list($app) = M()->query("SELECT appid,responsible,`position` FROM mc_app where id = $id limit 1");
        $appid = $app["appid"];
        $responsible = $app["responsible"];
        $position = $app["position"];
        $info = M()->query(" SELECT B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,'$responsible' as responsible,'$position' as `position`,
                             A.int_page_read_user,A.int_page_read_count,A.int_page_from_session_read_user,A.int_page_from_feed_read_user,A.share_user,A.active_percent,A.conversation_percent,A.open_percent,A.share_percent
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) 
                             WHERE A.appid = '$appid' $where ORDER  BY A.ref_date desc");
        return $info;
    }

    public function getGroupList($page,$r,$query,$queryType){
        $row = ($page-1) * $r;
        $where = "";
        if(!empty($query)){
            if ($queryType == 1){
                $where = " WHERE group_name LIKE '%$query%'";
            }else{
                $where = " WHERE responsible = '$query'";
            }
        }
        if ($queryType == 1){
            $list = M()->query("SELECT id,group_name FROM mc_app_group $where ORDER  BY id DESC limit $row,$r");
            $app = M()->query("SELECT COUNT(*) AS len,group_id FROM mc_app  WHERE group_id IN ( SELECT id FROM mc_app_group $where ) GROUP BY group_id");
            foreach ($app as $key=>$val){
                foreach ($list as $k=>$v){
                    if($v['id'] == $val["group_id"]){
                        $list[$k]['len'] = $val["len"];
                    };
                }
            }
            $count = M()->query("SELECT count(*) as len FROM mc_app_group  $where");
            return array($list,$count['len']);
        }else{
            $list = M()->query("SELECT responsible,COUNT(*) AS len FROM mc_app $where GROUP BY responsible limit $row,$r");
            $count = M()->query("SELECT count(*) as len FROM mc_app  $where");
            return array($list,$count['len']);
        }
    }

    public function getAppsData($key,$page,$r,$stime,$etime,$state){
        $row = ($page-1) * $r;
        $where1 = "";
        if ($state){
            $where1  = "C.group_id = $key";
        }else{
            $where1 = "C.responsible = '$key'";
        }
        $where = "";
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        $info = M()->query("SELECT A.appid,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,C.responsible,C.position,C.nick_name,
                             A.int_page_read_user,A.int_page_read_count,A.int_page_from_session_read_user,A.int_page_from_feed_read_user,A.share_user,A.active_percent,A.conversation_percent,A.open_percent,A.share_percent
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                             WHERE $where1  $where ORDER  BY A.ref_date desc limit $row,$r");

        list($count) = M()->query("SELECT count(*) AS len
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                             WHERE $where1 $where ");
        return array($info,$count['len']);
    }

    public function excelGuardAppData($key,$stime,$etime,$state){
        $where1 = "";
        if ($state){
            $where1  = "C.group_id = $key";
        }else{
            $where1 = "C.responsible = '$key'";
        }
        $where = "";
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        $info = M()->query("SELECT A.appid,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,C.responsible,C.position,C.nick_name,
                             A.int_page_read_user,A.int_page_read_count,A.int_page_from_session_read_user,A.int_page_from_feed_read_user,A.share_user,A.active_percent,A.conversation_percent,A.open_percent,A.share_percent
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                             WHERE $where1 $where ORDER  BY A.ref_date desc");
        return $info;
    }
}