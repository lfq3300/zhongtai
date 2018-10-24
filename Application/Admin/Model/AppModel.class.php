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

    public function  getList(){
        $data = M()->query("SELECT id,nick_name,service_type_info,verify_type_info,principal_name,head_img,DATE_FORMAT(create_time,'%Y-%m-%d') AS  create_time,responsible,group_id FROM mc_app order by id desc");
        $count = M("app")->count();
        return  array($data,$count);
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

    public function getAppData($id){
        list($app) = M()->query("SELECT appid FROM mc_app where id = $id limit 1");
        $appid = $app["appid"];
        $info = M()->query("select * FROM mc_app_data WHERE appid = '$appid' ORDER BY  id desc");
        return $info;
    }

}