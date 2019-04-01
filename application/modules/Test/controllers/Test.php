<?php
/*
 * 2019-01-03 cat
 * 测试脚本
 */
class TestController extends CommonController {
    
    public function indexAction(){
        $params = $this -> getRequest() -> getParams();
        var_dump($params);
    }
    
    public function mongoAction(){
        $TestDB = \Nosql\MongoCli\Test::mongo();
        //选择数据表
        $Test = $TestDB -> selectCollection('test');
        //选择数据表可以简写,执行效率比函数略低
//        $Test = $TestDB -> test;
        $ar = array(
            'a' => 1,
            'b' => 2,
            'c' => 'aaa'
        );
        $resultOne= $Test -> insertOne($ar);
        var_dump($resultOne);
        $lastId = $resultOne->getInsertedId();
        var_dump($lastId);
    }
    
    public function clientAction(){
        $Client = new \Db\Hall\Client(1);
        $ClientConn = $Client::pdo();
        var_dump($ClientConn);
    }
    
    public function mainAction(){
        $this -> getRequest() -> setParam(0,'abc');
        $this -> getRequest() -> setParam(1,'def');
        $params = $this -> getRequest() -> getParams();
        var_dump($params);
    }
    
    public function mysqlAction(){
        $Client = new \Db\Demo\Log();
        while(true){
            $Client::pdo() -> checkPing();
            sleep(5);
        }
    }

    public function oneAction(){
        $i = 0;
        while(true){
            $i++;
            echo 'one:',$i,PHP_EOL;
            sleep(5);
        }
    }
    
    public function twoAction(){
        $i = 0;
        while(true){
            $i++;
            echo 'two:',$i,PHP_EOL;
            sleep(6);
        }
    }
    
    public function threeAction(){
        $i = 0;
        while(true){
            $i++;
            echo 'three:',$i,PHP_EOL;
            sleep(7);
        }
    }
    
    public function fourAction(){
        $i = 0;
        while(true){
            $i++;
            echo 'four:',$i,PHP_EOL;
            sleep(4);
        }
    }
    
    
    
}
