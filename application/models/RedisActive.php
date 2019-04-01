<?php
/*
 * Cat 2019-01-05
 * 自定redis ActiveRecord
 */

abstract class RedisActiveModel {
    
    protected $key_pre;
    public $keyr;
    public $redis;

    abstract public static function redis();
    
    /*
     * 魔术方法，调用php_redis拓展自带方法
     */
//    public function __call($method,$args){
//        return $this->redis -> $method(...$args);
//    }

    //获取redis 真实 key
    public function getKey($key,$attr_key){
        if(strstr($key,$this->key_pre.':')){
            throw new \Error('请不要使用redis真实键名!');
        }
        if(empty($this->keyr[$attr_key])){     //如果键类型不存在
            throw new \Error('非法的attribute!'.$attr_key);
        }else{
            $mid = $this->keyr[$attr_key][0];
        }
        $redis_key = $this->key_pre.':'.$mid;
        if ($key){
            $redis_key .= ':'.$key;
        }
        return $redis_key;
    }
    
    //获取时效
    public function getExpire($attr_key){
        $expire = $this->keyr[$attr_key][1]??'';
        return $expire;
    }
    
    //设置时效
    public function setExpire($key,$attr_key){
        if(strstr($key,$this->key_pre.':')){
            throw new \Error('请不要使用redis真实键名!');
        }
        if(empty($this->keyr[$attr_key])){     //如果键类型不存在
            throw new \Error('非法的attribute!');
        }else{
            $r = $this->keyr[$attr_key];
        }
        $redis_key = $this->key_pre.':'.$r[0];
        if ($key){
            $redis_key .= ':'.$key;
        }
        $expire = $r[1];
        return $this->redis -> expire($redis_key,$expire);
    }
    
    //单个清除
    public function clear($key,$attr_key){
        if(strstr($key,$this->key_pre.':')){
            throw new \Error('请不要使用redis真实键名!');
        }
        if(empty($this->keyr[$attr_key])){     //如果键类型不存在
            throw new \Error('非法的attribute!');
        }else{
            $mid = $this->keyr[$attr_key][0];
        }
        $redis_key = $this->key_pre.':'.$mid;
        if ($key){
            $redis_key .= ':'.$key;
        }
        return $this->redis -> del($redis_key);
    }
    
    //全部清除
    public function allClear($key){
        if(strstr($key,$this->key_pre.':')){
            throw new \Error('请不要使用redis真实键名!');
        }
        $del_r = array();
        foreach($this->keyr as $_v){
            $mid = $_v[0];
            $redis_key = $this->key_pre.':'.$mid;
            if ($key){
                $redis_key .= ':'.$key;
            }
            $del_r[] = $redis_key;
        }
        return $this->redis -> del($del_r);
    }
}
