<?php
/*
 * 2019-02-20
 * 订单模型
 */

namespace Redis\Core;

class OrderModel extends CommonModel{
    
    public $keyr = array(   //该类下有哪些键
        'update_time' => ['update_time',null],   //第一个为缩写(不缩写填相同的)，第二个为默认时效(可以直接更改或setExpire($key,$attr_key)单独设置)
    );
    
    
    
}
