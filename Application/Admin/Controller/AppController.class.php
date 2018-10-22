<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 23/7/2018
 * Time: 下午 3:09
 */
namespace Admin\Controller;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
use Admin\Controller\AuthorizeController;
use Think\Controller;

class AppController extends Controller {
    public function index(){
        $builder = new AdminListBuilder();
        $builder
            ->title("公众号列表")
            ->powerAdd(U("add"))
            ->display();
    }

    public function add(){
        $component_appid = C('ZTAPPID');
        $Auth = new AuthorizeController();
        $pre_auth_code = $Auth->getPreAuthCode();
        $this->assign('AuthorizeUrl', "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_appid&pre_auth_code=$pre_auth_code&redirect_uri=http://zt.ltthk.top/index.php/wx/AuthorizeCallback");
        $this->display();
    }
}