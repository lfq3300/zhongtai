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
        $queryType = I("get.queryType",1,"intval");
        list($list,$count) = D("App")->getList($page,$r,$query,$queryType);
        $group = D("AppGroup")->getList(true);
        $url = U("App/index",array("page"=>$page,"query"=>$query,"queryType"=>$queryType));
        AddactionLog("查看单公众号管理：<a href='".$url."' target='_blank' >点击查看详细</a>");
        $builder
            ->title("单公众号管理")
            ->query(["state"=>!empty($query),"url"=>U("index"),"placeholder"=>"请搜索：公众号名称",'value'=>$query])
            ->queryselect(["1"=>"公众号名称","2"=>"负责人"],["title"=>"搜索条件","select"=>$queryType])
            ->powerAdd(U("add"))
            ->keyText("responsible","负责人")
            ->keyStatus("group_id",'分组',$group)
            ->keyText("nick_name","名称")
            ->keyStatus("service_type_info","类型",[0=>"订阅号",1=>"订阅号",2=>"服务号"])
            ->keyStatus("verify_type_info","认证",[-1=>"未认证",0=>"微信认证",1=>"新浪微博认证",2=>"腾讯微博认证",3=>"资质认证,但名称未认证",4=>"资质认证,但名称未认证",4=>"资质认证,但名称未认证"])
            ->keyText("principal_name","公众号主体")
            ->keyImg("head_img","头像")
            ->keyStatus("synchron","同步数据",[1=>"已加入同步计划",2=>"已完成同步",3=>"已加入同步计划"])
            ->keyText("create_time","授权日期")
            ->powerEdit("edit?id=###","信息编辑")
            ->powerEdit("operate?id=###&nick_name=n#","运营数据")
            ->powerEdit("article?id=###&nick_name=n#","文章数据")
            ->powerEdit("oneFans?id=###&nick_name=n#","粉丝数据")
            ->powerEdit("cancel?id=###","取消授权")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function article($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $nick_name = I("get.nick_name");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $id = I("get.id");
        $query = I("get.query");
        $state = $query||$stime||$etime;
        list($list,$count) = D("AppArticle")->getList($page,$r,$stime,$etime,$id,$query);
        $builder->title($nick_name." 文章数据")
                ->query(["state"=>$state,"placeholder"=>"搜索文章标题","value"=>$query,"url"=>U("article",array("id"=>$id,"nick_name"=>$nick_name))])
                ->queryStarTime($stime)
                ->queryEndTime($etime)
                ->keyText("ref_date","日期")
                ->keyText("title","标题")
                ->keyText("int_page_read_user","总阅读")
                ->keyText("cumulate_user","总粉丝")
                ->keyText("int_page_from_session_read_user","会话")
                ->keyText("int_page_from_friends_read_user","朋友圈")
                ->keyText("share_user","分享转发")
            //    ->keyText("int_page_from_kanyikan_read_user","看一看")
                ->keyText("active_percent","活跃度",["added"=>'%'])
                ->keyText("responsible","负责人")
                ->keyText("position","岗位")
                ->data($list)
                ->pagination($count,$r)
                ->display();

    }

    public function synchro(){
        if ($_POST){
            $appid = M("app")->where(array("id"=>I('post.id'),"synchron"=>1))->find();
            if (empty($appid)){
                $this->error("公众号已经同步");
            }else{
//                M("app")->where(array("id"=>I('post.id')))->save(array("synchron"=>3));
                $this->success("添加成功,等待同步",U("index"));
            }
        }else{
            $builder = new AdminConfigBuilder();
            list($len) = M()->query("select count(*) as len FROM mc_app where synchron = 2");
            $builder
                ->title("数据同步")
                ->keyHidden("id")
                ->formtitle("从2018-03-01开始至".date("Y-m-d",strtotime("-1 day"))."结束.
              <br/><br/> 单个公众号同步时间为 45 分钟.
              <br/><br/>目前等待同步公众号数量:<span style='color:red;padding-left:10px'>".$len['len']."个</span> 
              <br><br/>点击确定加入公众号同步列队")
                ->data(array("id"=>I("get.id")))
                ->buttonSubmit()
                ->display();
        }
    }

    public function edit(){
        $model = D("App");
        if($_POST){
            $nick_name = D("Account")->getNickName(I("post.account_id"));
            $data = [
                "group_id"=>I("post.group_id"),
                "account_id"=>I("post.account_id"),
                "responsible"=>$nick_name
            ];
            $oldnick_name = D("Account")->getNickName(I("post.oldaccount_id"));
            $ogroupName = D("AppGroup")->getInfo(I("post.hidegroup_id"));
            $ngroupName = D("AppGroup")->getInfo(I("post.group_id"));
            $id = I("post.id");
            if ($model->create($data,2)){
                $ret = $model->editApp($data,$id);
                if($ret!=false){
                    AddactionLog("编辑公众号信息：负责人 ".$oldnick_name."更换为".$nick_name.", 类型 ：".$ogroupName["group_name"]." 更换为".$ngroupName["group_name"]);
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
            $userList = D("Account")->getUserGroupList(cookieDecrypt(cookie("account_id")));
            $select = i_array_column($userList,'nick_name','id');
            $builder
                ->title($data['nick_name']." 信息编辑")
                ->keyDisabled("appid",["title"=>"Appid"])
                ->keyDisabled("nick_name",["title"=>"名称"])
                ->keyShowImg("head_img",["title"=>"头像"])
                ->keyHidden("id");
            if (cookieDecrypt(cookie("level")) == 2){
                $builder->keySelect("account_id",["title"=>"负责人","select"=>$select,"value"=>$data["account_id"]]);
            }else{
                $builder->keyDisabled("responsible",["title"=>"负责人"]);
            }
            $builder->keySelect("group_id",["title"=>"公众号分类","select"=>$group,"value"=>$data["group_id"]])
                ->keyHidden("oldaccount_id",$data["account_id"])
                ->keyHidden("hidegroup_id",$data["group_id"])
                ->data($data)
                ->buttonSubmit()
                ->display();
        }

    }

    public function add(){
        $component_appid = C('ZTAPPID');
        $Auth = new AuthorizeController();
        $pre_auth_code = $Auth->getPreAuthCode();
        $accountid = cookieDecrypt(cookie('account_id'));
        $this->assign('AuthorizeUrl', "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_appid&pre_auth_code=$pre_auth_code&redirect_uri=http://zt.ltthk.top/index.php/wx/AuthorizeCallback?accountid=$accountid");
        $this->display();
    }

    public function operate($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $state = $stime||$etime;
        $id = I("get.id");
        list($list,$count) = D("appData")->getAppData($id,$page,$r,$stime,$etime);
        $nick_name = I("get.nick_name");
        $url = U("App/operate",array("id"=>$id,"page"=>$page,"startime"=>$stime,"endtime"=>$etime,"nick_name"=>urldecode($nick_name)));
        AddactionLog("查看".$nick_name."运营数据：<a href='".$url."' target='_blank' >点击查看详细</a>");
        $builder
            ->title($nick_name."  运营数据")
            ->query(["placeholder"=>"搜索标题","state"=>$state,"url"=>U("operate",array("id"=>$id,"nick_name"=>$nick_name))])
            ->hidequery()
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->powerExport(U("oexcel",array("startime"=>$stime,"endtime"=>$etime,"id"=>$id,"filename"=>$nick_name)))
            ->keyText("ref_date","日期")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_friends_read_user","朋友圈打开")
            ->keyText("share_user","分享转发")
            ->keyText("active_percent","活跃度",["added"=>'%'])
            ->keyText("conversation_percent","会话打开",["added"=>'%'])
            ->keyText("open_percent","朋友圈打开",["added"=>'%'])
            ->keyText("share_percent","分享转发",["added"=>'%'])
            ->keyText("responsible","负责人")
            ->keyText("position","岗位")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function oexcel(){
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $id = I("get.id");
        $info = D("appData")->excelAppData($id,$stime,$etime);
        $filename = I("get.filename");
        AddactionLog("导出 ".$filename." ".$stime."到".$etime."数据");
        $data = array();
        foreach ($info as $k=>$goods_info){
            $data[$k][nick_name] = $filename;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
            $data[$k][int_page_read_user] = $goods_info['int_page_read_user'];
            $data[$k][int_page_from_session_read_user] = $goods_info['int_page_from_session_read_user'];
            $data[$k][int_page_from_friends_read_user] = $goods_info['int_page_from_friends_read_user'];
            $data[$k][share_user] = $goods_info['share_user'];
            $data[$k][active_percent] = $goods_info['active_percent']."%";
            $data[$k][conversation_percent] = $goods_info['conversation_percent']."%";
            $data[$k][open_percent] = $goods_info['open_percent']."%";
            $data[$k][share_percent] = $goods_info['share_percent']."%";
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
            if($field == 'int_page_from_friends_read_user'){
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
                $headArr[]='岗位';
            }
        }
        $filename=$filename.time();
        $Excel = new UploadExcel();
        $Excel->getExcel($filename,$headArr,$data);
    }

    public function appGroup($r = 20){
        $builder = new AdminListBuilder();
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $page = I("get.page",1,"intval");
        list($list,$count) = D("appData")->getGroupList($page,$r,$query,$queryType);
        $url = U("App/appGroup",array("page"=>$page,"query"=>$query,"queryType"=>$queryType));
        AddactionLog("查看多公众号管理：<a href='".$url."' target='_blank' >点击查看详细</a>");
        $builder
            ->title("多公众号管理")
            ->query(["state"=>true,"url"=>U("appGroup"),"placeholder"=>"按搜索条件搜索",'value'=>$query])
            ->queryselect(["1"=>"公众号分组","2"=>"精准负责人姓名"],["title"=>"搜索条件","select"=>$queryType]);
            if($queryType == 1){
                $builder->keyText("group_name",'分组')
                        ->keyText("len","公众号数量")
                        ->powerEdit("guardData?id=###","运营数据");
            }else{
                $builder->keyText("responsible",'负责人')
                        ->keyText("len","公众号数量")
                        ->powerEdit("responsibleData?responsible=r#","运营数据");
            }
        $builder
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    //按分组明计算
    public function guardData($r = 20){
        $builder = new AdminListBuilder();
        $id = I("get.id");
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $state = $stime||$etime||$query;
        $url = U("App/guardData",array("id"=>$id,"page"=>$page,"startime"=>$stime,"endtime"=>$etime,"query"=>$query,"queryType"=>$queryType));
        AddactionLog("查看公众号分组数据：<a href='".$url."' target='_blank' >点击查看详细</a>");
        list($list,$count) = D("appData")->getAppsData($id,$page,$r,$stime,$etime,true,$query,$queryType);
        $groupName = D("appGroup")->getInfo($id);
        $groupName = $groupName["group_name"];
        $builder
            ->title("公众号分组：$groupName")
            ->query(["placeholder"=>"搜索精准负责人","state"=>$state,'value'=>$query,"url"=>U("guardData",array("id"=>$id))])
            ->queryselect(["1"=>"按时间排序","2"=>"按公众号排序"],["title"=>"排序规则","select"=>$queryType])
            ->hidequery()
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->powerExport(U("excelGuardAppData",array("startime"=>$stime,"endtime"=>$etime,"key"=>$id,"filename"=>$groupName,"state"=>true,"query"=>$query,"queryType"=>$queryType)))
            ->keyText("ref_date","日期")
            ->keyText("nick_name","公众号")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_friends_read_user","朋友圈打开")
            ->keyText("share_user","分享转发")
            ->keyText("active_percent","活跃度",["added"=>'%'])
            ->keyText("conversation_percent","会话打开",["added"=>'%'])
            ->keyText("open_percent","朋友圈打开",["added"=>'%'])
            ->keyText("share_percent","分享转发",["added"=>'%'])
            ->keyText("responsible","负责人")
            ->keyText("position","岗位")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }


    public function excelGuardAppData(){
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $key = I("get.key");
        $state = I("get.state");
        $query  = I("get.query");
        $queryType = I("get.queryType");
        $info = D("appData")->excelGuardAppData($key,$stime,$etime,$state,$query,$queryType);
        $filename = I("get.filename");
        AddactionLog("导出 ".$filename." ".$stime."到".$etime."数据");
        $data = array();
        foreach ($info as $k=>$goods_info){
            $data[$k][nick_name] = $goods_info['nick_name'];;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
            $data[$k][int_page_read_user] = $goods_info['int_page_read_user'];
            $data[$k][int_page_from_session_read_user] = $goods_info['int_page_from_session_read_user'];
            $data[$k][int_page_from_friends_read_user] = $goods_info['int_page_from_friends_read_user'];
            $data[$k][share_user] = $goods_info['share_user'];
            $data[$k][active_percent] = $goods_info['active_percent']."%";
            $data[$k][conversation_percent] = $goods_info['conversation_percent']."%";
            $data[$k][open_percent] = $goods_info['open_percent']."%";
            $data[$k][share_percent] = $goods_info['share_percent']."%";
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
            if($field == 'int_page_from_friends_read_user'){
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
                $headArr[]='岗位';
            }
        }
        $filename=$filename.time();
        $Excel = new UploadExcel();
        $Excel->getExcel($filename,$headArr,$data);
    }

    //负责人计算
    public function responsibleData($r = 20){
        $builder = new AdminListBuilder();
        $responsible = I("get.responsible");
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $state = $stime||$etime;
        list($list,$count) = D("appData")->getAppsData($responsible,$page,$r,$stime,$etime,false,$query,$queryType);
        $url = U("App/responsibleData",array("responsible"=>$responsible,"page"=>$page,"startime"=>$stime,"endtime"=>$etime,"query"=>$query,"queryType"=>$queryType));
        AddactionLog("查看公众号负责人 $responsible 数据： <a href='".$url."' target='_blank'>点击查看详细</a>");
        $builder
            ->title("公众号负责人：$responsible")
            ->hidequery()
            ->query(["state"=>$state,"url"=>U("responsibleData",array("responsible"=>$responsible))])
            ->queryselect(["1"=>"按时间排序","2"=>"按公众号排序"],["title"=>"排序规则","select"=>$queryType])
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->queryEndTime($etime)
            ->powerExport(U("excelGuardAppData",array("startime"=>$stime,"endtime"=>$etime,"key"=>$responsible,"filename"=>$responsible,"state"=>false,"query"=>$query,"queryType"=>$queryType)))
            ->keyText("ref_date","日期")
            ->keyText("nick_name","公众号")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_friends_read_user","朋友圈打开")
            ->keyText("share_user","分享转发")
            ->keyText("active_percent","活跃度",["added"=>'%'])
            ->keyText("conversation_percent","会话打开",["added"=>'%'])
            ->keyText("open_percent","朋友圈打开",["added"=>'%'])
            ->keyText("share_percent","分享转发",["added"=>'%'])
            ->keyText("responsible","负责人")
            ->keyText("position","岗位")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function fans($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $url = U("App/fans",array("page"=>$page,"startime"=>$stime,"endtime"=>$etime,"query"=>$query,"queryType"=>$queryType));
        AddactionLog("查看粉丝： <a href='".$url."' target='_blank'>点击查看详细</a>");
        list($list,$count) = D("appFans")->getFans($page,$r,$stime,$etime,$query,$queryType);
        $builder
            ->title("粉丝数据")
            ->query(["state"=>!empty($query),"url"=>U("fans"),"placeholder"=>"请搜索：公众号名称",'value'=>$query])
            ->queryselect(["1"=>"公众号名称","2"=>"负责人"],["title"=>"搜索条件","select"=>$queryType])
            ->powerExport(U("excelfans",array("startime"=>$stime,"endtime"=>$etime,"queryType"=>$queryType,"query"=>$query)))
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->keyText("nick_name","公众号")
            ->keyText("ref_date","日期")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("responsible","负责人")
            ->keyText("position","职位")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }

    public function oneFans($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $url = U("App/oneFans",array("page"=>$page,"startime"=>$stime,"endtime"=>$etime));
        $id = I("get.id");
        $nick_name = I("get.nick_name");
        AddactionLog("查看".$nick_name."粉丝： <a href='".$url."' target='_blank'>点击查看详细</a>");
        list($list,$count) = D("appFans")->getoneFans($page,$r,$stime,$etime,$id);
        $builder
            ->title($nick_name."粉丝数据")
            ->query(["state"=>true,"url"=>U("oneFans",array("id"=>$id,"nick_name"=>$nick_name))])
            ->hidequery()
            ->powerExport(U("excelonfans",array("startime"=>$stime,"endtime"=>$etime,"id"=>$id)))
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->keyText("nick_name","公众号")
            ->keyText("ref_date","日期")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("responsible","负责人")
            ->keyText("position","职位")
            ->data($list)
            ->pagination($count,$r)
            ->display();
    }


    public function excelonfans(){
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $id = I("get.id");
        $nick_name = I("get.nick_name");
        $list = D("appFans")->getonFansAll($stime,$etime,$id);
        AddactionLog("导出 $nick_name".$stime."到".$etime."的 粉丝 数据");
        $filename ="粉丝数据";
        $data = array();
        foreach ($list as $k=>$goods_info){
            $data[$k][nick_name] = $goods_info['nick_name'];;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
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
            if($field == 'responsible'){
                $headArr[]='负责人';
            }
            if($field == 'position'){
                $headArr[]='岗位';
            }
        }
        $filename=$filename.time();
        $Excel = new UploadExcel();
        $Excel->getExcel($filename,$headArr,$data);
    }


    public function excelfans(){
        $stime = I("get.startime");
        $etime = I("get.endtime");
        $query  = I("get.query");
        $queryType = I("get.queryType");
        $list = D("appFans")->getFansAll($stime,$etime,$query,$queryType);
        empty($query)?"默认":$query;
        $filename = $queryType == 1?"公众号名称":"负责人";
        AddactionLog("导出 搜索条件：".$filename." 为：".$query." ".$stime."到".$etime."的 粉丝 数据");
        $filename ="粉丝数据";
        $data = array();
        foreach ($list as $k=>$goods_info){
            $data[$k][nick_name] = $goods_info['nick_name'];;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
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
            if($field == 'responsible'){
                $headArr[]='负责人';
            }
            if($field == 'position'){
                $headArr[]='岗位';
            }
        }
        $filename=$filename.time();
        $Excel = new UploadExcel();
        $Excel->getExcel($filename,$headArr,$data);
    }

    public function cancel(){
        if ($_POST){
            $appid = M("app")->where(array("id"=>I("post.id")))->find();
            $appid = $appid["appid"];
            M("app_fans")->where(array("appid"=>$appid))->delete();
            M("app_data")->where(array("appid"=>$appid))->delete();
            $appname =  M("app")->where(array("id"=>I("post.id")))->find();
            M("app")->where(array("id"=>I("post.id")))->delete();
            AddactionLog("取消授权公众号".$appname["nick_name"]);
            $this->success("取消授权成功",U("index"));
        }else{
            $builder = new AdminConfigBuilder();
            $id = I("get.id");
            $builder
                ->title("取消授权详细")
                ->keyHidden("id")
                ->formtitle("<span style='color: red;'> 取消授权后 平台将不再获取公众号数据,并将此公众号在本平台数据全部删除 </span>. <br/> 此页面取消授权 是在本平台上取消, 还需管理员登陆微信公众号取消")
                ->keyCancel()
                ->buttonSubmit('','确定取消授权')
                ->data(array("id"=>$id))
                ->display();
        }

    }

    public function data($r = 20){
        $page =  I("get.page",1,"intval");
        //   $stime = I("get.startime",date("Y-m-01", time()),"date");
        //   $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $builder = new AdminListBuilder();
        $query = I("get.query");
        $list = D("appFans")->getFansData($page,$r,$query);
        // $state = $query||$stime||$etime;
        $builder
            ->title("单公众号数据")
            ->query(["state"=>!empty($query),"url"=>U("data"),"placeholder"=>"请搜索：公众号名称",'value'=>$query])
//            ->queryStarTime($stime)
//            ->queryEndTime($etime)
            ->keyText("ref_data","时间")
            ->keyText("ranking","排名")
            ->keyText("nick_name","公众号")
            ->keyUserData("cumulate_user","总粉丝/同比")
            ->keyUserData("new_user","新粉/同比")
            ->keyUserData("pure_user","掉粉/同比")
            ->keyUserData("increase_user","增长/同比")
            //  ->data($data)
            ->display();
    }
}