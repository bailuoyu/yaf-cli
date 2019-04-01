<?php
/*
 * 2019-01-17 cat
 * 测试脚本
 */
class IntervalController extends CommonController {
    
//    $this -> getRequest() -> setParam(0,'abc');
//    $this -> getRequest() -> setParam(1,'def');
//    $params = $this -> getRequest() -> getParams();
    
    public function testAction(){
        
    }

    public function oneAction(){
        sleep(9);
        echo 'one',PHP_EOL;
    }
    
    public function twoAction(){
        sleep(11);
        echo 'two',PHP_EOL;
    }
    
    public function threeAction(){
        sleep(7);
        echo 'three',PHP_EOL;
    }
    
    public function fourAction(){
        sleep(13);
        echo 'four',PHP_EOL;
    }
    
}
