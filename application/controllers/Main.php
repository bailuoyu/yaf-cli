<?php
/*
 * 2019-01-03 cat
 * 总控脚本,没有使用
 */
class MainController extends CommonController {

    /*
     * 守护进程
     */
    public function daemonAction(){
        $cmd1 = 'php yaf.php test/test/one > /dev/null 2>&1 &';
//        system($cmd1, $res1);
//        exec($cmd1, $res1);
//        var_dump($res1);
        $cmd2 = 'php yaf.php test/test/two > /dev/null 2>&1 &';
//        system($cmd2, $res2);
//        exec($cmd2, $res2);
//        var_dump($res2);
        $cmd3 = 'php yaf.php test/test/three > /dev/null 2>&1 &';
//        system($cmd3, $res3);
//        exec($cmd3, $res3);
//        var_dump($res3);
        $cmd4 = 'php yaf.php test/test/four > /dev/null 2>&1 &';
//        system($cmd4, $res4);
//        exec($cmd4, $res4);
//        var_dump($res4);
        $cmd_r = array($cmd1,$cmd2,$cmd3,$cmd4);
        foreach ($cmd_r as $cmd) {
//            $pid = pcntl_fork();
            //父进程执行程序
//            if($pid){
//                echo $pid,PHP_EOL;
//            }else{  //子进程执行程序
                system($cmd,$code);
//                exec($cmd, $res ,$code);
                var_dump($code);
//            }
        }
    }
    
    /*
     * 守护进程
     */
    public function daemon2Action(){
//        $cmd1 = 'cd /usr/www/viba_cli; nohup php yaf.php test/test/one &';
//        $cmd1 = 'php yaf.php test/test/one &';
//        $pid = pcntl_fork();
//        echo 'pid:',$pid;
//        system($cmd1, $res1);
//        exec($cmd1, $res1);
//        var_dump($res1);
//        exit();
        $pid = pcntl_fork();

        if ($pid == -1)
        {
            throw new Exception('fork子进程失败');
        }
        elseif ($pid > 0)
        {
            //父进程退出,子进程不是进程组长，以便接下来顺利创建新会话
            exit(0);
        }

        // 最重要的一步，创建一个新的会话，脱离原来的控制终端
        posix_setsid();
        // 由于守护进程用不到标准输入输出，关闭标准输入，输出，错误输出描述符
//        fclose(STDIN);
//        fclose(STDOUT);
//        fclose(STDERR);

/*
 * 通过上一步，我们创建了一个新的会话组长，进程组长，且脱离了终端，但是会话组长可以申请重新打开一个终端，为了避免
 * 这种情况，我们再次创建一个子进程，并退出当前进程，这样运行的进程就不再是会话组长。
 */
        $pid = pcntl_fork();
        if ($pid == -1)
        {
            throw new Exception('fork子进程失败');
        }
        elseif ($pid > 0)
        {
            //  再一次退出父进程，子进程成为最终的守护进程
            exit(0);
        }
        
        while(true){
            $i++;
            echo $i,PHP_EOL;
            sleep(5);
        }
        
    }
    
}
