<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppModel extends CommonModel
{
    //自动验证
    protected $_validate = array(
        array('appid','',"此公众号已经授权,不允许重复授权",0,'unique',1),
        array('responsible','require',"负责人不能为空",1,'',2),
        array('position','require',"岗位不能为空",1,'',2)
    );

    public function addApp($data)
    {

        return M("app")->add($data);
    }


    public function editApp($data,$id){
        return M("app")->where(array("id"=>$id))->save($data);
    }

    public function  getList($page,$r,$query){
        $row = ($page-1) * $r;
        $where = "";
        if(!empty($query)){
            $where = " WHERE nick_name like '%$query%'";
        }
        $data = M()->query("SELECT id,nick_name,service_type_info,
                            verify_type_info,principal_name,head_img,
                            DATE_FORMAT(create_time,'%Y-%m-%d') AS  create_time,
                            responsible,group_id 
                            FROM mc_app $where order by id desc limit $row,$r");
        list($count) = M()->query("SELECT count(*) as len FROM mc_app $where");
        return  array($data,$count["len"]);
    }


    public function getEffeList(){
        if (S("applist")) {
            $appList = S("applist");
        } else {
            $appList = M()->query(" SELECT id,appid,authorizer_refresh_token,verify_type_info FROM mc_app WHERE  verify_type_info = 0");
            // 查询 全部公众号 然后请求 公众号数据  必须通过微信公众号认证  获取用户增长的话
            S("applist", $appList, 14400);
        }
        return $appList;
    }

    public function getInfo($id){
        list($data) = M()->query("SELECT nick_name,head_img,id,responsible,position,group_id FROM mc_app where id = $id limit 1");
        return $data;
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
        $info = M()->query(" SELECT B.cumulate_user,B.new_user,B.pure_user,DATE_FORMAT(A.ref_date,'%Y-%m-%d') as ref_date,'$responsible' as responsible,'$position' as `position`,
                             A.int_page_read_user,A.int_page_read_count,A.int_page_from_session_read_user,A.int_page_from_feed_read_user,A.share_user,A.active_percent,A.conversation_percent,A.open_percent,A.share_percent
                             FROM mc_app_data as A INNER JOIN  mc_app_fans as 
                             B on (A.appid = B.appid and A.ref_date = B.ref_date) 
                             WHERE A.appid = '$appid' $where ORDER  BY A.id desc limit $row,$r");
        list($count) = M()->query(" SELECT count(*) AS len  FROM mc_app_data as A INNER JOIN  mc_app_fans as B on (A.appid = B.appid and A.ref_date = B.ref_date) WHERE A.appid = '$appid' $where ORDER  BY A.id desc");
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
                             WHERE A.appid = '$appid' $where ORDER  BY A.id desc");
        return $info;
    }
}