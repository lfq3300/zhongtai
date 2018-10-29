<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Common\Upload\Upload;

class AdminController extends Controller{
    public function _initialize(){ //检测是否登录cookieDecrypt
        $account_id = cookieDecrypt(cookie('account_id'));
        $token = cookieDecrypt(cookie('token'));
        $account = cookieDecrypt(cookie('account'));
        $role_id = cookieDecrypt(cookie('role_id'));
        $accountInfo = M("account")->where(array("id"=>$account_id))->find();
        if(empty($accountInfo)||$accountInfo["password"] != $token || $role_id != $accountInfo["role_id"] || $account != $accountInfo["account"]){
            print_r("123456");
            $url = U("Index/index");
            header("Location: $url");
            exit;
        }else{
            //权限检测
            $controllername = CONTROLLER_NAME;
            $actionname = ACTION_NAME;
            //获取当前控制器/方法名
            $thisurl = $controllername."/".$actionname;
            if($actionname == "setMyPwd"){
            }else{
                //判断url是否在权限表中存在
                if($role_id){
                    $sql = "select * from mc_access as A INNER  JOIN mc_admin_menu as B on A.node_id = B.id where B.url = '$thisurl' AND A.role_id = $role_id";
                    $power = M()->query($sql);
                }
                /*直接查询数据库是否有条件*/
//            if(strtoupper($controllername) == "RBAC" && cookieDecrypt(cookie('level'))!=C(ROOT_LEVEL)){
//                $this->error("您没有访问该功能的权限，详情请询问开发人员");
//            }
                if(cookieDecrypt(cookie('level'))!=C(ROOT_LEVEL)){
                    if($thisurl!="Admin/admin" && !$power){
                        $this->error("您没有访问该功能的权限，详情请询问开发人员");
                    }
                }
            }
        }
    }

    public  function  admin(){
          $builder = new AdminListBuilder();
          $builder->display("index");
    }


	public  function  deleteMenu($ids){
        $id = array_unique((array)I('ids', 0));
        $map['id']= array('in', $ids);
        $map2['p_id']= array('in', $ids);
        $ret1 =  M("admin_menu")->where($map)->delete();
        $ret2 =  M("admin_menu")->where($map2)->delete();
        $data = M("account")->field("id")->select();
        foreach ($data as $key =>$item){
            S("menus".$item["id"],NULL);
            S("menuChildrens".$item["id"],NULL);
            S("menuPower".$item["id"],NULL);
        }
        if($ret1 > 0 && $ret2>0){
            $this->success("删除成功",U("index"));
        }else{
            $this->error("删除失败",U("index"));
        }
    }

    public  function  setMyPwd(){
        if(IS_POST){
            $two_new_pwd = I("post.two_new_pwd");
            $new_pwd = I("post.new_pwd");
            if($new_pwd !=$two_new_pwd){
                $this->error("两次输入密码不正确");
            }
            $old_pwd = I("post.old_pwd");
            $account = cookieDecrypt($_COOKIE["account"]);
            $data = M("account")->where(array("account"=>$account,"password"=>md5(md5($old_pwd))))->find();
            if($data){
                $psd = md5(md5($new_pwd));
                $ret = M("account")->where(array("account"=>$account))->save(array("password"=>$psd));
                if($ret>0){
                    $this->success("修改成功",U("Index/index"));
                }else{
                    $this->error("修改失败,请重试");
                }
            }else{
                $this->error("原密码输入不正确");
            }
        }else{
            $builder = new AdminConfigBuilder();
            $builder
                ->title("重置密码")
                ->keyText("old_pwd",array("title"=>"原密码"))
                ->keyText("new_pwd",array("title"=>"新密码"))
                ->keyText("two_new_pwd",array("title"=>"确认密码"))
                ->buttonSubmit()
                ->display();
        }
    }


    public  function  setPass(){
        $id = I("get.id");
        $pwd = md5(md5(C(DEFAULT_PWD)));
        M("account")->where(array("id"=>$id))->save(array("password"=>$pwd));
        $this->Success("重置成功 密码为： ".C(DEFAULT_PWD),U("account"),5);
	}

    public function UploadImg(){
        $up = new Upload();
        $up->UploadImg();
    }
}
?>
