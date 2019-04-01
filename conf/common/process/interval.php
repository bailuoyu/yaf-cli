<?php
/*
 * 需要间隔运行的方法
 */
return array(
    array(
        'module' => 'test',
        'controller' => 'interval',
        'action' => 'one',
        'params' => array('a','b'),
        'interval' => 10    //间隔秒
    ),
    array(
        'module' => 'test',
        'controller' => 'interval',
        'action' => 'two',
        'interval' => 12    //间隔秒
    ),
    array(
        'module' => 'test',
        'controller' => 'interval',
        'action' => 'three',
        'interval' => 8    //间隔秒
    ),
    array(
        'module' => 'test',
        'controller' => 'interval',
        'action' => 'four',
        'interval' => 14    //间隔秒
    ),
    
);

