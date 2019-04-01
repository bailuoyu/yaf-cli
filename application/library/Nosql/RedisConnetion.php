<?php
/*
 * 2019-01-05 cat
 * redis连接
 */
namespace Nosql;

class RedisConnetion extends \Redis{
    
    protected $config = array();    //连接属性
    /*
     * 初始化数据库连接
     */
    public function __construct($config){
        //redis的配置参数
        if(empty($config['host'])){
            throw new \Exception('host配置错误');
        }else{
            $this->config = $config;
        }
        $this->config['port'] = $this->config['port']?$this->config['port']:6379;
        
        parent::__construct();
        
        $this -> connetion();
        
    }
    
    private function connetion(){
        $this -> connect($this->config['host'],$this->config['port']);
        if($this->config['password']){   //如果有设置密码
            $this -> auth($this->config['password']);
        }
        if($this->config['database']){   //如果有选库
            $this -> select($this->config['database']);
        }
    }

    /*
     * 检查连接是否有效，失效则重连.只适用于长期执行不释放的脚本
     * 连接超过3600秒强制重连
     */
    function checkPing(){
        $nowtime = time();
        if(!$this->ping_time){$this->ping_time = $nowtime;}
        $duration = $nowtime - $this->ping_time;
        if($duration>3600){   //超过时间强制重连
            $this ->reconnect();
        }else{
            $res = $this -> ping();
            if($res=='+PONG'||$res=='PONG'){
            }else{
                $this ->reconnect();
            }
        }
    }
    
    /*
     * 强制重连
     */
    public function reconnect(){
        $this -> connetion();
        $this->ping_time = time();
    }
    
    

}