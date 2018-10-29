<?php
namespace Admin\Model;
use Admin\Model\CommonModel;
class AccountModel extends CommonModel{

    protected  $_validate  = array(
        array('account', 'require','账户不能为空', self::EXISTS_VALIDATE , 'regex'),
        array('account', 'require','账户已存在',  self::EXISTS_VALIDATE, 'unique'),
        array('password', 'require','密码不能为空', self::EXISTS_VALIDATE , 'regex'),
    );

    public  function  fountAdminRoot($map){
        if(M("account")->where(array("level"=>C(ROOT_LEVEL)))->count()==0){
           $map["level"] = C(ROOT_LEVEL);
           $map["creater_time"] = date("Y-m-d H:i:s",time());
           $map["password"] = md5(md5($map["password"]));  //creater_time 不能修改
           return  M("account")->add($map); //创建超级管理员成功
            //不用创建权限目录了   只要是-100000000  全部目录拿出来
        }else{
            return false;
        }
    }

    public  function  getAccountInfo($level){
        if ($level == C(ROOT_LEVEL)){
            $ret = M("account")
                ->where(array("level"=>"1"))
                ->field("account,level,id,if(status = 1,'正常','禁止登录') as status")
                ->select();
            return $ret;
        }
    }

    public function getAccountNameInfo($id){
        list($info) = M()->query("select nick_name,`position` from mc_account where id = $id limit 1");
        if (empty($info)){
            $info['nick_name'] = "系统";
            $info['position'] = "系统";
        }
        return $info;
    }

    public  function  getGameAccountInfo($level){
        if ($level == C(ROOT_LEVEL)){
            $ret = M("")->table("mc_account A")
                ->join("mc_false_room_info B on A.id=B.account_id")
                ->field("A.account,A.level,A.id,if(A.status = 1,'正常','禁止登录') as status")
                ->select();
            return $ret;
        }
    }

    public  function  getProxyAccountInfo($level){
        if ($level == C(ROOT_LEVEL)){
            $ret = M("")->table("mc_account A")
                ->join("mc_user B on A.coin_pool=B.id")
                ->field("A.account,A.level,A.id,if(A.status = 1,'正常','禁止登录') as status,B.username")
                ->select();
            return $ret;
        }
    }
}