<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 27/6/2018
 * Time: 上午 10:03
 */
namespace Admin\Controller;
use Admin\Builder\AdminListBuilder;
use Admin\Builder\AdminConfigBuilder;
class RbacController extends AdminController{
    //用户组管理 暂时不做删除功能
    public  function  index($r = 20)
    {
        $builder = new AdminListBuilder();
        $model = D("Role");
        $page = I("post.page",1,"intval");
        list($data,$count) = $model->getList($page,$r);
        $builder
            ->title("角色组管理")
            ->powerAdd(U("addGroup"))
            ->keyText("id","ID")
            ->keyText("name","组名")
            ->keyText("code_name","代码编号")
            ->keyText("description","描述")
            ->keyDoAction("editGroup?id=###","编辑")
            ->keyDoAction("powerGroup?id=###","授权")
            ->keyDoAction("userGroup?id=###","用户列表")
            ->data($data)
            ->pagination($count,$r)
            ->display();
    }

    public  function addGroup(){
        $builder = new AdminConfigBuilder();
        if($_POST){
            $data = array(
                "name"=>I("post.name"),
                "status"=>I("post.status"),
                "description"=>I("post.description"),
                "code_name"=>I("post.code_name")
            );
            $model = D("Role");
            if($model->create($data)){
               $ret =  $model->addGroup($data);
               if($ret){
                   $builder->success("添加成功",U("index"));
               }else{
                   $builder->error($model->getError());
               }
            }else{
                $builder->error($model->getError());
            }
        }else{
            $builder
                ->title("添加角色组")
                ->keyText("name",array("title"=>"组名"))
                ->keyText("code_name",array("title"=>"代码编号"))
                ->keySelect("status",array("select"=>array("1"=>"正常","2"=>"禁止登陆"),"title"=>"状态"))
                ->keyTextarea("description",array("title"=>"描述"))
                ->buttonSubmit()
                ->display();
        }
    }

    public  function  editGroup(){
        $builder = new AdminConfigBuilder();
        if($_POST){
            $data = array(
                "name"=>I("post.name"),
                "status"=>I("post.status"),
                "description"=>I("post.description"),
                "code_name"=>I("post.code_name")
            );
            $id = I("post.id");
            $model = D("Role");
            if($model->create($data)){
                $ret = $model->editGroup($id,$data);
                if($ret){
                    $builder->success("修改成功",U("index"));
                }else{
                    $builder->error($model->getError());
                }
            }else{
                $builder->error($model->getError());
            }
        }else{
            $model = D("Role");
            $data = $model->getGroupInfo(I("get.id"));
            $builder
                ->keyHidden("id")
                ->title("编辑角色组")
                ->keyText("name",array("title"=>"组名"))
                ->keyText("code_name",array("title"=>"代码编号"))
                ->keySelect("status",array("title"=>"状态","select"=>array("1"=>"正常","2"=>"禁止登陆")))
                ->keyTextarea("description",array("title"=>"描述"))
                ->buttonSubmit()
                ->data($data)
                ->display();
        }
    }

    public  function  userGroup(){
        $builder = new AdminListBuilder();
        $model = D("Role");
        $groupid = I("get.id");
        $data = $model->getGroupInfo($groupid);
        $name = $data["name"];
        $code_name = $data["code_name"];
        $data = M("account")->where(array("role_id"=>$groupid))->select();
        $builder
            ->title($name." 用户管理")
        ->powerAdd(U("addAccount",array("id"=>$groupid)));
        $builder->keyText("account","账号");
        $builder
            ->keyStatus("status","账户状态",array("0"=>"禁止登陆","1"=>"正常"))
            ->keyText("login_time","最后登陆")
            ->keyText("logincount","登陆次数")
            ->keyDoActionEdit("setLoginOff?id=###&pid=$groupid","禁止登录")
            ->keyDoActionEdit("setLoginOn?id=###&pid=$groupid","允许登录")
            ->keyDoActionEdit("setPass?id=###&pid=$groupid","重置密码")
            ->data($data)
            ->display();
    }

