<?php
namespace Common\Upload;
class Upload{
    public function UploadImg(){
        $result = 0;
        if ($_FILES["file"]["error"] === 0){
            $upload = new \Think\Upload(C("PICTURE_UPLOAD"));// 实例化上传类
            $info =  $upload->uploadOne($_FILES['file']);
            if(!$info) {
                $msg = $upload->getError();
            }else{
                $result = 1;
                $object = 'Uploads/'.date('Y-m-d').'/'.$info['savename'];//想要保存文件的名称
                $info['path'] =  'Uploads/'.$info['savepath'].$info['savename'];
                $info['root_path'] =  C(IMG_URL).$object;
                $msg = '上传成功';
            }
        }else{
            $msg = '未获取上传文件';
        }
        returnJson($result,$msg,$info);
    }
}
?>