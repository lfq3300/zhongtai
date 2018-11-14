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
        $data = M()->query("SELECT id,nick_name,service_type_info,synchron,
                            verify_type_info,principal_name,head_img,
                            DATE_FORMAT(create_time,'%Y-%m-%d') AS  create_time,
                            responsible,group_id 
                            FROM mc_app $where order by id desc limit $row,$r");
        list($count) = M()->query("SELECT count(*) as len FROM mc_app $where");
        return  array($data,$count["len"]);
    }

    public function getEffeList(){
        // 查询 全部公众号 然后请求 公众号数据  必须通过微信公众号认证  获取用户增长的话
        $appList = M()->query(" SELECT id,appid,authorizer_refresh_token,verify_type_info FROM mc_app WHERE  verify_type_info = 0 AND day_synchron = 2 ORDER BY id DESC limit  0,5");
        return $appList;
    }

    public function getHisList(){
        //此处不能做缓存 synchron  = 2 的状态会更新 所以不能缓存 且未同步的
        $appList = M()->query(" SELECT id,appid,authorizer_refresh_token,verify_type_info FROM mc_app WHERE  verify_type_info = 0 AND synchron = 1 ORDER BY id DESC 0,5");
        // 查询 全部公众号 然后请求 公众号数据  必须通过微信公众号认证  获取用户增长的话
        return $appList;
    }

    public function getInfo($id){
        list($data) = M()->query("SELECT account_id,appid,nick_name,head_img,id,responsible,position,group_id FROM mc_app where id = $id limit 1");
        return $data;
    }

    public function getAppid($id){
        list($data) = M()->query("SELECT appid FROM mc_app where id = $id limit 1");
        return $data['appid'];
    }

    public function saveSynchron($appid){
        $res = M("app")->where(array("appid"=>$appid))->save(array("synchron"=>2));
        if($res === false){
            writeLog('error',M()->getLastSql());
        }
    }
    
    public function DelData($appid,$time){
        //删除当前账号 当前time的数据
        $time = $time." 00:00:00";
        M()->execute("delete from mc_app_data where appid = '$appid' and ref_date = '$time'");
        M()->execute("delete from mc_app_fans where appid = '$appid' and ref_date = '$time'");
    }

    //今天已经同步
    public function savaData($appid){
        $data = array("day_synchron"=>1);
        M("app")->where(array("appid"=>$appid))->save($data);
    }



}