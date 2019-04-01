<?php
/*
 * 2019-01-03 cat
 * 测试脚本
 */
class TestController extends CommonController {
    
    public function indexAction(){
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
    
    public function coreAction(){
        $Core = new \Db\Hall\Core(1);
        $CoreConn = $Core::pdo();
        var_dump($CoreConn);
    }
    
    public function mongoAction(){
        $MongoCore = new \Nosql\MongoHall\Core(2);
        $log_mongo_tab = 'funds_deal_log';
        $FundsDealLog = $MongoCore::mongo() -> selectCollection($log_mongo_tab);
//        $result = $FundsDealLog -> find(
        $Obj = $FundsDealLog -> findOne(
            [],
            array(
//                'limit' => 5,
                'sort' => array('id' => -1),
                'projection' => array(
                    'id' => 1,
                    'name' => 1
                )
            )
        )
//        -> toArray()
        ;
//        var_dump((array)$Obj );
        echo 'aaa',PHP_EOL;
        echo $Obj['id'],PHP_EOL,$Obj['_id'];
    }
    
//    $this -> getRequest() -> setParam(0,'abc');
//    $this -> getRequest() -> setParam(1,'def');
//    $params = $this -> getRequest() -> getParams();
//    $params = $this -> getRequest() -> getParams();
//    $tid = $params[0];
    
    public function mainAction(){
        $loader = Yaf\Loader::getInstance();
        $loader->registerLocalNamespace(array('library'));
//        $config = \Yaf\Application::app() -> getConfig();
        $test = new library\Test();
        var_dump($test);
    }


    public function testAction(){
//        $config = \Yaf\Application::app() -> getConfig() -> redis -> toArray();
//        $Redis = new \Nosql\RedisConnetion($config);
//        $Redis -> quit();
//        $res = $Redis -> ping();
//        echo $res;
//        $Obj = new MongoDB\BSON\UTCDateTime(strtotime('2019-02-25 09:55:22'));
//        var_dump($Obj);
//        $tz = new DateTimeZone('PRC');
//        $time = new MongoDB\BSON\UTCDateTime(time()*1000);
//        $date_time = $time->toDateTime()->setTimezone($tz)->format(DATE_ATOM);
//        var_dump($date_time);
//        $bson = new MongoDB\BSON\fromPHP(['date' => new LocalDateTime]);
//        $document = new MongoDB\BSON\toPHP($bson);

//        var_dump($document);
//        var_dump($document->date->toDateTime());
        //**mongo选择Collection
        $order_mongo_tab = 'order_detail';
        //声明mongo
        $MongoCore = new \Nosql\MongoHall\Core(1);
        $Order = $MongoCore::mongo() -> selectCollection($order_mongo_tab);
        $res = $Order -> findOne([],array(
            'sort' => array('id' => -1),
//            'projection' => array('id' => 1,'updated'=>1)
        ));
        var_dump($res);exit();
    }
    
    public function mysqlAction(){
        $Core = new \Db\Master\Master();
        while(true){
            $Core::pdo() -> checkPing();
            sleep(1);
        }
    }

    public function oneAction(){
        while(true){
            $i++;
            echo 'one:',$i,PHP_EOL;
            sleep(5);
        }
    }
    
    public function twoAction(){
        while(true){
            $i++;
            echo 'two:',$i,PHP_EOL;
            sleep(5);
        }
    }
    
    public function threeAction(){
        while(true){
            $i++;
            echo 'three:',$i,PHP_EOL;
            sleep(5);
        }
    }
    
    public function fourAction(){
        while(true){
            $i++;
            echo 'four:',$i,PHP_EOL;
            sleep(5);
        }
    }
    
    
    
}
