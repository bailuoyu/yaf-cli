<?php
/*
 * 2019-02-15 cat
 * 注单明细处理
 */
use MongoDB\BSON\UTCDateTime;
class OrderController extends CommonController {
    /*
     * 拉取数据
     */
    public function pullAction(){
        //厅主表
        $hall_tab = '`hall_list`';
        //注单表
        $order_tab = '`order`';
        //代理表
        $agent_tab = '`agent`';
        //彩种表
        $lottery_tab = '`lottery`';
        //输赢详情表
        $prize_tab = '`send_prize`';
        //**mongo选择Collection
        $order_mongo_tab = 'order_detail';
        //声明mysql master
        $DbMaster = new \Db\Master\Master();
        //查询厅主
        $Query = $DbMaster::pdo() -> query("select id from {$hall_tab} where status>0");
        //声明Redis
        $OrderRedis = new \Redis\Core\OrderModel();
        $start_time = time();
        foreach($Query as $row){
            $tid = $row['id'];
            $redis_key = $OrderRedis -> getKey('','update_time');
            $redis_field = 't'.$tid;
            //声明mongo
            $MongoCore = new \Nosql\MongoHall\Core($tid);
            //声明mysql core
            $DbCore = new \Db\Hall\Core($tid);
            //**查询mysql**//
            $limit = 100;    //查询跨度
            $offset = $limit - 1;
            //**这里很重要,否则mongo中会将int存入string类型，需要php安装php-mysqlnd扩展而不是php-mysql扩展
            $DbCore::pdo() -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);      //禁止将数字转换为字符串
            
            $Order = $MongoCore::mongo() -> selectCollection($order_mongo_tab);
            $res = $Order -> findOne([],array(
                'sort' => array('id' => -1),
                'projection' => array('id' => 1,'updated'=>1)
            ));
            //查询mysql当前时间
            $TimeQuery = $DbCore::pdo() -> query("select now()");
            $time_r = $TimeQuery -> fetch();
            $now_time = $time_r['now()'];
            $mongo_id = (int)$res['id'];
            $updated = $OrderRedis::redis()->hGet($redis_key,$redis_field);
            
            //实时修改
            if(!$res['id']||!$updated){
            }else{
                //查询需要更新的id最小值
                $MinQuery = $DbCore::pdo() -> query("select id from {$order_tab} where updated>'{$updated}' and id<=$mongo_id order by id limit 1");
                $min_r = $MinQuery -> fetch();
                $start_id = $min_r['id'];
                //预处理语句，**这里很重要，需要先查询出范围,否则联表mysql会产生大量数据扫描
                $new_end_stmt_sql = "select id from {$order_tab} where updated>'{$updated}' and id>:start_id and id<=$mongo_id order by id limit {$offset},1";
                //新更新的数据
                $new_stmt_sql = 
                    "select ".
                    "o.id,o.state,o.chase_amount,o.updated,p.lose_earn ".
                    "from {$order_tab} as o ".
                    "left join {$prize_tab} as p on o.order_number=p.order_number ".
                    "where o.id between :start_id and :end_id and o.updated>'{$updated}' ".
                    "order by o.id ";
                $new_end_stmt = $DbCore::pdo() -> prepare($new_end_stmt_sql);
                $new_stmt = $DbCore::pdo() -> prepare($new_stmt_sql);
                do{
                    $new_end_bind_params = array(
                        'start_id' => $start_id
                    );
                    $new_end_stmt -> execute($new_end_bind_params);
                    $new_end_r = $new_end_stmt -> fetch();
                    if($new_end_r){
                        $new_end_id = $new_end_r['id'];
                    }else{
                        $new_end_id = $mongo_id;
                    }
                    $new_bind_params = array(
                        'start_id' => $start_id,
                        'end_id' => $new_end_id
                    );
                    $new_stmt -> execute($new_bind_params);
                    while($nr = $new_stmt -> fetch()){
//                        echo json_encode($nr),PHP_EOL;
                        $re = $Order -> updateOne(
                            array('id'=>$nr['id']),
                            array(
                                '$set' => array(
                                    'state' => $nr['state'],
                                    'chase_amount' => $nr['chase_amount'],
                                    'updated' => $nr['updated'],
                                    'lose_earn' => $nr['lose_earn']
                                )
                            )
                        );
                    }
                    $start_id = $new_end_id;
                }while($start_id<$mongo_id);
            }
            //更新update时间
            $OrderRedis::redis()->hSet($redis_key,$redis_field,$now_time);
            
            //查询id最大值
            $MaxQuery = $DbCore::pdo() -> query("select max(id) from {$order_tab}");
            $max_r = $MaxQuery -> fetch();
            $max_id = $max_r['max(id)'];
            //预处理语句，**这里很重要，需要先查询出范围,否则联表mysql会产生大量数据扫描
            $end_stmt_sql = "select id from {$order_tab} where id>:start_id order by id limit {$offset},1";
            //预处理语句
            $stmt_sql = 
                "select ".
                "o.*,".
                "p.lose_earn,a.name,a.pname,a.pid,a.pid_join,".
                "l.name as lottery_name,h5_pic,".
                "p.lose_earn,p.money as send_money ".
                "from {$order_tab} as o ".
                "inner join {$agent_tab} as a on o.user_id=a.id ".
                "inner join {$lottery_tab} as l on o.lottery_id=l.id ".
                "left join {$prize_tab} as p on o.order_number=p.order_number ".
                "where o.id>:start_id and o.id<=:end_id ".
                "order by o.id ";
//            $DbCore::pdo() -> setAttribute($DbCore::pdo()::ATTR_STRINGIFY_FETCHES,false);      //或者这样写
            $end_stmt = $DbCore::pdo() -> prepare($end_stmt_sql);
            $stmt = $DbCore::pdo() -> prepare($stmt_sql);

            $id = $mongo_id;
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
                    $r['pid_join']=explode(',', $r['pid_join']);
                    $r['order_number'] = (string)$r['order_number'];
                    $r['lottery_number'] = (string)$r['lottery_number'];
                    $info[] = $r;
                }
//                $info = $stmt -> fetchAll();
                //**写入mongo**//
                if($info){
                    $Order -> InsertMany($info);
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
