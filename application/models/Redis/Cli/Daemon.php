<?php
/*
 * 2019-01-04
 * 进程守护模型
 */

namespace Redis\Cli;

class DaemonModel extends CommonModel{
    
    public $keyr = array(   //该类下有哪些键
        'allprocess' => ['allprocess',null],   //第一个为缩写(不缩写填相同的)，第二个为默认时效(可以直接更改或setExpire($key,$attr_key)单独设置)
        'process' => ['process',3600],
    );
    
    /*
     * 将所有键存入hash
     */
    public function setAllProcess($arr,$expire=null){
        $redis_key = $this ->getKey('','allprocess');
        if($expire<=0){
            $expire = $this -> keyr['allprocess'][1];
        }
        $res = self::redis() -> hMset($redis_key,$arr);
//        $res_ex = self::redis() -> expire($redis_key,$expire);
//        return $res&&$res_ex;
        return $res;
    }
    
    /*
     * 拼接进程键名
     */
    public function splitProcessKey($module,$controller,$action,$params=[]){
        $key = $module.'/'.$controller.'/'.$action;
        if($params){
            $key .= ':'.implode('-',$params);
        }
        return $this ->getKey(strtolower($key),'process');
    }
    
}
