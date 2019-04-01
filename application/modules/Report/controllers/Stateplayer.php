<?php
/*
 * 2019-01-21 cat
 * 玩家报表数据处理
 */
class StateplayerController extends CommonController {
    
    /*
     * 拉取数据
     */
    public function pullAction(){
        //厅主表
        $hall_tab = '`hall_list`';
        //玩家记录表
        $play_tab = '`state_player`';
        //代理表
        $agent_tab = '`agent`';
        //**mongo选择Collection
        $log_mongo_tab = 'state_player';
        //声明mysql master
        $DbMaster = new \Db\Master\Master();
        //查询厅主
        $Query = $DbMaster::pdo() -> query("select id from {$hall_tab} where status>0");
        $start_time = time();
        foreach($Query as $row){
            $tid = $row['id'];
            //声明mongo,放在core库中
            $MongoCore = new \Nosql\MongoHall\Core($tid);
            //声明mysql data
            $DbData = new \Db\Hall\Data($tid);
            $data_db = $DbData::pdo() -> dbName();
            //声明mysql core
            $DbCore = new \Db\Hall\Core($tid);
            $core_db = $DbCore::pdo() -> dbName();
            //查询id最大值
            $MaxQuery = $DbData::pdo() -> query("select max(id) from {$play_tab}");
            $max_r = $MaxQuery -> fetch();
            $max_id = $max_r['max(id)'];
            //**查询mysql**//
            $limit = 100;    //查询跨度
            $offset = $limit - 1;
            //预处理语句，**这里很重要，需要先查询出范围,否则联表mysql会产生大量数据扫描
            $end_stmt_sql = "select id from {$play_tab} where id>:start_id  order by id limit {$offset},1";
            //预处理语句
            $stmt_sql = 
                "select p.*,a.name,a.pname,a.pid,a.pid_join ".
                "from {$data_db}.{$play_tab} as p,{$core_db}.{$agent_tab} as a ".
                "where p.id>:start_id and p.id<=:end_id and p.user_id=a.id ".
                "order by p.id ";
            //**这里很重要,否则mongo中会将int存入string类型，需要php安装php-mysqlnd扩展而不是php-mysql扩展
            $DbCore::pdo() -> setAttribute(PDO::ATTR_STRINGIFY_FETCHES,false);      //禁止将数字转换为字符串
//            $DbData::pdo() -> setAttribute($DbData::pdo()::ATTR_STRINGIFY_FETCHES,false);      //或者这样写
            $end_stmt = $DbData::pdo() -> prepare($end_stmt_sql);
            $stmt = $DbData::pdo() -> prepare($stmt_sql);
            $StatePlayer = $MongoCore::mongo() -> selectCollection($log_mongo_tab);
            $res = $StatePlayer -> findOne([],array(
                'sort' => array('id' => -1),
                'projection' => array('id' => 1)
            ));
            $id = (int)$res['id'];
            do{
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
                $info = array();
                while($r = $stmt -> fetch()){
                    $r['bonus'] = (double)$r['bonus'];
                    $r['contri'] = (double)$r['contri'];
                    $info[] = $r;
                }
    //            $info = $stmt -> fetchAll();
                //**写入mongo**//
                if($info){
                    $StatePlayer -> InsertMany($info);
                }
                $id = $end_id;
            }while($id<$max_id);
            //释放mysql和mongo连接
            $DbData -> close();
            $MongoCore -> close();
        }
        $end_time = time();
        echo 'time:',$end_time - $start_time;
    }
    
}

