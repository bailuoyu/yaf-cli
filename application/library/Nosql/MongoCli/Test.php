<?php
/*
 * 2019-01-14 cat
 * 因为命令行要做复杂的数据处理，为了保证最大的灵活性，此处模型为库模型
 * cli的mongo库
 */
namespace Nosql\MongoCli;

class Test {
    
    const DB = 'test';
    
    protected static $Connect;
    
    public static function mongo(){
        //防止多次实例化时多次连接数据库
        if(self::$Connect){
        }else{
            $config = \Yaf\Application::app() -> getConfig() -> mongo -> cli -> toArray();
            $config['dbname'] = self::DB;
            self::$Connect = new \Nosql\MongoConnetion($config);
        }
        return self::$Connect;
    }
    
}
