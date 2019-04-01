<?php
/*
 * 2018-11-21 cat
 * 彩票采集脚本
 */
class CollectController extends CommonController {
    
    public function mainAction(){
        $interval = 5;  //请求间隔设置(秒)
        //声明彩票采集类
        $Opencai = new \Lottery\Collecter\Opencai\Opencai();
        //声明Master\Log的PDO连接
        $LogConn = \Db\Master\Log::pdo();
        //循环开始,无限循环
        $i = 1;
        while(true){
            echo 'start : ',$i,PHP_EOL;
            //开始时间
            $start_time = time();
            $res = $Opencai -> news();
    //        $result = $res['result'];
            $log_tab = '`collect_opencai`';
            $params = array(
                'url' => $res['url'],
                'content' => $res['response'],
                'remark' => '自动采集脚本'
            );
            $values = $LogConn -> values($params);
            $log_query = "insert into {$log_tab} $values";
            $LogConn -> query($log_query);
            //结束时间
            $end_time = time();
            //花费时间
            $time = $end_time - $start_time;
            echo 'time : ',$time,PHP_EOL;
            //睡眠时间
            $sleep_time = $interval - $time;
            echo 'sleep time : ',$sleep_time,PHP_EOL;
            if($sleep_time>0){
                sleep($sleep_time);
            }
            //计数
            $i++;
        }
    }
    
    public function testAction(){
        //声明Master\Log的PDO连接
        $LogConn = \Db\Master\Log::pdo();
        $LogConn -> checkPing();
    }
    
}
