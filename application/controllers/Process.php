<?php
/*
 * 2019-01-03 cat
 * 进程总控脚本
 * 在redis中daemon对应哈希键的'ctl'中输入：
 * start,stop,kill对应开始,停止,杀死,restart可实现安全重启守护的进程;(目前只支持直接在redis修改值，命令行模式我懒得写了-_-)
 * 进程守护主控方法
 * 启动方式: nohup php yaf.php process/daemon > log/daemon.log 2>&1 &
 * 重启方式: pkill -f 'yaf.php process/daemon' ; nohup php yaf.php process/daemon > log/daemon.log 2>&1 &
 * 间隔任务主控方法
 * 启动方式: nohup php yaf.php process/interval > log/interval.log 2>&1 &
 * 重启方式: pkill -f 'yaf.php process/interval' ; nohup php yaf.php process/interval > log/interval.log 2>&1 &
 */
class ProcessController extends CommonController {
    
    /*
     * 进程守护主控方法
     * 启动方式: nohup php yaf.php process/daemon > log/daemon.log 2>&1 &
     * 重启方式: pkill -f 'yaf.php process/daemon' ; nohup php yaf.php process/daemon > log/daemon.log 2>&1 &
     */
    public function daemonAction(){
        $this->DaemonRedis = new \Redis\Cli\DaemonModel();
        $this->Shell = new \Shell\Process();
        //初始化配置文件
        $this -> daemonInit();
        $interval = 5;  //守护间隔
        $allprocess_key = $this->DaemonRedis -> getKey('','allprocess');
        while(true){
            //检查redis连接是否断开
            $this->DaemonRedis::redis() -> checkPing();
            $allprocess_r = $this->DaemonRedis::redis() -> hGetAll($allprocess_key);
            $this -> daemonLoop($allprocess_r);
            sleep($interval);
        }
    }
    
    /*重新载入需要进程守护的方法*/
    private function daemonInit(){
        //进程初始化
        $fun_r  = require(APP_PATH.'/conf/common/process/daemon.php');      //需要进程守护的方法
//        $this->DaemonRedis = new \Redis\Cli\DaemonModel();
        //声明命令行进程类
//        $Shell = new \Shell\Process();
        $allprocess_r = array();
        foreach ($fun_r as $_v) {
            $rediskey = $this->DaemonRedis -> splitProcessKey($_v['module'],$_v['controller'],$_v['action'],$_v['params']);
            $allprocess_r[$rediskey] = $this->Shell -> splitCmd($_v['module'],$_v['controller'],$_v['action'],$_v['params']);
        }
        //查询并清理原有的key
        $allprocess_key = $this->DaemonRedis -> getKey('','allprocess');
        $old_allprocess_r = $this->DaemonRedis::redis() -> hGetAll($allprocess_key);
        $clear_r = array_diff_key($old_allprocess_r,$allprocess_r);
        if($clear_r){
            $clear_keys = array_keys($clear_r);
            $this->DaemonRedis::redis() -> hDel($allprocess_key,...$clear_keys);
            $this->DaemonRedis::redis() -> del($clear_keys);
            foreach($clear_r as $value){
                $this -> kill($value);
            }
        }
        $this->DaemonRedis -> setAllProcess($allprocess_r);
    }

    /**循环体**/
    private function daemonLoop($allprocess_r){
//        $this->DaemonRedis = new \Redis\DaemonModel();
        $r = array(
            'status' => 'running',
//            'ctl' => 'none',
            'check_time' => date('Y-m-d H:i:s')
        );
        //声明命令行进程类
//        $Shell = new \Shell\Process();
        foreach($allprocess_r as $key => $value){
            //查询该命令是否存活
            $check_code = $this -> Shell ->check($value);
            if(!$check_code<0){
                throw new \Error('check命令执行错误');
            }elseif($check_code>1){  //进程重复,异常全部杀死
                $this -> kill($value);
                $check_code = 0;
            }
            $pro_r = $this->DaemonRedis::redis() -> hMGet($key,array('ctl'));  //读取指令
            $ctl = $pro_r['ctl'];
            switch($ctl){
                case 'start':
                    if($check_code==0){
                        $r['code'] = $this->Shell -> start($value);
                    }
                    break;
                case 'restart':
                    if($check_code){
                        $r['code'] = $this->Shell -> restart($value);
                    }else{
                        $r['code'] = $this->Shell -> start($value);
                    }
                    break;
                case 'kill':
                    if($check_code){
                        $r['code'] = $this->Shell -> kill($value);
                    }
                    if(is_numeric($r['code'])&&$r['code']>0){
                        //直接移除
                        $allprocess_key = $this->DaemonRedis -> getKey('','allprocess');
                        $this->DaemonRedis::redis() -> hDel($allprocess_key,$key);
                        $r['status'] = 'kill';
                    }
                    break;
                case 'stop':    //停止守护
                    if($check_code){
                    }else{
                        $r['status'] = 'stop';
                    }
                    $r['code'] = 0;
                    break;
                default:
                    if($check_code==0){
                        $r['code'] = $this->Shell -> start($value);
                    }
                    break;
            }
            if($r['code']==0){
                $r['ctl'] = 'none';
            }else{
                $r['status'] = 'error';
            }
            $this->DaemonRedis::redis() -> hMSet($key,$r);
            $expire = $this->DaemonRedis -> getExpire('process');
            $this->DaemonRedis::redis() -> expire($key,$expire);
        }
    }
    
