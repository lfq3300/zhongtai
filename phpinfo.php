<?php
$shell = "ls -la";
echo "<pre>";
system($shell, $status);
echo "</pre>";
echo $status;
//注意shell命令的执行结果和执行返回的状态值的对应关系
$shell = "<font color='red'>$shell</font>";
if( $status ){
    echo "shell命令{$shell}执行失败";
} else {
    echo "shell命令{$shell}成功执行";
}