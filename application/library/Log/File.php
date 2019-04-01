<?php
/*
 * 2018-11-14 cat
 * 文件日志记录
 */
namespace Log;

class File{
    
    public static $log_directory;
    public static $log_num;
    
    /*
     * 获取日志根目录
     */
    public function LogDir(){
        if(self::$log_directory){
            
        }else{
            self::$log_directory = \Yaf\Application::app() -> getConfig() -> log -> directory;
            if(!self::$log_directory){
                throw new \Exception('config配置错误');
            }
        }
        return self::$log_directory;
    }
    
    /*
     * 获取日志进程唯一标识(错误联动导致多处错误时对应关系)
     */
    public function logNum(){
        if(empty(self::$log_num)){
            self::$log_num = randomStr(16,1);
        }else{
        }
        return self::$log_num;
    }

    /*
     * 写日志
     */
    public function write($path,$name,$content){
        $path = $this ->checkPath($path);
        if(stristr($name,'.log')){
        }else{
            $name = $name.'.log';
        }
        $path_name = $this->LogDir().'/'.$path.'/'.$name;
        //声明文件操作类
        $FileUtil = new \File\FileUtil();
        //如果文件不存在则创建
        $FileUtil ->createFile($path_name);
        $content .= PHP_EOL;    //拼接换行符
        //读写方式打开，追加写
//        try{
            $log_file = fopen($path_name,'a+');
            fwrite($log_file,$content);
            fclose($log_file);
//        }catch(Exception $e){
//            throw new \Exception($e);
//        }
    }
    
    /*
     * 检查路径
     */
    protected function checkPath($path){
        if(strstr($path,'..')){
            throw new \Exception('危险的路径参数');
        }
        return trim($path,'/');
    }
    
    /*
     * 处理抓取类错误Exception，Error
     */
    public function objError($e){
        $msg = $e->getMessage().PHP_EOL.$e->getLine().PHP_EOL.$e->getFile().PHP_EOL.$e->getCode();
        return $msg;
    }
    
    /*
     * library错误日志
     * @e 错误
     * @class 脚本路径取__ClASS__或者__METHOD__,会被记录
     */
    public function libError($e,$class){
        if(is_string($e)){
            $msg = $e;
        }elseif(is_object($e)){
            $msg = $this -> objError($e);
        }
        $path = 'library/'.strstr(__CLASS__,'\\',true);
        $name = 'error_'.date('Y-d-m');
        $content = '<'.date('Y-d-m H:i:s').'>'.PHP_EOL;     //添加时间
        $content .= '['.$this->logNum().']'.PHP_EOL;    //添加进程标识
        $content .= '('.$class.')'.PHP_EOL;    //添加类名
        $content .= $msg.PHP_EOL;   //错误详情
        $this -> write($path,$name,$content);
    }
    
    /*
     * controller错误日志
     * @e 错误
     * $module,$controller,$action 模块，控制器，方法
     */
    public function ctrlError($e,$module,$controller,$action){
        if(is_string($e)){
            $msg = $e;
        }elseif(is_object($e)){
            $msg = $this -> objError($e);
        }
        $path = $module;
        $name = 'error_'.date('Y-d-m');
        $content = '<'.date('Y-d-m H:i:s').'>'.PHP_EOL;     //添加时间
        $content .= '['.$this->logNum().']'.PHP_EOL;    //添加进程标识
        $content .= '('.$module.'-'.$controller.'-'.$action.')'.PHP_EOL;    //对应控制器
        $content .= $msg.PHP_EOL;   //错误详情
        $this -> write($path,$name,$content);
    }
    
}

