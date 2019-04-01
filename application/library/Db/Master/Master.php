<?php
/*
 * 2018-11-14 cat
 * master平台master库的连接
 */
namespace Db\Master;

class Master {
    
    const DB = 'master';
    
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
    
    /*
     * 查询厅主mysql配置
     */
    public function mysqlConfig($tid){
        $master_tab = '`hall_mysql_config`';
        $Query = self::pdo() -> query("select host,port,username,password from {$master_tab} where tid={$tid} limit 1");
        return $Query->fetch();
    }
    
    /*
     * 查询厅主mysql配置
     */
    public function mongoConfig($tid){
        $master_tab = '`hall_mongo_config`';
        $Query = self::pdo() -> query("select host,port,username,password from {$master_tab} where tid={$tid} limit 1");
        return $Query->fetch();
    }
    
}
