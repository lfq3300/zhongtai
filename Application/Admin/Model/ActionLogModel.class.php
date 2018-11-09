<?php
namespace Admin\Model;
use Admin\Model\CommonModel;
class ActionLogModel extends CommonModel{
    public function getAction($page,$noselect,$selectdata,$stime,$etime){
        $where = "";
        $row = ($page-1) * 20;
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.create_time BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.create_time > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.create_time < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.create_time = '$stime 00:00:00' ";
            }
        }
        if(empty($selectdata) || empty($noselect)){
            return array([],0);
        }else{
            $where .= " AND A.account_id = $selectdata  AND B.role_id = $noselect ";
        }
        $list = M()->query("SELECT A.id,B.account,B.nick_name,B.position,A.create_time,A.action FROM mc_action_log as A INNER JOIN mc_account as B on A.account_id = B.id  WHERE  state = 2 $where ORDER BY A.id DESC limit $row,20");
        list($count) = M()->query("SELECT A.id,B.account,B.nick_name,A.create_time,A.action FROM mc_action_log as A INNER JOIN mc_account as B on A.account_id = B.id  WHERE  state = 2 $where");
        return array($list,$count['len']);
    }
    public function getLoginAction($page,$noselect,$selectdata,$stime,$etime){
        $where = "";
        $row = ($page-1) * 20;
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.create_time BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND A.create_time > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.create_time < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.create_time = '$stime 00:00:00' ";
            }
        }
        if(empty($selectdata) || empty($noselect)){
            return array([],0);
        }else{
            $where .= " AND A.account_id = $selectdata  AND B.role_id = $noselect ";
        }
        $list = M()->query("SELECT A.id,B.account,B.nick_name,B.position,A.create_time,A.action FROM mc_action_log as A INNER JOIN mc_account as B on A.account_id = B.id  WHERE  state = 1 $where ORDER BY A.id DESC limit $row,20");
        list($count) = M()->query("SELECT A.id,B.account,B.nick_name,A.create_time,A.action FROM mc_action_log as A INNER JOIN mc_account as B on A.account_id = B.id  WHERE  state = 1  $where");
        return array($list,$count['len']);
    }
}