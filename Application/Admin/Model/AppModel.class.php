<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class AppModel extends CommonModel
{
    //自动验证
    protected $_validate = array(
        array('appid','',"此公众号已经授权,不允许重复授权",'1','unique','1')
    );

    public function addApp($data)
    {
        return M("app")->add($data);
    }

    public function  getList(){
        return  M("app")->order("id desc")->select();
    }
}