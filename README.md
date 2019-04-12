# yaf-cli
基于yaf的cli脚本程序

## 简介
在这里我并不想讨论哪种语言更适合写脚本，当你团队里后台开发人员基本都是php程序员的时候，php就成了脚本的最佳选择。
团队开发就得用框架，不用框架的团队开发出的代码让人绝望，所以，世界上最快的php框架yaf成了最好的选择，基于php7.2的yaf框架cli已经稳定运行。

## 一.配置环境

1. ### php环境
    1. php版本：7.0及以上，推荐版本7.2(5.5,5.6版本理论上也可以，可能需要修改部分bug)
    1. yaf扩展：2.0以上，推荐3.0以上(2.0只支持到php5.5,5.6)
    1. php.ini配置：
        1. 错误等级,推荐E_ALL & ~E_NOTICE
        1. 加入yaf配置
        ```
        [yaf]
        extension=yaf.so
        yaf.use_namespace=1
        yaf.cache_config=1
        yaf.environ=dev     #(dev,local,product根据部署环境填写)
        ```
1. ### mysql环境
    1. 版本：5.5及以上，推荐5.7
    1. php拓展版本：mysqlnd(不是mysql),pdo
        
1. ### redis环境
    1. 版本：4.0以上，推荐5.0以上，低版本理论理论可行但未尝试
    1. php拓展版本：推荐3.0以上

1. ### mongo环境
    1. 版本：3.4以上，推荐4.0以上，低版本理论理论可行但未尝试
    1. php拓展版本：推荐1.50以上
    1. composer 安装mongodb/mongodb 版本：推荐1.3以上，按照源码中的composer.json或者composer.lock

## 二.运行环境
1. ### 配置
    1. 集成了local，lan，dev，product四个环境，对应php.ini中的yaf.environ配置，具体请参考yaf-cli\conf\dev\application.ini
    1. cli入口文件为yaf-cli\yaf.php，可以在入口文件做一些全局操作，比如引入composer

1. ### 集成
    1. library中集成了mysql，redis，mongo，file，log等操作类，为本源码使用的功能插件，自行结合yaf开发，不足之处可以自行修改
    1. 集成了composer，不需要用到的可以在yaf-cli\yaf.php注释掉，注意mongo插件依赖composer中的mongodb/mongodb

1. ### 运行
    1. 完全以mvc模式运行，Cli模拟Web访问，进入yaf-cli目录，运行'php yaf.php test/test/index abc 123',对应的模块是**test**，控制器是**Test**Controller，方法是**index**Action(),abc和123是参数，通过$this -> getRequest() -> getParams()获取
    2. 集成承了Cli进程守护'nohup php yaf.php process/daemon > log/daemon.log 2>&1',只有两个路由参数默认为**index**模块 &',能守护需要持续运行的脚本，配置示例在yaf-cli\conf\common\process\daemon.php
    3. 集成了Cli定时脚本，时间精度代码中设置的是2秒，'nohup php yaf.php process/interval > log/interval.log 2>&1 &'，配置示例在yaf-cli\conf\common\process\interval.php

## 三.文档
看代码注释吧