    public  function  addAccount(){
        if(IS_POST){
            $data = array(
                "account"=>I("post.account"),
                "password"=>md5(md5(I("post.password"))),
                "add_time"=>date("Y-m-d H:i:s"),
                "role_id" =>I("post.role_id")
            );
            $groupid =I("post.role_id");
            $groupinfo = D("Role")->getGroupInfo($groupid);
            if($groupinfo["code_name"] == "proxycharge"){
                $data["proxyrechar_id"] = I("post.userid");
                $data["level"] = 5;
            }
            if ($data["level"] ==C(ROOT_LEVEL)){
                $this->error("权限不正确 ,请刷新页面后重试");
            }
            $model = D("account");
            if($model->create($data)){
                $ret =  D("account")->add($data);
                if($ret){
                    $this->success("添加成功",U("userGroup",array("id"=>I("post.role_id"))));
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $groupid = I("get.id");
            $model = D("Role");
            $data = $model->getGroupInfo($groupid);
            $name = $data["name"];
            $code_name = $data["code_name"];
            $builder = new AdminConfigBuilder();
            $builder->title("添加".$name."账号")
                ->keyHidden("role_id")
                ->keyText("account",array("title"=>"账号"))
                ->keyText("password",array("title"=>"密码"))
                ->data(array("role_id"=>$groupid))
                ->display();
        }
    }

    public  function  setPass(){
        $id = I("get.id");
        $pid = I("get.pid");
        $pwd = md5(md5(C(DEFAULT_PWD)));
        M("account")->where(array("id"=>$id))->save(array("password"=>$pwd));
        $this->Success("重置成功 密码为： ".C(DEFAULT_PWD),U("userGroup",array("id"=>$pid)),5);
    }

    public  function  setLoginOff(){
        $id = I("get.id");
        $pid = I("get.pid");
        M("account")->where(array("id"=>$id))->save(array("status"=>"0"));
        $this->success("修改成功",U("userGroup",array("id"=>$pid)));
    }

    public  function  setLoginOn(){
        $id = I("get.id");
        $pid = I("get.pid");
        M("account")->where(array("id"=>$id))->save(array("status"=>"1"));
        $this->success("修改成功",U("userGroup",array("id"=>$pid)));
    }

    public  function  node(){
        $model = D("AdminMenu");
        $builder = new AdminListBuilder();
        $data = $model->getFirstMenuList();
        $builder
            ->title("菜单管理")
            ->newButton(U("addMenu"))
           // ->deleteButton(U("deleteMenu"))
            ->keyLink('title','标题',"nextMenu?id=###")
            ->keyText('p_title','所属目录')
            ->keyText('url','连接')
            ->keyText('sort','排序')
            ->keyStatus('hide','是否隐藏',array("0"=>'未隐藏',"1"=>"隐藏"))
            ->keyDoActionEdit("editMenu?id=###")
            ->data($data)
            ->display();
    }

    public function addMenu(){
        $model = D("Admin/AdminMenu");
        if(IS_POST){
            $next = I("post.next");
            $data  =
                array(
                    "title" =>I("post.title"),
                    "url"	=>I("post.url"),
                    "hide"	=>I("post.hide",0,"intval"),
                    "p_id"	=>I("post.p_id",0,"intval"),
                    "sort"	=>I("post.sort",1,"intval"),
                );
            if($data["p_id"] == 0){
                $data["level"] = 1;
            }else{
                $data["level"] = 2;
            }
            if($model->create($data,1)){
                $ret = $model->addMenu($data);
                if($ret){
                    //清除缓存
                    $data = M("account")->field("id")->select();
                    foreach ($data as $key =>$item){
                        S("menus".$item["id"],NULL);
                        S("menuChildrens".$item["id"],NULL);
                        S("menuPower".$item["id"],NULL);
                    }
                    if($next){
                        $this->success("成功",U("nextmenu",array("id"=>$next)));
                    }else{
                        $this->success("成功",U("node"));
                    }
                }
            }
        }else{
            $nextId = I("get.next");
            $builder = new AdminConfigBuilder();
            $pidList = $model->getFirstMenuConfig();
            $pidList = i_array_column($pidList,'title','id');
            $builder
                ->keyHidden("next")
                ->title("新增菜单")
                ->keySelect("p_id",array("select"=>$pidList,"title"=>"所属目录"))
                ->keyText("title",array("title"=>"标题"))
                ->keyText('url',array("title"=>"菜单连接","placeholder"=>"一级目录无需连接"))
                ->keyText("sort",array("title"=>"排序"))
                ->keySelect('hide',array("title"=>"是否隐藏","select"=>array("0"=>"不隐藏","1"=>"隐藏")))
                ->buttonSubmit(U("addMenu"))
                ->data(array("next"=>$nextId))
                ->display();
        }
    }

    //查看下一级目录
    public function nextMenu(){
        $id = I("get.id");
        $model = D("AdminMenu");
        $builder = new AdminListBuilder();
        $data = $model->getNextMenuList($id);
        $builder
            ->title("菜单管理")
            ->newButton(U("addMenu",array("next"=>$id   )))
//            ->deleteButton(U("deletenextMenu"))
            ->keyLink('title','标题','hideurl?id=###')
            ->keyText('p_title','所属目录')
            ->keyText('url','连接')
            ->keyText("sort","排序")
            ->keyStatus('hide','是否隐藏',array("0"=>'未隐藏',"1"=>"隐藏"))
            ->keyDoActionEdit("editMenu?p_id={$id}&id=###")
            ->data($data)
            ->display();

    }


    /*
     *  每一个操作对应的函数都需要写入进来,区分大小写  Controller/Function
     *  例如
     *       User/edit 修改
     *       User/delete  删除
     *       User/add  新增
     * */
    public  function  hideUrl(){
        $builder = new AdminListBuilder();
        $model = D("AdminMenu");
        $id = I("get.id");
        list($data) = $model->getMenuInfo($id);
        $title = $data["title"];
        $data = $model->getThreeFun($id);
        $builder->title($title."  相关函数")
                ->powerAdd(U("addNode",array("id"=>$id)))
                ->powerRemove(U("deleteNode"))
                ->keyText("id","ID")
                ->keyText("title","标题")
                ->keyText("url","函数名")
                ->keyDoAction("editNode?id=###")
                ->data($data)
                ->display();
    }

    public  function  deleteNode(){

    }

    public  function editNode(){
        $builder = new AdminConfigBuilder();
        if($_POST){
            $data = array(
                "url"=>I("post.url"),
                "title"=>I("post.title"),
            );
            $id = I("post.id");
            $model = D("AdminMenu");
                $ret =  $model->saveMenu($data,$id);
                if($ret){
                    $builder->success("添加成功",U("hideUrl",array("id"=>I("post.p_id"))));
                }else{
                    $builder->error($model->getError());
                }
        }else{
            $id = I("get.id");
            $model = D("AdminMenu");
            list($data) = $model->getMenuInfo($id);
            $builder
                ->keyHidden("id")
                ->keyHidden("p_id")
                ->title($data["title"]."  编辑授权函数")
                ->keyText("url",array("title"=>"函数名"))
                ->keyText("title",array("title"=>"标题"))
                ->buttonSubmit()
                ->data($data)
                ->display();
        }
    }

    public  function addNode(){
        $builder = new AdminConfigBuilder();
        if($_POST){
           $data = array(
               "url"=>I("post.url"),
               "title"=>I("post.title"),
               "p_id"=>I("post.p_id"),
               "level"=>I("post.level"),
               "sort"=>0
           );
           $model = D("AdminMenu");
               $ret =  $model->addMenu($data);
               if($ret){
                   $builder->success("添加成功",U("hideUrl",array("id"=>I("post.p_id"))));
               }else{
                   $builder->error($model->getError());
               }
       }else{
           $id = I("get.id");
           $model = D("AdminMenu");
           list($data) = $model->getMenuInfo($id);
           $builder
               ->title($data["title"]."  新增授权函数")
               ->keyText("url",array("title"=>"函数名"))
               ->keyText("title",array("title"=>"标题"))
               ->keyHidden("p_id")
               ->keyHidden("level")
               ->data(array("p_id"=>$id,"level"=>3))
               ->buttonSubmit()
               ->display();
       }
    }

    public  function  powerGroup(){
        if($_POST){
            $role_id = $_POST["role_id"];
            $one = $_POST["one"];
            $two = $_POST["two"];
            $pageFun = $_POST["pageFun"];

            //每次修改时将之前组的权限全部删除, 在添加回去组的权限
            M("access")->where(array("role_id"=>$role_id))->delete();
            foreach ($one as $key =>$val){
                M("access")->add(array("role_id"=>$role_id,"node_id"=>$val,"level"=>1));
           }
            foreach ($two as $key =>$val){
                M("access")->add(array("role_id"=>$role_id,"node_id"=>$val,"level"=>2));
            }
            foreach ($pageFun as $key =>$val){
                M("access")->add(array("role_id"=>$role_id,"node_id"=>$val,"level"=>3));
            }

            M("power")->where(array("role_id"=>$role_id))->delete();

            $three =$_POST["btn"];
            foreach ($three as $key =>$item){
                $add = $item['add'][0]==1?$item['add'][0]:0;
                $remove = $item['remove'][0]==1?$item['remove'][0]:0;
                $edit = $item['edit'][0]==1?$item['edit'][0]:0;
                $query = $item['query'][0]==1?$item['query'][0]:0;
                $excel = $item['excel'][0]==1?$item['excel'][0]:0;
                $verify = $item['verify'][0]==1?$item['verify'][0]:0;
               M("power")->add(array("role_id"=>$role_id,"menu_id"=>$key,"add"=>$add,"remove"=>$remove,"edit"=>$edit,"query"=>$query,"export"=>$excel,"verify"=>$verify,"level"=>2));
            }
           $this->success("权限更改成功",U("index"));
        }else{
            $builder = new AdminListBuilder();
            $groupid = I("post.id");
            $model = D("Role");
            $data = $model->getGroupInfo($groupid);
            $name = $data["name"];
            $data = $model->setPower();
            $builder
                ->title($name."权限配置")
                ->otherData($data)
                ->display("power");
        }
    }

    public  function  deletenextMenu($ids){
        $id = array_unique((array)I('ids', 0));
        $map2['id']= array('in', $ids);
        $ret2 =  M("admin_menu")->where($map2)->delete();
        $data = M("account")->field("id")->select();
        foreach ($data as $key =>$item){
            S("menus".$item["id"],NULL);
            S("menuChildrens".$item["id"],NULL);
            S("menuPower".$item["id"],NULL);
        }
        if( $ret2>0){
            $this->success("删除成功",U("index"));
        }else{
            $this->error("删除失败");
        }
    }

    public function editMenu(){
        $model = D("AdminMenu");
        if(IS_POST){
            $data = array(
                "title" =>I("post.title"),
                "url"	=>I("post.url"),
                "hide"	=>I("post.hide",0,"intval"),
                "p_id"	=>I("post.p_id",0,"intval"),
                "sort"	=>I("post.sort",1,"intval"),
            );
            $id = I("post.id");
            $ret = $model->saveMenu($data,$id);
            if($ret){
                $data = M("account")->field("id")->select();
                foreach ($data as $key =>$item){
                    S("menus".$item["id"],NULL);
                    S("menuChildrens".$item["id"],NULL);
                    S("menuPower".$item["id"],NULL);
                }
                if(I("post.p_id")){
                    $this->success("成功",U("nextMenu",array("id"=>I("post.p_id"))));
                }else{
                    $this->success("成功",U("index"));
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $builder = new AdminConfigBuilder();
            $pidList = $model->getFirstMenuConfig();
            $id = I("get.id");
            $p_id = I("get.p_id");
            $pidList = i_array_column($pidList,'title','id');
            list($data) = $model->getMenuInfo($id);
            if($p_id)$data["p_id"] = $p_id;
            $builder
                ->title("修改菜单")
                ->keyHidden("id")
                ->keyHidden("p_id")
                ->keySelect("p_id",array("title"=>"所属目录","select"=>$pidList))
                ->keyText("title",array("title"=>"标题"))
                ->keyText('url',array("title"=>"菜单连接","placeholder"=>"一级目录无需连接"))
                ->keyText("sort",array("title"=>"排序"))
                ->keySelect('hide',array("title"=>"是否隐藏","select"=>array("0"=>"不隐藏","1"=>"隐藏")))
                ->buttonSubmit(U("editMenu"))
                ->data($data)
                ->display();
        }
    }
}