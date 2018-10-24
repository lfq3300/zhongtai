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
            list($fans) = $send_result["list"];
            $send_result2 = json_decode($send_result2, true);
            list($fansCount) = $send_result2["list"];
            $data = array(
                "ref_date"=>$time." 00:00:00",
                "new_user"=>$fans["new_user"],
                "cancel_user"=>$fans["cancel_user"],
                "pure_user"=>$fans["new_user"] -  $fans["cancel_user"],
                "cumulate_user"=>$fansCount["cumulate_user"],
                "creater_time"=>date("Y-m-d H:i:s")
            );
            $data['appid'] = $appid;
            $ret = M("app_fans")->lock(true)->add($data);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($data,true));
            }else{
                S($appid."fans",$fansCount["cumulate_user"],14400);
            }
    }

    public function getFansCount($appid){
        if(S($appid."fans")){
            return S($appid."fans");
        }else{
            list($info) =  M()->query("SELECT cumulate_user FROM mc_app_fans WHERE appid = '$appid' ORDER  BY  id DESC ");
            return $info['cumulate_user'];
        }

    }

}