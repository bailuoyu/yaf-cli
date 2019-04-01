<?php
/*
 * 2018-11-20 cat
 * 开采网数据采集
 */
namespace Lottery\Collecter\Opencai;

class Opencai{
    
    public static $Url,$Token;

    /*
     * 初始化请求参数
     */
    public function __construct(){
        if(self::$Token){
        }else{
            $Config = \Yaf\Application::app() -> getConfig() -> lottery -> opencai;
            self::$Url   = $Config -> url;
            self::$Token = $Config -> token;
        }
    }
    
    /*
     * 发送请求
     * 只用josn格式
     */
    public function request(array $params=[],$timeout=5){
        $Curl = new \Curl\Curl();
        //过期时间设置为5秒
        $Curl -> setTimeout(5);
        $params['token'] = self::$Token;
        $params['format'] = 'json';
        $url = self::$Url .'?'.http_build_query($params);   //urldecode(http_build_query($params))
        $Curl -> setTimeout($timeout);     //超时时间设置
        $Curl -> get($url);
        if($Curl->error){
            //记录错误日志
            $e = 'Url:'.$url.PHP_EOL.$Curl->errorCode.': '.$Curl->errorMessage;
            $this -> errorLog($e);
            return false;
        }else{
            $res['url'] = $url;
            $res['response'] = $Curl->response;
            $res['result'] = json_decode($Curl->response,true);   //结果以数组形式返回
            return $res;
        }
    }
    
    /*
     * 记录错误日志
     */
    public function errorLog($e='error'){
        $Log = new \Log\File();
        $Log -> libError($e,_CLASS_);
    }
    
    /*
     * 获取最新
     * @$code 彩票代号，不填则取全部,支持数组和逗号分隔的字符串
     * @$row 取得行数，不填默认取3行
     */
    public function news($code=null,$row=3){
        $params = array();
        if($code){
            if(is_array($code)){
                $params['code'] = implode(',',$params);
            }else{
                $params['code'] = $code;
            }
        }
        if($row){
            $params['row'] = getRange(1,5,$row);
        }
        return $this -> request($params);
    }
    
    /*
     * 条件查询
     */
    public function select(){
    }



    
}
