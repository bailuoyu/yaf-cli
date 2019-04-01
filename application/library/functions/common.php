<?php
/*
 * 2018-10-29 cat
 * 通用函数
 */

//生成随机字符串
function randomStr($length=16,$type=0){
    switch((int)$type){
        case 1:     //数字和字母小写
            $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
            break;
        case 2:     //16进制数字符串
            $chars = '0123456789abcdef';
            break;
        default :   //数字和字母大小写
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
    }
    $max = strlen($chars)-1;
//    mt_srand();     //防止随机种子爆破
    for($i=0;$i<$length;$i++){
//        $str .= $chars[mt_rand(0,$max)];
        $str .= $chars[random_int(0,$max)];     //php7新函数random_int()取代mt_rand()为加密安全
    }
    return $str;
}

//第一个数组获取第二个数组指定的键和值
function copyKeyValue(&$get_r,$copy_r,$key_r){
    foreach ($key_r as $_v) {
        $get_r[$_v] = $copy_r[$_v];
    }
}

//',','|'等形式字符串取交集
function strIntersect($strA,$strB,$sp=','){
    $arA = explode($sp,$strA);
    $arB = explode($sp,$strB);
    $ar_diff = array_intersect($arA,$arB);
    return implode($sp,$ar_diff);
}

//将参数$x取[$m,$n]范围值,如果$x超出范围取对应最大或最小极值
function getRange($m,$n,$x){
    if($x<$m){
        $x = $m;
    }elseif($x>$n){
        $x = $n;
    }
    return $x;
}