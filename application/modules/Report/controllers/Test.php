<?php
/*
 * 2019-01-14
 * 报表测试数据
 */
class TestController extends CommonController {
    
    /*
     * 拉取数据
     */
    public function pullAction(){
        $params = $this -> getRequest() -> getParams();
        $tid = $params[0];
        //声明mysql
        $DbCore = new \Db\Hall\Core($tid);
        //声明mongo
        $MongoCore = new \Nosql\MongoHall\Core($tid);
        //流水表
        $log_tab = '`funds_deal_log`';
        //代理表
        $agent_tab = '`agent`';
        //**查询mysql**//
        $id = 0;
        $limit = 100;    //查询跨度
        //预处理语句
        $stmt_sql = 
            "select l.*,a.name,a.pname,a.pid,a.pid_join ".
            "from {$log_tab} as l,{$agent_tab} as a ".
            "where l.id>:id and l.user_id=a.id ".
            "order by id ".
            "limit :limit";
        //**这里很重要,否则mongo中会将int存入string类型，需要php安装php-mysqlnd扩展而不是php-mysql扩展
        $DbCore::pdo() -> setAttribute($DbCore::pdo()::ATTR_STRINGIFY_FETCHES,false);      //禁止将数字转换为字符串
//        $DbCore::pdo() -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);      //或者这样写
        $stmt = $DbCore::pdo() -> prepare($stmt_sql);
        //**mongo选择Collection
        $log_mongo_tab = 'funds_deal_log';
        $FundsDealLog = $MongoCore::mongo() -> selectCollection($log_mongo_tab);
        $start_time = time();
        do{
            $bind_params = array(
                'id' => $id,
                'limit' => $limit
            );
            $stmt -> execute($bind_params);
            $info = $stmt -> fetchAll();
            //**写入mongo**//
            $FundsDealLog -> InsertMany($info);
            $end = end($info);
            $id = $end['id'];
        }while(count($info)==$limit);
        $end_time = time();
        echo $end_time - $start_time;
    }
}
