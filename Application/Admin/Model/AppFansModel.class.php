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

    /*
     *  $data = [
            [
                "ref_data"=>"2018-10-08",
                "ranking"=>"1",
                "nick_name"=>"测试",
                "cumulate_user"=>[
                    "data"=>6600000,
                    "data1"=>35,
                    "data2"=>45,
                    "data3"=>35,
                    "data4"=>45,
                    "data5"=>35,
                    "data6"=>45,
                ],
                "new_user"=>[
                    "data"=>6600000,
                    "data1"=>35,
                    "data2"=>45,
                    "data3"=>35,
                    "data4"=>45,
                    "data5"=>35,
                    "data6"=>45,
                ],
                "pure_user"=>[
                    "data"=>6600000,
                    "data1"=>35,
                    "data2"=>45,
                    "data3"=>35,
                    "data4"=>45,
                    "data5"=>35,
                    "data6"=>45,
                ],
                "increase_user"=>[
                    "data"=>6600000,
                    "data1"=>35,
                    "data2"=>45,
                    "data3"=>35,
                    "data4"=>45,
                    "data5"=>35,
                    "data6"=>45,
                ],
            ]
        ];
     * */

    public function getFansData($page,$r,$query = '',$stime = '',$etime = ''){
        $thisDay = Date("Y-m-d",strtotime("- 1 day"));
        $yDay = Date("Y-m-d",strtotime("- 2 day"));
        $data = array();
        $dayData =  M()->query("SELECT 
        DATE_FORMAT(A.ref_date, '%Y-%m-%d') AS ref_date,
        B.`nick_name`,A.`cumulate_user`,
        A.`cancel_user`,A.`pure_user`,A.`new_user`,A.appid 
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
                    "data"=>$val["cumulate_user"]
                ),
                "pure_user"=>array(
                    "data"=>$val["pure_user"]
                ),
                "new_user"=>array(
                    "data"=>$val['new_user']
                ),
                "increase_user"=>array(
                    "data"=>$val['increase_user']
                ),
            );
        }
        $ids = rtrim($appids,",").")";
        $yData = M()->query("SELECT 
        DATE_FORMAT(A.ref_date, '%Y-%m-%d') AS ref_date,
        B.`nick_name`,A.`cumulate_user`,
        A.`cancel_user`,A.`cumulate_user`,A.appid 
        FROM mc_app_fans AS A INNER JOIN mc_app AS B
        ON A.`appid` = B.`appid`  WHERE A.`appid` IN $ids AND  A.`ref_date` = '$yDay' 
        GROUP BY A.`appid` ORDER BY A.cumulate_user DESC");
        foreach ($yData as $key=>$val){
            $data[$val["appid"]]["cumulate_user"]["data1"]= $val["cumulate_user"];
            $data[$val["appid"]]["pure_user"]["data1"]= $val["pure_user"];
            $data[$val["appid"]]["new_user"]["data1"]= $val["new_user"];
            $data[$val["appid"]]["increase_user"]["data1"]= $val["increase_user"];
        }
        //周 上周 这周
        

    }

}