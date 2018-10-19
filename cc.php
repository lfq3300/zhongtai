<?php
//清理日志缓存
$dirname = './Runtime/';
cleanup_directory($dirname);

function cleanup_directory($dir) {
  $iter = new RecursiveDirectoryIterator($dir);
  foreach (new RecursiveIteratorIterator($iter, RecursiveIteratorIterator::CHILD_FIRST)
 as $f) {
    if ($f->isDir()) {
      //rmdir($f->getPathname());
      //echo $f->getPathname()."<br />";
    } else {
      $pathName = $f->getPathname();
      //echo $pathName."<br />";
      if(!strstr($pathName,'Logs')){
        unlink($pathName);
      }else{
        if(strstr($pathName,'Logs/Api') || strstr($pathName,'Logs/Meiku') || strstr($pathName,'Logs/Home') ||
       strstr($pathName,'Logs/Common')){
          unlink($pathName);
        }
      }
    }
  }
}

//清理memcached缓存
if(function_exists('memcache_init')){
    $mem = memcache_init();
    $mem->flush();
}
if (extension_loaded('memcached') ) {
  $memcache = new Memcached();
  $memcache->addServer('127.0.0.1',11211);
  $memcache->flush();
}

echo "yes";
