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
    public function addFans($send_result,$send_result2,$appid,$time,$access_token = ''){
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
            //如果  当前的总粉丝数为空  那么一定请求过程发生错误
            //再次发起请求 ***
            if(empty($fansCount["cumulate_user"])){
                $url2 = "https://api.weixin.qq.com/datacube/getusercumulate?access_token=$access_token";
                $parameter = array(
                    "begin_date" => $time,
                    "end_date" =>$time
                );
                $send_result2 = curl_get_https($url2, json_encode($parameter, true));
                list($fansCount) = $send_result2["list"];
            }
            $data = array(
                "ref_date"=>$time." 00:00:00",
                "new_user"=>$new_user,
                "cancel_user"=>$cancel_user,
                "pure_user"=>$new_user -  $cancel_user,
                "cumulate_user"=>$fansCount["cumulate_user"],
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $data['appid'] = $appid;
            if (empty($fansCount["cumulate_user"])){
                //不在请求 写入日志
                writeLog('data',json_encode($data,true));
                return;
            }
            $ret = M("app_fans")->lock(true)->add($data);
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


    public function getFansAll($stime,$etime,$query,$queryType){
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
                            FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where $where1 ORDER BY A.ref_date desc ");
        return $list;
    }

    public function getoneFans($page,$r,$stime,$etime,$id){
        $row = ($page-1) * $r;
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND  A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        $where.= " AND B.id = $id ";
        $list = M()->query("SELECT B.nick_name,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,A.cumulate_user,A.pure_user,A.new_user,B.responsible,B.position 
                            FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where  ORDER BY A.ref_date desc limit $row,$r");
        list($count) = M()->query("SELECT count(*) as len FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where ");
        return array($list,$count['len']);
    }

    public function getonFansAll($stime,$etime,$id){
        if (!empty($stime) || !empty($etime)){
            if(strtotime($etime)>strtotime($stime)){
                if ($stime && $etime){
                    $where = " AND A.ref_date BETWEEN '$stime 00:00:00' AND '$etime 00:00:00' ";
                }else if ($stime){
                    $where = " AND  A.ref_date > '$stime 00:00:00' ";
                }else if ($etime){
                    $where = " AND A.ref_date < '$etime 00:00:00' ";
                }
            }else if (strtotime($etime)==strtotime($stime)){
                $where = " AND A.ref_date = '$stime 00:00:00' ";
            }
        }
        $where.= " AND B.id = $id ";
        $list = M()->query("SELECT B.nick_name,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,A.cumulate_user,A.pure_user,A.new_user,B.responsible,B.position 
                            FROM mc_app_fans  as A INNER JOIN mc_app as B ON A.appid = B.appid WHERE true $where  ORDER BY A.ref_date desc");
        return $list;
    }


    public function getFansData($page,$r,$query = '',$stime = '',$etime = ''){
        return array();
        //获取 昨天的数据日期的 号数
        $Number = date("d",strtotime("- 1 day"));
        //获取前天的数据日期
        $yDay = Date("Y-m-d",strtotime("- 2 day"));
        print_r($yDay);
        //获取昨日数据日期
        $thisDay = date('Y-m-d',strtotime("- 1 day"));
        print_r($thisDay);
        $data = array();
        //获取昨日的数据
        $dayData =  M()->query("SELECT 
        DATE_FORMAT(A.ref_date, '%Y-%m-%d') AS ref_date,
        B.`nick_name`,IFNULL(A.`cumulate_user`,0) AS  cumulate_user,
        IFNULL(A.`cancel_user`,0) AS  cancel_user,IFNULL(A.`pure_user`,0) as pure_user,IFNULL(A.`new_user`,0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`ref_date` = '$thisDay' 
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");

        $appids = "(";
        foreach ($dayData as $key=>$val){
            $appids.= '"'.$val["appid"].'",';
            $data[$val["appid"]] = array(
                "ref_date"=>$val["ref_date"],
                "nick_name"=>$val["nick_name"],
                "appid"=>$val["appid"],
                "cumulate_user"=>array(
                    "data"=>$val["cumulate_user"],
                    "data1"=>$val["cumulate_user"]
                ),
                "pure_user"=>array(
                    "data"=>$val["pure_user"],
                    "data1"=>$val["pure_user"],
                ),
                "new_user"=>array(
                    "data"=>$val['new_user'],
                    "data1"=>$val['new_user']
                ),
                "cancel_user"=>array(
                    "data"=>$val['cancel_user'],
                    "data1"=>$val['cancel_user']
                ),
            );
        }
        $ids = rtrim($appids,",").")";
        //获取前天的数据 和 昨日的进行对比
        $yData = M()->query("SELECT IFNULL(A.`cumulate_user`,0) AS  cumulate_user,
        IFNULL(A.`cancel_user`,0) AS  cancel_user,IFNULL(A.`pure_user`,0) as pure_user,IFNULL(A.`new_user`,0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids AND  A.`ref_date` = '$yDay' 
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");
        foreach ($yData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data1"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data1"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data1"]= $val["new_user"];
            $data[$val["appid"]]["cancel_user"]["data1"]= $val["cancel_user"];
        }

        //获取昨天的星期号数   若超了第三个星期  需要查询回前两个星期的

        //获取
        $w = date('w');
        if( $w== 1){
            //如果是转个星期一的话
            $MondayStart = date('Y-m-d', strtotime('-1 monday', time()));
            $MondayEnd = date('Y-m-d', strtotime('-1 monday', time())+ 7 * 60*60*24);
            $pMondayStart = date("Y-m-d",strtotime('-2 monday',time()) - 7*60*60*24);
            $pMondayEnd = date("Y-m-d",strtotime('-2 monday') +  7*60*60*24);

        } else{
            //  数据的星期一
            $MondayStart = date('Y-m-d', strtotime("- $w day", time()));
            //  数据 当天
            $MondayEnd = date('Y-m-d', strtotime("- 1 day"));
            //上个星期
            $pMondayStart = date("Y-m-d",strtotime('- 1 monday',time()) - 7*60*60*24);
            $pMondayEnd = date("Y-m-d",strtotime('- 1 monday') +  7*60*60*24);

        }
        $tMData = M()->query("SELECT IFNULL(SUM(A.`cumulate_user`),0) AS cumulate_user,
        IFNULL(SUM(A.`cancel_user`),0) AS  cancel_user,IFNULL(SUM(A.`pure_user`),0) as pure_user,IFNULL(SUM(A.`new_user`),0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids  and A.`ref_date` BETWEEN '$MondayStart' AND '$MondayEnd'
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");

        foreach ($tMData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data2"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data2"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data2"]= $val["new_user"];
            $data[$val["appid"]]["cancel_user"]["data2"]= $val["cancel_user"];
        }

        $pMData = M()->query("SELECT IFNULL(SUM(A.`cumulate_user`),0) AS cumulate_user,
        IFNULL(SUM(A.`cancel_user`),0) AS  cancel_user,IFNULL(SUM(A.`pure_user`),0) as pure_user,IFNULL(SUM(A.`new_user`),0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids  and A.`ref_date` BETWEEN '$pMondayStart' AND '$pMondayEnd'
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");

        foreach ($pMData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data3"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data3"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data3"]= $val["new_user"];
            $data[$val["appid"]]["cancel_user"]["data3"]= $val["cancel_user"];
        }
        //判断今天是不是1号
        if(date("d") == 1){
            //最后一天 取最后 一天 和 1号
            $psm = date('Y-m-01', strtotime('-2 month'));
            $pem = date('Y-m-t', strtotime('-2 month'));
            $sm =  date('Y-m-01', strtotime('-1 month'));
            $em = date('Y-m-t', strtotime('-1 month'));

        }else{
            //判断获取时间是多少 号  上个月有没有获取时间的号
            //获取上个天数
            $pmdays = date('t', strtotime(date("Y-m-d",strtotime("-1 month"))));
            //这个月
            $sm = date('Y-m-01');
            $em = date('Y-m-d',strtotime("- 2 day"));
            //上个月就获取全部
            $psm =  date('Y-m-01', strtotime('-1 month'));
            if ($pmdays == $Number){
                $pem = date('Y-m-t', strtotime('-1 month'));
            }else{
                $pem = date('Y-m-01', strtotime("+ $Number month"));
            }
        }
        $tMData = M()->query("SELECT IFNULL(SUM(A.`cumulate_user`),0) AS cumulate_user,
        IFNULL(SUM(A.`cancel_user`),0) AS  cancel_user,IFNULL(SUM(A.`pure_user`),0) as pure_user,IFNULL(SUM(A.`new_user`),0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids  and A.`ref_date` BETWEEN '$sm' AND '$em'
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");
        foreach ($tMData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data4"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data4"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data4"]= $val["new_user"];
            $data[$val["appid"]]["cancel_user"]["data4"]= $val["cancel_user"];
        }
        $pMData = M()->query("SELECT IFNULL(SUM(A.`cumulate_user`),0) AS cumulate_user,
        IFNULL(SUM(A.`cancel_user`),0) AS  cancel_user,IFNULL(SUM(A.`pure_user`),0) as pure_user,IFNULL(SUM(A.`new_user`),0) AS new_user,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids  and A.`ref_date` BETWEEN '$psm' AND '$pem'
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");
        foreach ($pMData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data5"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data5"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data5"]= $val["new_user"];
            $data[$val["appid"]]["cancel_user"]["data5"]= $val["cancel_user"];
        }
    }

}