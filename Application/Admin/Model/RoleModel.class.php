<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 27/6/2018
 * Time: 上午 10:37
 */

namespace Admin\Model;

use Think\Model;

class RoleModel extends Model{
    protected  $_validate  = array(
        array('name', 'require','组名不能为空', self::EXISTS_VALIDATE , 'regex'),
    );

    public  function addGroup($data){
        $data["create_time"] = date("Y-m-d H:i:s");
        $data["update_time"] = date("Y-m-d H:i:s");
        $ret = $this->add($data);
        return $ret;
    }

    public  function  getList($page,$r){
        if (cookieDecrypt(cookie('level'))>0){
            $account_id = cookieDecrypt(cookie('account_id'));
            $data = M()->query("select B.* from  mc_account as A INNER JOIN mc_role AS B ON A.role_id = B.id WHERE A.id = $account_id");
        }else{
            $data = $this->page($page,$r)->select();
        }
        $count = $this->count();
        return array( $data,$count);
    }

    public  function  getGroupInfo($id){
        return $this->where(array("id"=>$id))->find();
    }

    public  function editGroup($id,$data){
       $ret = $this->where(array("id"=>$id))->save($data);
        if($ret === false){
            return false;
        }else{
            return true;
        }
    }
    public  function setPower($roleid = ''){
        if(empty($roleid)){
            $oneData = M("admin_menu")->where(array("p_id"=>0,"level"=>1))->select();
            foreach ($oneData as $key =>$menu) {
                $id = $menu["id"];
                $twoMenu = M("admin_menu")->where(array("p_id" => $id, "level" => 2))->select();
                foreach ($twoMenu as $a => $g) {
                    $twoMenu[$a]["power"] = M("admin_menu")->where(array("p_id" => $g["id"], "level" => 3))->select();
                }
                $oneData[$key]["twoMenu"] = $twoMenu;
            }
            return $oneData;
        }else{
            $oneData = M()->query("SELECT A.* FROM mc_admin_menu as A INNER JOIN (SELECT p_id FROM mc_admin_menu AS A  INNER JOIN mc_power AS B ON A.id = B.menu_id WHERE B.role_id = $roleid GROUP BY p_id) AS B on A.id = B.p_id AND A.level  = 1");
            foreach ($oneData as $key =>$menu){
                $id = $menu["id"];
                $twoMenu =  M()->query("SELECT A.* FROM mc_admin_menu AS A  INNER JOIN mc_power AS B ON A.id = B.menu_id WHERE B.role_id = $roleid AND A.p_id = $id AND A.level = 2");
                foreach ($twoMenu as $a =>$g){
                    $gid = $g["id"];
                    $twoMenu[$a]["power"]  =  M()->query("SELECT A.* FROM mc_admin_menu AS A  INNER JOIN mc_power AS B ON A.p_id = B.menu_id WHERE B.role_id = $roleid AND A.p_id = $gid AND A.level = 3");

                }
                $oneData[$key]["twoMenu"] = $twoMenu;
            }
            return $oneData;
        }

    }

    public function getAccountInfo($accountid,$roleid){
        //获取一个组长所拥有的权限
        return $this->where(array("account_id"=>$accountid,"role_id"=>$roleid))->find();
    }
}