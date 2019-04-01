<?php
/*
 * cli命令行
 * 此文件是不被允许访问的文件，**为第一重保险**
 */
//只允许cli模式运行，**为第二重保险**
if(php_sapi_name()!='cli'){
    echo 'No authority';exit();
}
//指向项目目录
define('APP_PATH',realpath(__DIR__.'/'));
//加载框架的配置文件
$app = new Yaf\Application(APP_PATH.'/conf/'.ini_get('yaf.environ').'/application.ini');     //载入cli的配置

//引入composer
require  APP_PATH.'/vendor/autoload.php';

//加载cli的bootstrap配置内容
$app -> bootstrap();

//检查argv参数，**为第三重保险**
$uri_r = explode('/',$argv[1]);
if($uri_r[2]){
}elseif($uri_r[1]){
    array_unshift($uri_r,'index');
}else{
    echo 'uri error!';exit();
}
list($module,$controller,$action) = $uri_r;
$params = array_slice($argv,2);

//改造请求
$Request = new Yaf\Request\Simple('CLI',$module,$controller,$action,$params);

//启动
$app -> getDispatcher() -> dispatch($Request);

