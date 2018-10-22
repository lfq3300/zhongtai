<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends Controller{
	public function index($r = 50){
        cookie('account',NULL);
        cookie('token',NULL);
        cookie('account_id',NULL);
        cookie('level',NULL);
        cookie('account',NULL);
        cookie('account_id_token',NULL);
        $this->display();
	}

	public  function  setRoot(){
		$this->display();
	}

	public  function  fountAdminRoot(){
        if(M("account")->where(array("level"=>C(ROOT_LEVEL)))->count()>0){
            return returnJson(0,"hehe","hehe");
        }else{
            $map['account'] = I('post.account');
            $map['password'] = I('post.password');
            $model = D("Account");
            $ret = $model->fountAdminRoot($map);
            if ($ret){
                returnJson(1);
			}else{
                returnJson(1,$model->getError());
			}
		}
	}
	public function login(){
		$accountInfo = M("account")->where(array("account"=>I('post.account')))->find();
        if(empty($accountInfo)){
            returnJson("0","账户不存在");
		}else{
            if(md5(md5(I("post.password"))) == $accountInfo["password"]){
                //登陆成功后
                M("account")->where(array("account"=>I('post.account')))->save(array("login_time"=>date("Y-m-d H:i:s"),"logincount"=>$accountInfo["logincount"]+1,"loginip"=>get_ip()));
                $time = C("SESSION_TIME");
                cookie("account",cookieEncrypt($accountInfo["account"]),86400);
                cookie("account_id",cookieEncrypt($accountInfo["id"]),86400);
                cookie("token",cookieEncrypt($accountInfo["password"]),86400);
                cookie('level', cookieEncrypt($accountInfo["level"]));
                cookie('role_id', cookieEncrypt($accountInfo["role_id"]));
                returnJson(1);
            }else{
                returnJson("0","密码错误,请注意字母大小写、符号、空格",md5(md5(I('post.password')).md5($accountInfo["creater_time"])));
            }
        }
	}

	public function exitlogin(){
        $url = U("Index/index");
        header("Location: $url");
	}

	public  function  menuIndex(){
        $time = C("SESSION_TIME");
        cookie('pid', I('post.pid'),$time);
        cookie('cid', I('post.cid'),$time);
        returnJson(1);
	}
}
?>
