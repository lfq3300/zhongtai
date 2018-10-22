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
                ->keyText("id","ID")
                ->keyText("group_name","组名")
                ->powerEdit("edit?id=###")
                ->data($list)
                ->display();
    }
    public  function add(){
        if($_POST){
            $model = D("AppGroup");
            $data = [
                'group_name'=>$_POST['group_name']
            ];
            if ($model->create($data,1)){
                $ret = $model->addGruop($data);
                if($ret){
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
                'group_name'=>$_POST['group_name']
            ];
            $id = $_POST['id'];
            if ($model->create($data,1)){
                $ret = $model->editGruop($id,$data);
                if($ret){
                    $this->success("成功",U("index"));
                }else{
                    $this->error($model->getError());
                }
            }else{
                $this->error($model->getError());
            }
        }else{
            $data = $model->getInfo($_GET['id']);
            $builder = new AdminConfigBuilder();
            $builder
                ->title("修改公众号分组")
                ->keyHidden("id")
                ->keyText("group_name",['title'=>'分组名称'])
                ->buttonSubmit()
                ->data($data)
                ->display();
        }
    }
}