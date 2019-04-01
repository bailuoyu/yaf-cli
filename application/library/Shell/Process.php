<?php
/*
 * 2018-01-17 cat
 * 文件日志记录
 */
namespace Shell;

class Process {
    
    public function __construct(){
        $this -> cliconfig = \Yaf\Application::app() -> getConfig() -> cli -> toArray();
    }
    /*
     * 拼接cmd命令
     */
    public function splitCmd($module,$controller,$action,$params=[]){
        $cmdmid = $module.'/'.$controller.'/'.$action;
        if($params){
            $cmdmid .= ' '.implode(' ',$params);
        }
        return strtolower($cmdmid);
    }
    
    /*
     * 检测命令是否存活
     */
    public function check($cmdmid){
        $cmd = "ps -ef | grep '{$_SERVER['PHP_SELF']} {$cmdmid}' | grep -v grep | wc -l";
//        system($cmd,$code);
        exec($cmd,$out,$code);
        if($code==0){
            return $out[0];
        }else{
            return -1;
        }
    }
    /*
     * 开始命令
     */
    public function start($cmdmid,$output='/dev/null 2>&1 &'){
        $cmd = "{$this->cliconfig['php']} {$_SERVER['PHP_SELF']} {$cmdmid} > {$output}";
        system($cmd,$code);
        return $code;
    }
    
    /*
     * 杀死命令进程(强制)
     */
    public function kill($cmdmid){
        $cmd = "pkill -f '{$_SERVER['PHP_SELF']} {$cmdmid}'";
        system($cmd,$code);
        return $code;
    }
    
    /*
     * 重启命令(强制)
     */
    public function restart($cmdmid,$output='/dev/null 2>&1 &'){
        $cmd1 = "pkill -f '{$_SERVER['PHP_SELF']} {$cmdmid}'";
        system($cmd1);
        $cmd2 = "{$this->cliconfig['php']} {$_SERVER['PHP_SELF']} {$cmdmid} > {$output}";
        system($cmd2,$code);
        return $code;
    }
}
