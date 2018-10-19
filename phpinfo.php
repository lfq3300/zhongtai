<?php
$command = "./home/sh/zt.ltthk.top.sh";
$a = exec($command,$out,$status);
print_r($a);
print_r($out);
print_r($status);