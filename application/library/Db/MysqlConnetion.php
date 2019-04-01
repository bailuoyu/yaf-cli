<?php
/*
 * 2018-11-21 cat
 * mysql连接,基于PDO实现，这里只实现一些基本的语句拼接功能，最大限度保证**灵活性**
 */
namespace Db;

class MysqlConnetion extends \PDO{
    
    protected $config = array();    //连接属性
    /*
     * 初始化数据库连接
     */
    public function __construct($config){
        //mysql的配置参数
        if(empty($config['host'])){
            throw new \Exception('host配置错误');
        }else{
            $host = $config['host'];
        }
        $port = $config['port']?$config['port']:3306;
        $charset = $config['charset']??'utf8';
        $this->config['dbname'] = $config['dbname'];
        $this->config['dsn'] = "mysql:host={$host};port={$port};dbname={$config['dbname']};charset={$charset}";
        $this->config['username'] = $config['username'];
        $this->config['password'] = $config['password'];
        //额外的配置参数
        $this->config['options'] = array(
            self::ATTR_ERRMODE => self::ERRMODE_EXCEPTION,    //错误模式,如果发生错误，则抛出一个 PDOException 异常
            self::ATTR_DEFAULT_FETCH_MODE => self::FETCH_ASSOC,     //默认的结果取出方式为关联数组
            self::ATTR_EMULATE_PREPARES => false,   //预处理prepare不将默认的整型转化为字符串
//            self::ATTR_PERSISTENT => true,      //使用持久化连接
        );
        if($config['options']){
            $this->config['options'] = array($this->config['options'],$config['options']);
        }
        
        parent::__construct($this->config['dsn'],$this->config['username'],$this->config['password'],$this->config['options']);
        
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
                $res = $this -> getAttribute(self::ATTR_SERVER_INFO);
//                return $res;
                echo json_encode($res),PHP_EOL;
            }catch (\PDOException $e) {
                $this ->reconnect();
            }
        }
    }
    
    /*
     * 强制重连
     */
    public function reconnect(){
        parent::__construct($this->config['dsn'],$this->config['username'],$this->config['password'],$this->config['options']);
        $this->ping_time = time();
    }
    
    /*
     * 获取库名
     */
    public function dbName(){
        return $this->config['dbname'];
    }

    /*
     * 拼接set语句,用于update和insert
     * 此处只做最简单的键='值'拼接
     * 支持键值对和字符串两种形式及其组合
     */
    public function sets(array $params){
        $arr = array();
        foreach ($params as $_k => $_v) {
            if(is_numeric($_k)){    //如果索引是数字
                $arr[] = $_v;
            }else{
                $arr[] = "{$_k}='$_v'";
            }
        }
        return 'set '.implode(',',$arr);
    }
    
    /*
     * 拼接values语句,用于insert
     * 此处只做最简单的键='值'拼接,mysql中可以使用set写法代替values写法
     */
    public function values(array $params){
        $keys = array_keys($params);
        return '('.implode(',',$keys).") values ('".implode("','",$params)."')";
    }
    
    /*
     * 拼接condition语句
     * 只做最简单的拼接，且**不会**加上where
     * 支持键值对和字符串两种形式及其组合
     */
    public function condition(array $params,$andor='and'){
        if(strcasecmp($andor,'or')){
            $andor = 'or';
        }else{
            $andor = 'and';
        }
        foreach($params as $_k => $_v){
            if(is_numeric($_k)){    //如果索引是数字
                $arr[] = $_v;
            }else{      //如果是键值对
                $arr[] = "{$_k}='$_v'";
            }
        }
        return implode(' '.$andor.' ',$arr);
    }
}