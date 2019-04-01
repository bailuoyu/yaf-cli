<?php
/*
 * 2018-01-05 cat
 * redis通用继承模型
 */
namespace Redis\Cli;

class CommonModel extends \RedisActiveModel{
    
    protected static $Connect;
    
    function __construct() {
        $Class = get_called_class();
        $str = str_ireplace('Model','',strrchr(get_called_class(),'\\'));
        if($str){
            $class_name = ltrim($str,'\\');
        }else{
            $class_name  = str_ireplace('Model','',$Class);
        }
        $this->key_pre = str_ireplace('Model','',$class_name);
        $this->redis = self::redis();
    }
    
    public static function redis(){
        if(self::$Connect){
        }else{
            $config = \Yaf\Application::app() -> getConfig() -> redis -> toArray();
            self::$Connect = new \Nosql\RedisConnetion($config);
        }
        return self::$Connect;
    }
    
}

