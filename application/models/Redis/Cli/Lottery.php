<?php
/*
 * 2018-12-26
 * 彩票模型
 */

namespace Redis\Cli;

class LotteryModel extends CommonModel{
    
    public $keyr = array(   //该类下有哪些键
//        'token' => ['token',3600],   //第一个为缩写(不缩写填相同的)，第二个为默认时效(可以直接更改或setExpire($key,$attr_key)单独设置)
        
    );
    
    
    
}
