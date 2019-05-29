<?php
/*
 * 2019-01-15 cat
 * 因为命令行要做复杂的数据处理，为了保证最大的灵活性，此处类为数据库类
 * 此为*分*库模型,根据条件拆分client数据库
 * 使用方法为：
 * $DbClient = new \Db\Divide\Client($params);
 * $DbClient::pdo()即是Client对应分库的pdo连接
 */
namespace Db\Divide;

class Client {
    
    const DB = 'client';
    
    public static $ConnectPool;
    public static $DivideParams;
    
    public function __construct($params){
        if($params){
            self::$DivideParams= $params;
        }else{
            throw new \Exception('参数错误');
        }
    }

    public static function pdo(){
        //防止多次实例化时多次连接数据库
        if(self::$ConnectPool[self::$DivideParams['divide']]){  //假设参数中的divide为分库条件
        }else{
            /*
             * 处理分表条件逻辑自行编写
             * =======================
             * =======================
             * 以下是简单示例
             */
            $config['dbname'] = self::$DivideParams['divide'].'_'.self::DB;
            self::$ConnectPool[self::$Tid] = new \Db\MysqlConnetion($config);
        }
    return self::$ConnectPool[self::$DivideParams['divide']];
    }
    
    public function close(){
        self::$ConnectPool[self::$DivideParams['divide']] = null;
    }
    
}
