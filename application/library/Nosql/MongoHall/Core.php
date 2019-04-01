<?php
/*
 * 2019-01-15 cat
 * 因为命令行要做复杂的数据处理，为了保证最大的灵活性，此处模型为库模型
 * hall厅core库的连接
 */
namespace Nosql\MongoHall;

class Core {
    
    const DB = 'core';
    
    public static $ConnectPool;
    public static $Tid;
    
    public function __construct($tid){
        if($tid){
            self::$Tid = (int)$tid;
        }else{
            throw new \Exception('参数错误');
        }
    }

    public static function mongo(){
        //防止多次实例化时多次连接数据库
        if(self::$ConnectPool[self::$Tid]){
        }else{
            //声明Master\Master
            $Master = new \Db\Master\Master();
            $config = $Master -> mongoConfig(self::$Tid);
            if(!$config){
                throw new \Exception('查询不到配置参数');
            }
            $config['dbname'] = 't'.self::$Tid.'_'.self::DB;
            self::$ConnectPool[self::$Tid] = new \Nosql\MongoConnetion($config);
        }
        return self::$ConnectPool[self::$Tid];
    }
    
    public function close(){
        self::$ConnectPool[self::$Tid] = null;
    }
    
}
