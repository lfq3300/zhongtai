<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppArticleModel extends CommonModel
{
    public function addHisData($send_result,$appid,$time){
        $send_result = json_decode($send_result, true);
        $list = $send_result["list"];
        if(empty($list)){
            return;
        }else{
            $fans = D("AppFans")->getFansCount($appid,$time);
            foreach($list as $key=>$v) {
                if($fans == 0){
                    $active_percent = 0;
                }else{
                    $active_percent = $v["int_page_read_user"] / $fans * 100;
                }
                $data = array(
                    "ref_date"=>$v["ref_date"],
                    "msgid"=>$v["msgid"],
                    "appid"=>$appid,
                    "title"=>$v["title"],
                    "int_page_read_user"=>$v["int_page_read_user"],
                    "ori_page_read_user"=>$v["ori_page_read_user"],
                    "share_user"=>$v["share_user"],
                    "add_to_fav_user"=>$v["add_to_fav_user"],
                    "cumulate_user"=>$fans,
                    "active_percent"=>$active_percent,
                    "create_date"=>date("Y-m-d H:i:s"),
                );
                $ret = M("app_article")->add($data);
                if (!$ret){
                    writeLog('error',M()->getLastSql());
                    writeLog('data',json_encode($data,true));
                }
            }
        }
    }

    public function getList($page,$r,$stime,$etime,$id,$query){
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
        if (!empty($query)){
            $where.= " AND A.title LIKE '%$query%'";
        }
        $appid = D("app")->getAppid($id);
        $list  = M()->query("SELECT A.title,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,A.int_page_read_user,A.cumulate_user,A.int_page_from_session_read_user,A.int_page_from_friends_read_user,A.share_user,A.active_percent,B.responsible,B.position 
            from mc_app_article as A INNER JOIN mc_app as B on A.appid = B.appid where A.appid = '$appid' $where ORDER BY A.ref_date desc limit $row,$r");
        list($count) =  M()->query("SELECT count(*) as len from mc_app_article as A INNER JOIN mc_app as B on  A.appid = B.appid where A.appid = '$appid' $where ORDER BY A.ref_date desc");
        return array($list,$count['len']);
    }
}