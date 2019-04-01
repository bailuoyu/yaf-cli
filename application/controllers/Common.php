<?php
/*
 * 2018-11-21 cat
 * 通用控制器方法
 */
abstract class CommonController extends Yaf\Controller_Abstract {
    
    /*
     * 初始化载入
     */
    protected function init(){
//        $module = $this -> getModuleName();   //模块名
//        $this -> getRequest() -> application -> library = APP_PATH."/application/modules/Test";
//        echo $this -> getRequest() -> application -> library;exit();
        
    }
    
    /*
     ** 返回成功并退出脚本
     */
    protected function rSuccess($msg=''){
        if($msg){
        }else{
            $msg = 'success';
        }
        print_r($msg);
        exit();
    }
    
    /*
     ** 返回错误并退出脚本
     * @log 是否记录日志
     */
    protected function rError($e='error',$log=false){
        if($log){
            $module = $this -> getModuleName();   //模块名
            $controller = $this -> getRequest() -> getControllerName();   //控制器名
            $action = $this -> getRequest() -> getActionName();   //方法名
            $Log = new \Log\File();
            $Log -> ctrlError($e,$module,$controller,$action);
        }
        throw new \Error($e);
    }
    
}
