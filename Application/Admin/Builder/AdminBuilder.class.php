<?php
namespace Admin\Builder;
use Admin\Controller\AdminController;
/**
 * AdminBuilder：快速建立管理页面。
 *
 * 为什么要继承AdminController？
 * 因为AdminController的初始化函数中读取了顶部导航栏和左侧的菜单，
 * 如果不继承的话，只能复制AdminController中的代码来读取导航栏和左侧的菜单。
 * 这样做会导致一个问题就是当AdminController被官方修改后AdminBuilder不会同步更新，从而导致错误。
 * 所以综合考虑还是继承比较好。
 *
 * Class AdminBuilder
 * @package Admin\Builder
 */
abstract class AdminBuilder extends AdminController{

	 // public function _initialize()
   //  {
   //      $adminid = $_SESSION['account_id'];
   //      if(empty($adminid)){
   //          $url = U("Index/index");
   //          header("Location: $url");
   //          exit;
   //      }
   //  }

    public function display($templateFile='',$charset='',$contentType='',$content='',$prefix='') {
        //  重写AdminController中得_initialize(),防止重复执行AdminController中的_initialize()
        //获取模版的名称
        $template = dirname(__FILE__) . '/../View/default/Builder/' . $templateFile . '.html';
        $adminlevel = cookieDecrypt(cookie("level"));
        $role_id = cookieDecrypt(cookie('role_id'));
        $time = C("SESSION_TIME");
        if($adminlevel == C(ROOT_LEVEL)){
            $role_id = "root";
        }
        if(!(S("menus".$role_id)&&S("menuChildrens".$role_id))){
            if($adminlevel == C(ROOT_LEVEL)){ //判断超级用户
                $role_id = "root";
                $sql = "SELECT title,id FROM mc_admin_menu WHERE p_id = 0 AND hide = 0 ORDER BY sort DESC";
                $sqldata = M()->query($sql); //获取一级目录
                $sql = "SELECT A.`id`,A.`title`,A.`url`,A.`p_id` FROM mc_admin_menu A LEFT JOIN (SELECT id,title,sort FROM mc_admin_menu WHERE p_id =0 AND hide = 0) B ON A.`p_id` = B.id WHERE A.`hide` = 0  AND A.`p_id` != 0 ORDER BY B.sort,A.`sort`,A.`id` DESC";
                $sqldata2 = M()->query($sql); //获取二级目录
            }else{
                //不是超级用户
                //根据 $role_id 获取一级目录
                $sql = "select B.title,B.id from mc_access as A INNER JOIN mc_admin_menu as B on A.node_id = B.id WHERE  A.role_id = $role_id AND A.level = 1";
                $sqldata = M()->query($sql);
                $sql = "select B.`id`,B.`title`,B.`url`,B.`p_id` from mc_access as A INNER JOIN mc_admin_menu as B on A.node_id = B.id WHERE  A.role_id = $role_id AND A.level = 2";
                $sqldata2 = M()->query($sql);
            }
            $this->assign("menus",$sqldata);
            $this->assign("menuChildrens",$sqldata2);
            S("menus".$role_id,$sqldata,$time);  //在设置账户权限的时候清空
            S("menuChildrens".$role_id,$sqldata2,$time); //在设置账户权限的时候清空
        }else{
            $this->assign("menus",S("menus".$role_id));
            $this->assign("menuChildrens",S("menuChildrens".$role_id));
        }
        $this->assign("adminlUrl",C("ADMIN_URL"));
        parent::display($template);
    }

    protected function compileHtmlAttr($attr) {
        $result = array();
        foreach($attr as $key=>$value) {
            $value = htmlspecialchars($value);
            $result[] = "$key=\"$value\"";
        }
        $result = implode(' ', $result);
        return $result;
    }

}

?>
