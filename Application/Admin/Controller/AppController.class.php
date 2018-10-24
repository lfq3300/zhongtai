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
    public function index($r = 20){
        $builder = new AdminListBuilder();
        $page = I("get.page");
        list($list,$count) = D("App")->getList($page,$r);
        $group = D("AppGroup")->getList(true);
        $builder
            ->title("公众号列表")
            ->powerAdd(U("add"))
            ->keyText("responsible","负责人")
            ->keyStatus("group_id",'分类',$group)
            ->keyText("nick_name","名称")
            ->keyStatus("service_type_info","类型",[0=>"订阅号",1=>"订阅号",2=>"服务号"])
            ->keyStatus("verify_type_info","认证",[-1=>"未认证",0=>"微信认证",1=>"新浪微博认证",2=>"腾讯微博认证",3=>"资质认证,但名称未认证",4=>"资质认证,但名称未认证",4=>"资质认证,但名称未认证"])
            ->keyText("principal_name","公众号主体")
            ->keyImg("head_img","头像")
            ->keyText("create_time","授权日期")
            ->powerEdit("edit?id=###","信息编辑")
            ->powerEdit("operate?id=###&nick_name=n#","运营数据")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function edit(){
        $model = D("App");
        if($_POST){
            $data = [
                "responsible"=>I("post.responsible"),
                "position"=>I("post.position"),
                "group_id"=>I("post.group_id"),
            ];
            $id = I("post.id");
            if ($model->create($data,2)){
                $ret = $model->editApp($data,$id);
                if($ret!=false){
                    $this->success("修改成功",U("index"));
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $builder = new AdminConfigBuilder();
            $data= $model->getInfo(I("get.id"));
            $group = D("AppGroup")->getList(true);
            $builder
                ->title($data['nick_name']." 信息编辑")
                ->keyDisabled("nick_name",["title"=>"名称"])
                ->keyShowImg("head_img",["title"=>"头像"])
                ->keyHidden("id")
                ->keyText("responsible",["title"=>"负责人"])
                ->keyText("position",["title"=>"岗位"])
                ->keySelect("group_id",["title"=>"公众号分类","select"=>$group,"value"=>$data["group_id"]])
                ->data($data)
                ->buttonSubmit()
                ->display();
        }

    }

    public function add(){
        $component_appid = C('ZTAPPID');
        $Auth = new AuthorizeController();
        $pre_auth_code = $Auth->getPreAuthCode();
        $this->assign('AuthorizeUrl', "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_appid&pre_auth_code=$pre_auth_code&redirect_uri=http://zt.ltthk.top/index.php/wx/AuthorizeCallback");
        $this->display();
    }

    public function operate(){
        $builder = new AdminListBuilder();
        $list = D("App")->getAppData(I("get.id"));
        $builder
            ->title(I("get.nick_name")."  运营数据")
            ->keyText("nick_name","平台名称")
            ->keyText("cumulate_user","总粉丝数")
            ->keyText("new_user","新增粉丝")
            ->keyText("pure_user","净增粉丝")
            ->keyText("int_page_read_user","图文阅读人数")
            ->keyText("int_page_from_session_read_user","会话打开人数")
            ->keyText("int_page_from_friends_read_user","朋友圈打开人数")
            ->keyText("conversation","分享转发次数")
            ->keyText("active_percent","活跃度")
            ->keyText("conversation_percent","会话打开")
            ->keyText("open_percent","朋友圈打开")
            ->keyText("share_percent","分享转发")
            ->display();
    }

}