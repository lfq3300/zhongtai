<?php

define('APP_DEBUG',True);
define('BIND_MODULE','Admin');
define ( 'APP_PATH', './Application/' );
define ('OS_THEME_PATH', './Theme/');
define ( 'RUNTIME_PATH', './Runtime/' );
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");

require './ThinkPHP/ThinkPHP.php';