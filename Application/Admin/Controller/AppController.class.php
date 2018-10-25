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
use Common\Excel\UploadExcel;

class AppController extends AdminController {
    public function index($r = 20){
        $builder = new AdminListBuilder();
        $page = I("get.page",1,"intval");
        $query = I("get.query");
        list($list,$count) = D("App")->getList($page,$r,$query);
        $group = D("AppGroup")->getList(true);
        $builder
            ->title("公众号列表")
            ->query(["state"=>true,"url"=>U("index"),"placeholder"=>"公众号名称",'value'=>$query])
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

    public function operate($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $id = I("get.id");
        $nick_name = I("get.nick_name");
        list($list,$count) = D("App")->getAppData($id,$page,$r,$stime,$etime);
        $builder
            ->title($nick_name."  运营数据")
            ->query(["url"=>U("operate",array("id"=>$id,"nick_name"=>$nick_name))])
            ->hidequery()
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->powerExport(U("oexcel",array("startime"=>$stime,"endtime"=>$etime,"id"=>$id,"nick_name"=>$nick_name)))
            ->keyText("ref_date","日期")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_feed_read_user","朋友圈打开")
            ->keyText("share_user","分享转发")
            ->keyText("active_percent","活跃度")
            ->keyText("conversation_percent","会话打开")
            ->keyText("open_percent","朋友圈打开")
            ->keyText("share_percent","分享转发")
            ->keyText("responsible","负责人")
            ->keyText("position","部门")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function oexcel(){
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $id = I("get.id");
        $info = D("App")->excelAppData($id,$stime,$etime);
        $nick_name = I("get.nick_name");
        $data = array();
        foreach ($info as $k=>$goods_info){
            $data[$k][nick_name] = $nick_name;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
            $data[$k][int_page_read_user] = $goods_info['int_page_read_user'];
            $data[$k][int_page_from_session_read_user] = $goods_info['int_page_from_session_read_user'];
            $data[$k][int_page_from_feed_read_user] = $goods_info['int_page_from_feed_read_user'];
            $data[$k][share_user] = $goods_info['share_user'];
            $data[$k][active_percent] = $goods_info['active_percent'];
            $data[$k][conversation_percent] = $goods_info['conversation_percent'];
            $data[$k][open_percent] = $goods_info['open_percent'];
            $data[$k][share_percent] = $goods_info['share_percent'];
            $data[$k][responsible] = $goods_info['responsible'];
            $data[$k][position] = $goods_info['position'];
        }
        foreach ($data as $field=>$v){
            if($field == 'nick_name'){
                $headArr[]='公众号';
            }
            if($field == 'ref_date'){
                $headArr[]='日期';
            }
            if($field == 'cumulate_user'){
                $headArr[]='总粉丝';
            }
            if($field == 'pure_user'){
                $headArr[]='净粉丝';
            }
            if($field == 'new_user'){
                $headArr[]='新粉丝';
            }
            if($field == 'int_page_read_user'){
                $headArr[]='图文阅读';
            }
            if($field == 'int_page_from_session_read_user'){
                $headArr[]='会话打开';
            }
            if($field == 'int_page_from_feed_read_user'){
                $headArr[]='朋友圈打开';
            }
            if($field == 'share_user'){
                $headArr[]='分享转发';
            }
            if($field == 'active_percent'){
                $headArr[]='活跃度';
            }
            if($field == 'conversation_percent'){
                $headArr[]='会话打开';
            }
            if($field == 'open_percent'){
                $headArr[]='朋友圈打开';
            }
            if($field == 'share_percent'){
                $headArr[]='分享转发';
            }
            if($field == 'responsible'){
                $headArr[]='负责人';
            }
            if($field == 'position'){
                $headArr[]='部门';
            }
        }
        $filename=$nick_name.time();
        $Excel = new UploadExcel();
        $Excel->getExcel($filename,$headArr,$data);
    }

    public function appGroup(){
        $builder = new AdminListBuilder();
        $builder
            ->title("公众号分组列表")
            ->keyText("","分组")
            ->keyText("","")
            ->display();
    }
}