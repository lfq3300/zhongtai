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
use Think\Controller;

class AppGroupController extends Controller {
    public function index(){
        $builder = new AdminListBuilder();
        $model = D("AppGroup");
        $list = $model->getList();
        $builder->title("公众号分组")
                ->powerAdd(U("add"))
           //     ->keyText("id","ID")
                ->keyText("group_name","组名")
                ->powerEdit("edit?id=###")
                ->data($list)
                ->display();
    }
    public  function add(){
        if($_POST){
            $model = D("AppGroup");
            $data = [
                'group_name'=>I("post.group_name")
            ];
            if ($model->create($data,1)){
                $ret = $model->addGruop($data);
                if($ret){
                    AddactionLog("添加公众号分组".I("post.group_name"));
                    $this->success("成功",U("index"));
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $builder = new AdminConfigBuilder();
            $builder
                ->title("添加公众号分组")
                ->keyText("group_name",['title'=>'分组名称'])
                ->buttonSubmit()
                ->display();
        }
    }

    public  function edit(){
        $model = D("AppGroup");
        if($_POST){
            $data = [
                'group_name'=>I("post.group_name")
            ];
            $id = I("post.id");
            if ($model->create($data,1)){
                $ret = $model->editGruop($id,$data);
                if($ret!=false){
                    AddLoginActionLog("修改公众号分组".I("post.group_name")."改为".I("post.old_group_name"));
                    $this->success("成功",U("index"));
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $builder = new AdminConfigBuilder();
            $data = $model->getInfo(I("get.id"));
            $data["old_group_name"] = $data["group_name"];
            $builder
                ->title("修改公众号分组")
                ->keyHidden("id")
                ->keyHidden("old_group_name")
                ->keyText("group_name",['title'=>'分组名称'])
                ->buttonSubmit()
                ->data($data)
                ->display("");
        }
    }
}