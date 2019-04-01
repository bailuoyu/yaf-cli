<?php
/*
 * 2018-11-14 cat
 * 因为命令行要做复杂的数据处理，为了保证最大的灵活性，此处模型为库模型
 * master平台log库的连接
 */
namespace Db\Master;

class Log {
    
    const DB = 'log';
    
    protected static $Connect;
    
    public static function pdo(){
        //防止多次实例化时多次连接数据库
        if(self::$Connect){
        }else{
            $config = \Yaf\Application::app() -> getConfig() -> mysql -> master -> toArray();
            $config['dbname'] = self::DB;
            self::$Connect = new \Db\MysqlConnetion($config);
        }
        return self::$Connect;
    }
    
    public function close(){
        self::$Connect = null;
    }
    
}
