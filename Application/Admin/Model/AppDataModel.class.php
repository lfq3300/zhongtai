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
    public function addHisData($send_result,$appid,$time){
        $send_result = json_decode($send_result, true);
        $list = $send_result["list"];
        if(empty($list)){
            return;
        }else{
            $data = array();
            foreach($list as $key=>$v) {
                $data['int_page_read_user']+=$v['int_page_read_user'];
                $data['int_page_read_count']+=$v['int_page_read_count'];
                $data['ori_page_read_user']+=$v['ori_page_read_user'];
                $data['ori_page_read_count']+=$v['ori_page_read_count'];
                $data['share_user']+=$v['share_user'];
                $data['share_count']+=$v['share_count'];
                $data['add_to_fav_user']+=$v['add_to_fav_user'];
                $data['add_to_fav_count']+=$v['add_to_fav_count'];
            }
            $fans = D("AppFans")->getFansCount($appid,$time);
            $data["int_page_from_session_read_user"] =  $list[0]["int_page_read_user"];
            $data["int_page_from_session_read_count"] =  $list[0]["int_page_read_count"];
            $data["int_page_from_friends_read_user"] =  $list[2]["int_page_read_user"];
            $data["int_page_from_friends_read_count"] =  $list[2]["int_page_read_count"];
            $data["appid"] = $appid;
            $data["ref_date"] = $time;
            if($fans == 0){
                $active_percent = 0;
                $conversation_percent = 0;
                $open_percent = 0;
            }else{
                $active_percent = $data["int_page_read_user"] / $fans * 100;
                $conversation_percent = $data['int_page_from_session_read_user'] / $fans * 100;
                $open_percent = $data["int_page_from_friends_read_user"] / $fans * 100;
            }
            if($data['int_page_read_count'] == 0){
                $share_percent = 0;
            }else{
                $share_percent = $data["share_user"] / $data['int_page_read_count'] * 100;
            }
            $data["active_percent"] = $active_percent;
            $data["open_percent"] = $open_percent;
            $data["share_percent"] = $share_percent;
            $data["conversation_percent"] = $conversation_percent;
            $data["create_date"] = date("Y-m-d H:i:s");
            $ret = M("app_data")->add($data);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($data,true));
            }
        }
    }


    public function getAppData($id,$page,$r,$stime,$etime){
        $row = ($page-1) * $r;
        $where = "";
        list($app) = M()->query("SELECT appid,responsible,`position` FROM mc_app where id = $id limit 1");
        $appid = $app["appid"];
        $responsible = $app["responsible"];
        $position = $app["position"];
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
            $info = M()->query(" SELECT A.int_page_read_user,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,'$responsible' as responsible,'$position' as `position`,
                             A.int_page_from_session_read_user,A.int_page_from_friends_read_user,A.active_percent,A.open_percent,A.share_percent,A.conversation_percent,A.share_user
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date)
                             WHERE A.appid = '$appid' $where ORDER  BY A.ref_date desc limit $row,$r");
            list($count) = M()->query(" SELECT count(*) AS len  FROM mc_app_data as A INNER JOIN  mc_app_fans as B on (A.appid = B.appid and A.ref_date = B.ref_date) WHERE A.appid = '$appid' $where ");
            return array($info,$count["len"]);
    }

    public function excelAppData($id,$stime,$etime){
        $where = "";
        list($app) = M()->query("SELECT appid,responsible,`position` FROM mc_app where id = $id limit 1");
        $appid = $app["appid"];
        $responsible = $app["responsible"];
        $position = $app["position"];
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
        $info = M()->query(" SELECT A.int_page_read_user,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,'$responsible' as responsible,'$position' as `position`,
                             A.int_page_from_session_read_user,A.int_page_from_friends_read_user,A.active_percent,A.open_percent,A.share_percent,A.conversation_percent,A.share_user
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date)
                             WHERE A.appid = '$appid' $where ORDER  BY A.ref_date desc");
        return$info;
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

    public function getAppsData($key,$page,$r,$stime,$etime,$state,$query,$queryType){
        $row = ($page-1) * $r;
        if ($queryType == 1){
            $order = " ORDER BY A.ref_date DESC,A.appid DESC";
        }else{
            $order = " ORDER  BY A.appid DESC, A.ref_date DESC ";
        }
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
        if($query){
            $where.= " AND C.responsible = '$query'";
        }
        $info = M()->query("SELECT A.appid,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,C.responsible,C.position,C.nick_name,
                        A.int_page_from_session_read_user,A.int_page_from_friends_read_user,A.active_percent,A.open_percent,A.share_percent,A.conversation_percent,A.share_user,A.int_page_read_user,A.int_page_from_friends_read_user
                         FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                         B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                         WHERE $where1 $where $order limit $row,$r");
        list($count) = M()->query("SELECT count(*) AS len
                         FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                         B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                         WHERE $where1 $where ");
        return array($info,$count['len']);
    }

    public function excelGuardAppData($key,$stime,$etime,$state,$query,$queryType){
        if ($queryType == 1){
            $order = " ORDER BY A.ref_date DESC,A.appid DESC";
        }else{
            $order = " ORDER  BY A.appid DESC, A.ref_date DESC ";
        }
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
        if($query){
            $where.= " AND C.responsible = '$query'";
        }
        $info = M()->query("SELECT A.appid,B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,C.responsible,C.position,C.nick_name,
                        A.int_page_from_session_read_user,A.int_page_from_friends_read_user,A.active_percent,A.open_percent,A.share_percent,A.conversation_percent,A.share_user,A.int_page_read_user,A.int_page_from_friends_read_user
                         FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                         B on (A.appid = B.appid and A.ref_date = B.ref_date) INNER JOIN mc_app as C ON A.appid = C.appid
                         WHERE $where1 $where $order");
        return $info;
    }
}