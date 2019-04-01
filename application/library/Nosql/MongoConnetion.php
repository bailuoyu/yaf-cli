<?php
/*
 * 2019-01-14 cat
 * mongo连接
 */
namespace Nosql;

//选择继承MongoDB\Database而不是MongoDB\Client,从而类似于mysql模式
class MongoConnetion extends \MongoDB\Database{
    
    protected $config = array();    //连接属性
    /*
     * 初始化数据库连接
     */
    public function __construct($config){
        //mongo的配置参数
        if(empty($config['host'])){
            throw new \Exception('host配置错误');
        }else{
            $host = $config['host'];
        }
        $port = $config['port']?$config['port']:27017;
        if(empty($config['username'])||empty($config['password'])){
            $this->config['dsn'] = "mongodb://{$host}:{$port}";
        }else{
            $this->config['dsn'] = "mongodb://{$config['username']}:{$config['password']}@{$host}:{$port}";
        }
        $this->config['urioptions'] = $config['urioptions']??[];
        
        if(empty($config['dbname'])){
            throw new \Exception('dbname配置为空');
        }else{
            $this->config['dbname'] = $config['dbname'];
        }
        $this->config['options'] = $config['options']??[];
        //建立连接
        $this->Client = new \MongoDB\Client($this->config['dsn'],$this->config['urioptions']);
        parent::__construct($this->Client->getManager(),$this->config['dbname'],$this->config['options']);
        
    }

    /*
     * 检查连接是否有效，失效则重连.只适用于长期执行不释放的脚本
     * 连接超过3600秒强制重连
     */
    public function checkPing(){
        $nowtime = time();
        if(!$this->ping_time){$this->ping_time = $nowtime;}
        $duration = $nowtime - $this->ping_time;
        if($duration>3600){   //超过时间强制重连
            $this ->reconnect();
        }else{
            try{
                $res = $this -> Command('db');
                return $res;
            }catch (\PDOException $e) {
                $this ->reconnect();
            }
        }
    }
    
    /*
     * 强制重连
     */
    public function reconnect(){
        $this->Client = new \MongoDB\Client($this->config['dsn'],$this->config['urioptions']);
        $this->ping_time = time();
    }
    
    /*
     * 获取库名
     */
    public function dbName(){
        return $this->config['dbname'];
    }
    
}