    /*
     * 间隔任务主控方法
     * 启动方式: nohup php yaf.php process/interval > log/interval.log 2>&1 &
     * 重启方式: pkill -f 'yaf.php process/interval' ; nohup php yaf.php process/interval > log/interval.log 2>&1 &
     */
    public function intervalAction(){
        $this->IntervalRedis = new \Redis\Cli\IntervalModel();
        $this->Shell = new \Shell\Process();
        //初始化配置文件
        $this -> intervalInit();
        $interval = 2;  //守护间隔
        $allprocess_key = $this->IntervalRedis -> getKey('','allprocess');
        while(true){
            //检查redis连接是否断开
            $this->IntervalRedis::redis() -> checkPing();
            $allprocess_r = $this->IntervalRedis::redis() -> hGetAll($allprocess_key);
            $this -> intervalLoop($allprocess_r);
            sleep($interval);
        }
    }
    
    /*重新载入需要间隔运行的方法*/
    public function intervalInit(){
        //进程初始化
        $fun_r  = require(APP_PATH.'/conf/common/process/interval.php');      //需要间隔运行的方法
//        $this->IntervalRedis = new \Redis\Cli\IntervalModel();
        //声明命令行进程类
//        $this->Shell = new \Shell\Process();
        $allprocess_r = array();
        $interval_r = array();
        foreach ($fun_r as $_v) {
            $rediskey = $this->IntervalRedis -> splitProcessKey($_v['module'],$_v['controller'],$_v['action'],$_v['params']);
            $allprocess_r[$rediskey] = $this->Shell -> splitCmd($_v['module'],$_v['controller'],$_v['action'],$_v['params']);
            $interval_r[$rediskey] = $_v['interval'];
        }
        //查询并清理原有的key
        $allprocess_key = $this->IntervalRedis -> getKey('','allprocess');
        $old_allprocess_r = $this->IntervalRedis::redis() -> hGetAll($allprocess_key);
        $clear_r = array_diff_key($old_allprocess_r,$allprocess_r);
        if($clear_r){
            $clear_keys = array_keys($clear_r);
            $this->IntervalRedis::redis() -> hDel($allprocess_key,...$clear_keys);
            $this->IntervalRedis::redis() -> del($clear_keys);
        }
        $this->IntervalRedis -> setAllProcess($allprocess_r);
        foreach($interval_r as $_k => $_v){
            $this->IntervalRedis::redis() -> hSet($_k,'interval',$_v);
        }
    }
    
    /**循环体**/
    private function intervalLoop($allprocess_r){
//        $this->IntervalRedis = new \Redis\IntervalModel();
        $now_time = time();
        $date_time = date('Y-m-d H:i:s',$now_time);
        $r = array(
            'status' => 'running',
//            'ctl' => 'none',
            'check_time' => $date_time
        );
        //声明命令行进程类
//        $Shell = new \Shell\Process();
        foreach($allprocess_r as $key => $value){

            $pro_r = $this->IntervalRedis::redis() -> hMGet($key,array('ctl','interval','last_time','op_times','jump'));  //读取信息
            $real_interval = $now_time - strtotime($pro_r['last_time']);
            if($real_interval>=$pro_r['interval']){
                $is_time = true;
            }else{
                $is_time = false;
            }
            $ctl = $pro_r['ctl'];
            switch($ctl){
                case 'start':
                    $check_code = $this -> checkInterval($value);
                    if($check_code==0){
                        $r['code'] = $this->Shell -> start($value);
                    }
                    $r['ctl'] = 'none';
                    break;
                case 'restart':
                    $check_code = $this -> checkInterval($value);
                    if($check_code){
                        $r['code'] = $this->Shell -> restart($value);
                    }else{
                        $r['code'] = $this->Shell -> start($value);
                    }
                    $r['ctl'] = 'none';
                    break;
                case 'kill':
                    $check_code = $this -> checkInterval($value);
                    if($check_code){
                        $r['code'] = $this->Shell -> kill($value);
                    }
                    if(is_numeric($r['code'])&&$r['code']>0){
                        //直接移除
                        $allprocess_key = $this->IntervalRedis -> getKey('','allprocess');
                        $this->IntervalRedis::redis() -> hDel($allprocess_key,$key);
                        $r['status'] = 'kill';
                    }
                    $r['ctl'] = 'none';
                    break;
                case 'stop':    //停止间隔
                    $r['code'] = 0;
                    $r['status'] = 'stop';
                    $r['ctl'] = 'stop';
                    break;
                default:
                    if($is_time){
                        $check_code = $this -> checkInterval($value);
                        if($check_code){
                            $this->IntervalRedis::redis() -> hIncrBy($key,'jump',1);
                            $r['code'] = 0;
                        }else{
                            $r['code'] = $this->Shell -> start($value);
                            $r['last_time'] = $date_time;
                            $this->IntervalRedis::redis() -> hIncrBy($key,'op_times',1);
                        }
                    }
                    $r['ctl'] = 'none';
                    break;
            }
            if($r['code']==0){
            }else{
                $r['status'] = 'error';
            }
            $this->IntervalRedis::redis() -> hMSet($key,$r);
            $expire = $this->IntervalRedis -> getExpire('process');
            $this->IntervalRedis::redis() -> expire($key,$expire);
        }
    }
    
    //*检查间隔任务*//
    private function checkInterval($cmdmid){
        //查询该命令是否存活
        $check_code = $this -> Shell ->check($cmdmid);
        if(!$check_code<0){
            throw new \Error('check命令执行错误');
        }elseif($check_code>1){  //进程重复,异常全部杀死
            $this -> kill($cmdmid);
            $check_code = 0;
        }
        return $check_code;
    }
}
