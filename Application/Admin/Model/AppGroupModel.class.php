<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppGroupModel extends CommonModel
{
    protected $tableName = 'app_group';
    //自动验证
    protected $_validate = array(
        array('group_name','require',"分组名称不能为空",'1','','1'),
        array('group_name','',"分组名称已存在",'1','unique','1')
    );

    public function addGruop($data)
    {
      return M("app_group")->add($data);
    }

    public function  getList($status = false){
        $data = M("app_group")->order("id desc")->select();
        if($status){
            $arr = [];
            foreach ($data as $item){
                $arr[$item["id"]] = $item["group_name"];
            }
            return $arr;
        }else{
            return $data;
        }
    }

    public function getInfo($id){
        return M("app_group")->where(array("id"=>$id))->find();
    }
    public function editGruop($id,$data){
        return M("app_group")->where(array("id"=>$id))->save($data);
    }
}