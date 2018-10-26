<?php
/**
 * Created by PhpStorm.
 * User: luo
 * Date: 2018/10/22
 * Time: 11:20
 */

namespace Admin\Model;
use Admin\Model\CommonModel;
class ArticleTermModel extends CommonModel
{
    public function addData($send_result,$appid){
        $send_result = json_decode($send_result, true);
        $data = $send_result["list"];
        foreach ($data as $dataInfo=>$key){
            $ref_date =  $dataInfo["ref_date"];
            $msgId = $dataInfo["msgid"];
            $title = $dataInfo["title"];
            $data = array(
                "msgid"=>$msgId,
                "title"=>$title,
                "ref_date"=>$ref_date,
                "appid"=>$appid
            );
            $ret = M("article_term")->add($data);
            if (!$ret){
                writeLog('error',M()->getLastSql());
                writeLog('data',json_encode($data,true));
            }
        }
    }

    public function getEffeList(){
       return M()->query("SELECT appid,num,ref_date,msgid FROM mc_article_term WHERE num > 0");
    }

    public function setNum()
    {
       $ret = M()->execute(" update mc_article_term set num = num - 1 ");
        if (!$ret){
            writeLog('error', M()->getLastSql());
        }
    }

    public function  deOver(){
        $ret =  M()->execute(" DELETE FROM mc_article_term WHERE num < 1 ");
        if (!$ret){
            writeLog('error',M()->getLastSql());
        }
    }
}