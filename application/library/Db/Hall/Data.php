<?php
/*
 * 2019-01-21 cat
 * 因为命令行要做复杂的数据处理，为了保证最大的灵活性，此处类为数据库类
 * hall厅data库的连接
 */
namespace Db\Hall;

class Data {
    
    const DB = 'data';
    
    public static $ConnectPool;
    public static $Tid;
    
    public function __construct($tid){
        if($tid){
            self::$Tid = (int)$tid;
        }else{
            throw new \Exception('参数错误');
        }
    }

    public static function pdo(){
        //防止多次实例化时多次连接数据库
        if(self::$ConnectPool[self::$Tid]){
        }else{
            //声明Master\Master
            $Master = new \Db\Master\Master();
            $config = $Master -> mysqlConfig(self::$Tid);
            if(!$config){
                throw new \Exception('查询不到配置参数');
            }
            $config['dbname'] = 't'.self::$Tid.'_'.self::DB;
            self::$ConnectPool[self::$Tid] = new \Db\MysqlConnetion($config);
        }
        return self::$ConnectPool[self::$Tid];
    }
    
    public function close(){
        self::$ConnectPool[self::$Tid] = null;
    }
    
}
