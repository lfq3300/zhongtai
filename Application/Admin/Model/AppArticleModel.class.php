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
}