<?php
/*
 * 2019-01-14 cat
 * 流水数据处理
 */
class FundsdeallogController extends CommonController {
    /*
     * 拉取数据
     */
    public function pullAction(){
        //厅主表
        $hall_tab = '`hall_list`';
        //流水表
        $log_tab = '`funds_deal_log`';
        //代理表
        $agent_tab = '`agent`';
        //**mongo选择Collection
        $log_mongo_tab = 'funds_deal_log';
        //声明mysql master
        $DbMaster = new \Db\Master\Master();
        //查询厅主
        $Query = $DbMaster::pdo() -> query("select id from {$hall_tab} where status>0");
        $start_time = time();
        foreach($Query as $row){
            $tid = $row['id'];
            //声明mongo
            $MongoCore = new \Nosql\MongoHall\Core($tid);
            //声明mysql core
            $DbCore = new \Db\Hall\Core($tid);
            //查询id最大值
            $MaxQuery = $DbCore::pdo() -> query("select max(id) from {$log_tab}");
            $max_r = $MaxQuery -> fetch();
            $max_id = $max_r['max(id)'];
            //**查询mysql**//
            $limit = 100;    //查询跨度
            $offset = $limit - 1;
            //预处理语句，**这里很重要，需要先查询出范围,否则联表mysql会产生大量数据扫描
            $end_stmt_sql = "select id from {$log_tab} where id>:start_id order by id limit {$offset},1";
            //预处理语句
            $stmt_sql = 
                "select l.*,a.name,a.pname,a.pid,a.pid_join ".
                "from {$log_tab} as l,{$agent_tab} as a ".
                "where l.id>:start_id and l.id<=:end_id and l.user_id=a.id ".
                "order by l.id ";
            //**这里很重要,否则mongo中会将int存入string类型，需要php安装php-mysqlnd扩展而不是php-mysql扩展
            $DbCore::pdo() -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);      //禁止将数字转换为字符串
//            $DbCore::pdo() -> setAttribute($DbCore::pdo()::ATTR_STRINGIFY_FETCHES,false);      //或者这样写
            $end_stmt = $DbCore::pdo() -> prepare($end_stmt_sql);
            $stmt = $DbCore::pdo() -> prepare($stmt_sql);
//            var_dump($MongoCore::mongo());
            $FundsDealLog = $MongoCore::mongo() -> selectCollection($log_mongo_tab);
            $res = $FundsDealLog -> findOne([],array(
                'sort' => array('id' => -1),
                'projection' => array('id' => 1)
            ));
//            var_dump($res);exit();
            $id = (int)$res['id'];
            do{
//                $c_start_time = time();
                $end_bind_params = array(
                    'start_id' => $id
                );
                $end_stmt -> execute($end_bind_params);
                $end_r = $end_stmt -> fetch();  
                if($end_r){
                    $end_id = $end_r['id'];
                }else{
                    $end_id = $max_id;
                }
                
                $bind_params = array(
                    'start_id' => $id,
                    'end_id' => $end_id
                );
                $stmt -> execute($bind_params);
//                $c_end_time = time();
//                echo 'time:',$c_end_time - $c_start_time,PHP_EOL;
                $info = array();
                while($r = $stmt -> fetch()){
                    $r['deal_number'] = (string)$r['deal_number'];
                    $r['deal_money'] = (double)$r['deal_money'];
                    $r['coupon_money'] = (double)$r['coupon_money'];
                    $r['balance'] = (double)$r['balance'];
                    $info[] = $r;
                }
//                $info = $stmt -> fetchAll();
                //**写入mongo**//
                if($info){
                    $FundsDealLog -> InsertMany($info);
                }
                $id = $end_id;
            }while($id<$max_id);
            //释放mysql和mongo连接
            $DbCore -> close();
            $MongoCore -> close();
        }
        $end_time = time();
        echo 'time:',$end_time - $start_time;
    }
    
}
