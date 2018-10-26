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
        $data = $this->page($page,$r)->select();
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
    public  function setPower(){
        $oneData = M("admin_menu")->where(array("p_id"=>0,"level"=>1))->select();
        foreach ($oneData as $key =>$menu){
            $id = $menu["id"];
            $twoMenu =  M("admin_menu")->where(array("p_id"=>$id,"level"=>2))->select();
            foreach ($twoMenu as $a =>$g){
                $twoMenu[$a]["power"] = M("admin_menu")->where(array("p_id"=>$g["id"],"level"=>3))->select();
            }
            $oneData[$key]["twoMenu"] = $twoMenu;
        }
        return $oneData;
    }
}