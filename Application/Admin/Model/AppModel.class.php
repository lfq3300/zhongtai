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

    public function  getList($page,$r,$query,$queryType){
        $row = ($page-1) * $r;
        $where = "";
        if(!empty($query)){
            if($queryType == 1){
                $where = " WHERE nick_name like '%$query%'";
            }else{
                $where = " WHERE responsible like '%$query%'";
            }
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

    public function getHisList(){
        if (S("hislist")) {
            $appList = S("hislist");
        } else {
            $appList = M()->query(" SELECT id,appid,authorizer_refresh_token,verify_type_info FROM mc_app WHERE  verify_type_info = 0 AND synchron = 1 ");
            // 查询 全部公众号 然后请求 公众号数据  必须通过微信公众号认证  获取用户增长的话
            S("hislist", $appList, 14400);
        }
        return $appList;
    }

    public function getInfo($id){
        list($data) = M()->query("SELECT nick_name,head_img,id,responsible,position,group_id FROM mc_app where id = $id limit 1");
        return $data;
    }

    public function saveSynchron($appid){
        $res = M()->query("UPDAE mc_app set synchron = 2 where appid = $appid");
        if($res === false){
            writeLog('error',M()->getLastSql());
            writeLog('data',json_encode($c,true));
        }
    }



}