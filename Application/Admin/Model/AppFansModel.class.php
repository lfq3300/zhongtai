<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppFansModel extends CommonModel
{
    public function addFans($send_result,$send_result2,$appid,$time){
            $send_result = json_decode($send_result, true);
            $new_user = 0;
            $cancel_user = 0;
            $fans = $send_result["list"];
            if(empty($fans)){
                return;
            }
            foreach ($fans as $val){
                $new_user += $val['new_user'];
                $cancel_user += $val['cancel_user'];
            }
            $send_result2 = json_decode($send_result2, true);
            list($fansCount) = $send_result2["list"];
            $data = array(
                "ref_date"=>$time." 00:00:00",
                "new_user"=>$new_user,
                "cancel_user"=>$cancel_user,
                "pure_user"=>$new_user -  $cancel_user,
                "cumulate_user"=>$fansCount["cumulate_user"],
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $data['appid'] = $appid;
            if (S($appid.$time."fans",true)){
                return;
            }else{
                S($appid.$time."fans",true);
                $ret = M("app_fans")->lock(true)->add($data);
            }
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($data,true));
            }else{
                S($appid.$time."fans",$fansCount["cumulate_user"]);
            }
    }

    public function getFansCount($appid,$time){
        $c = $time." 00:00:00";
        if(S($appid.$time."fans")){
            return S($appid.$time."fans");
        }else{
            list($info) =  M()->query("SELECT cumulate_user FROM mc_app_fans WHERE appid = '$appid' AND ref_date = '$c' ");
            S($appid.$time."fans",$info['cumulate_user']);
            return $info['cumulate_user'];
        }
    }

    public function getFans($page,$r,$stime,$etime,$query,$queryType){
        $row = ($page-1) * $r;
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND  A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " WHERE A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        $where1 = "";
        if(!empty($query)){
            if ($queryType == 1){
                $where1 = " AND B.nick_name LIKE '%$query%'";
            }else{
                $where1 = " AND B.responsible LIKE '%$query%'";
            }
        }
        $list = M()->query("SELECT B.nick_name,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,A.cumulate_user,A.pure_user,A.new_user,B.responsible,B.position 
                            FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where $where1 ORDER BY A.ref_date desc limit $row,$r");
        list($count) = M()->query("SELECT count(*) as len FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where $where1");
        return array($list,$count['len']);
    }

}