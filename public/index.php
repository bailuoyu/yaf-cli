<?php
//指向项目目录
define('APP_PATH',realpath(dirname(__FILE__).'/../'));
//加载框架的配置文件
$app = new Yaf\Application( APP_PATH . "/conf/application.ini");
//加载bootstrap配置内容并启动
$app -> bootstrap() -> run();

