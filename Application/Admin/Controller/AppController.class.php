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
            ->keyText("create_time","授权日期")
            ->powerEdit("edit?id=###","信息编辑")
            ->powerEdit("operate?id=###&nick_name=n#","运营数据")
            ->powerEdit("cancel?id=###","取消授权")
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
                ->keyDisabled("appid",["title"=>"Appid"])
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
        $accountid = cookieDecrypt(cookie('account_id'));
        $this->assign('AuthorizeUrl', "https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid=$component_appid&pre_auth_code=$pre_auth_code&redirect_uri=http://zt.ltthk.top/index.php/wx/AuthorizeCallback?accountid=$accountid");
        $this->display();
    }

    public function operate($r = 20){
        $builder = new AdminListBuilder();
        $page =  I("get.page",1,"intval");
        $stime = I("get.startime",date("Y-m-01", time()),"date");
        $etime = I("get.endtime",date("Y-m-t", time()),"date");
        $key = I("get.query");
        $select = I("get.select",2,"intval");
        $state = $stime||$etime||$key;
        $id = I("get.id");
        $nick_name = I("get.nick_name");

        list($list,$count) = D("appData")->getAppData($id,$page,$r,$stime,$etime,$key,$select);
        $builder
            ->title($nick_name."  运营数据")
            ->query(["placeholder"=>"搜索标题","value"=>$key,"state"=>$state,"url"=>U("operate",array("id"=>$id,"nick_name"=>$nick_name))]);
        if($select == 2){
            $builder->hidequery();
        }
        $builder->queryStarTime($stime)
            ->queryEndTime($etime)
            ->powerExport(U("oexcel",array("startime"=>$stime,"endtime"=>$etime,"id"=>$id,"filename"=>$nick_name,"key"=>$key,"select"=>$select)))
            ->select(["1"=>"详细数据","2"=>"总数据"],["title"=>"查看数据方式","select"=>$select])
            ->keyHidden("select",$select)
            ->keyText("ref_date","日期")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝");
           if($select==1){
               $builder->keyText("title","标题");
           }
        $builder->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_feed_read_user","朋友圈打开")
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
        $key = I("get.key");
        $select = I("get.select");
        $info = D("appData")->excelAppData($id,$stime,$etime,$key,$select);
        $filename = I("get.filename");
        $data = array();
        foreach ($info as $k=>$goods_info){
            $data[$k][nick_name] = $filename;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
            $data[$k][int_page_read_user] = $goods_info['int_page_read_user'];
            $data[$k][int_page_from_session_read_user] = $goods_info['int_page_from_session_read_user'];
            $data[$k][int_page_from_feed_read_user] = $goods_info['int_page_from_feed_read_user'];
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
        $select = I("get.select",1,"intval");
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $state = $stime||$etime||$query;
        list($list,$count) = D("appData")->getAppsData($id,$page,$r,$stime,$etime,true,$select,$query,$queryType);
        $groupName = D("appGroup")->getInfo($id);
        $groupName = $groupName["group_name"];
        $builder
            ->title("公众号分组：$groupName")
            ->query(["placeholder"=>"搜索精准负责人","state"=>$state,'value'=>$query,"url"=>U("guardData",array("id"=>$id))])
            ->queryselect(["1"=>"按时间排序","2"=>"按公众号排序"],["title"=>"排序规则","select"=>$queryType])
            ->hidequery()
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->keyHidden("select",$select)
            ->select(["1"=>"详细数据","2"=>"总数据"],["title"=>"查看数据方式","select"=>$select])
            ->powerExport(U("excelGuardAppData",array("startime"=>$stime,"endtime"=>$etime,"key"=>$id,"filename"=>$groupName,"state"=>true,"select"=>$select,"query"=>$query,"queryType"=>$queryType)))
            ->keyText("ref_date","日期")
            ->keyText("nick_name","公众号")
            ->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝");
            if($select==1){
                $builder->keyText("title","标题");
            }
        $builder->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_feed_read_user","朋友圈打开")
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
        $select = I("get.select");
        $query  = I("get.query");
        $queryType = I("get.queryType");
        $info = D("appData")->excelGuardAppData($key,$stime,$etime,$state,$select,$query,$queryType);
        $filename = I("get.filename");
        $data = array();
        foreach ($info as $k=>$goods_info){
            $data[$k][nick_name] = $goods_info['nick_name'];;
            $data[$k][ref_date] = $goods_info['ref_date'];
            $data[$k][cumulate_user] = $goods_info['cumulate_user'];
            $data[$k][pure_user] = $goods_info['pure_user'];
            $data[$k][new_user] = $goods_info['new_user'];
            if($select == 1){
                $data[$k][title] = $goods_info['title'];
            }
            $data[$k][int_page_read_user] = $goods_info['int_page_read_user'];
            $data[$k][int_page_from_session_read_user] = $goods_info['int_page_from_session_read_user'];
            $data[$k][int_page_from_feed_read_user] = $goods_info['int_page_from_feed_read_user'];
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
            if($field == 'title'){
                $headArr[]='标题';
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
        $select = I("get.select",1,"intval");
        $query = I("get.query");
        $queryType = I("get.queryType",1,"intval");
        $state = $stime||$etime;
        list($list,$count) = D("appData")->getAppsData($responsible,$page,$r,$stime,$etime,false,$select,$query,$queryType);
        $builder
            ->title("公众号负责人：$responsible")
            ->hidequery()
            ->keyHidden("select",$select)
            ->query(["state"=>$state,"url"=>U("responsibleData",array("responsible"=>$responsible))])
            ->queryselect(["1"=>"按时间排序","2"=>"按公众号排序"],["title"=>"排序规则","select"=>$queryType])
            ->queryStarTime($stime)
            ->queryEndTime($etime)
            ->queryEndTime($etime)
            ->select(["1"=>"详细数据","2"=>"总数据"],["title"=>"查看数据方式","select"=>$select])
            ->powerExport(U("excelGuardAppData",array("startime"=>$stime,"endtime"=>$etime,"key"=>$responsible,"filename"=>$responsible,"state"=>false,"select"=>$select,"query"=>$query,"queryType"=>$queryType)))
            ->keyText("ref_date","日期")
            ->keyText("nick_name","公众号");
             if($select==1){
                 $builder->keyText("title","标题");
             }
        $builder->keyText("cumulate_user","总粉丝")
            ->keyText("pure_user","净粉丝")
            ->keyText("new_user","新粉丝")
            ->keyText("int_page_read_user","图文阅读")
            ->keyText("int_page_from_session_read_user","会话打开")
            ->keyText("int_page_from_feed_read_user","朋友圈打开")
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
        list($list,$count) = D("appFans")->getFans($page,$r,$stime,$etime,$query,$queryType);
        $builder
            ->title("粉丝数据")
            ->query(["state"=>!empty($query),"url"=>U("fans"),"placeholder"=>"请搜索：公众号名称",'value'=>$query])
            ->queryselect(["1"=>"公众号名称","2"=>"负责人"],["title"=>"搜索条件","select"=>$queryType])
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

    public function cancel(){
        $builder = new AdminConfigBuilder();
        if ($_POST){
            $res= D("app")->DelApp($_POST['id']);
            if($res){
                $this->success("取消授权成功",U("index"));
            }else{
                $this->error(D("app")->getError());
            }
        }else{
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
}