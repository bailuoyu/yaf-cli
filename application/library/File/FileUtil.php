<?php
/*
 * 2018-11-05 cat
 * php文件操作类
 */
namespace File;
/**
 * 操纵文件类
 * 
 * 例子：
 * FileUtil::createDir('a/1/2/3');                    测试建立文件夹 建一个a/1/2/3文件夹
 * FileUtil::createFile('b/1/2/3');                    测试建立文件        在b/1/2/文件夹下面建一个3文件
 * FileUtil::createFile('b/1/2/3.exe');             测试建立文件        在b/1/2/文件夹下面建一个3.exe文件
 * FileUtil::copyDir('b','d/e');                    测试复制文件夹 建立一个d/e文件夹，把b文件夹下的内容复制进去
 * FileUtil::copyFile('b/1/2/3.exe','b/b/3.exe'); 测试复制文件        建立一个b/b文件夹，并把b/1/2文件夹中的3.exe文件复制进去
 * FileUtil::moveDir('a/','b/c');                    测试移动文件夹 建立一个b/c文件夹,并把a文件夹下的内容移动进去，并删除a文件夹
 * FileUtil::moveFile('b/1/2/3.exe','b/d/3.exe'); 测试移动文件        建立一个b/d文件夹，并把b/1/2中的3.exe移动进去                   
 * FileUtil::unlinkFile('b/d/3.exe');             测试删除文件        删除b/d/3.exe文件
 * FileUtil::unlinkDir('d');                      测试删除文件夹 删除d文件夹
 */
class FileUtil {
    
    /**
     * 建立文件夹
     *
     * @param string $aimUrl
     * @return viod
     */
    function createDir($aimUrl) {
        $aimUrl = str_replace('', '/', $aimUrl);
        $aimDir = '';
        $arr = explode('/', $aimUrl);
        $result = true;
        foreach ($arr as $str) {
            $aimDir .= $str . '/';
            if (!file_exists($aimDir)) {
                $result = mkdir($aimDir);
            }
        }
        return $result;
    }

    /**
     * 建立文件
     *
     * @param string $aimUrl 
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function createFile($aimUrl, $overWrite = false) {
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            FileUtil :: unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil :: createDir($aimDir);
        if(touch($aimUrl)){
            return true;
        }else{
            throw new \Exception('系统操作失败');
        }
    }

    /**
     * 移动文件夹
     *
     * @param string $oldDir
     * @param string $aimDir
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function moveDir($oldDir, $aimDir, $overWrite = false) {
        if(!$aimDir){return false;}
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if(!$oldDir){return false;}
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            FileUtil :: createDir($aimDir);
        }
        @$dirHandle = opendir($oldDir);
        if (!$dirHandle) {
            return false;
        }
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                FileUtil :: moveFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                FileUtil :: moveDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        closedir($dirHandle);
        return rmdir($oldDir);
    }

    /**
     * 移动文件
     *
     * @param string $fileUrl
     * @param string $aimUrl
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function moveFile($fileUrl, $aimUrl, $overWrite = false) {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite = false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite = true) {
            FileUtil :: unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil :: createDir($aimDir);
        if(rename($fileUrl, $aimUrl)){
            return true;
        }else{
            throw new \Exception('系统操作失败');
        }
    }

    /**
     * 删除文件夹及其子文件
     *
     * @param string $aimDir
     * @return boolean
     */
    function unlinkDir($aimDir) {
        if(!$aimDir){return false;}
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir . $file)) {
                FileUtil :: unlinkFile($aimDir . $file);
            } else {
                FileUtil :: unlinkDir($aimDir . $file);
            }
        }
        closedir($dirHandle);
        return rmdir($aimDir);
    }

    /**
     * 删除文件夹下的所有子文件
     *
     * @param string $aimDir
     * @return boolean
     */
    function unlinkDirFile($aimDir) {
        if(!$aimDir){return false;}
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($aimDir . $file)) {
                FileUtil :: unlinkFile($aimDir . $file);
            } else {
                FileUtil :: unlinkDir($aimDir . $file);
            }
        }
        closedir($dirHandle);
        //return rmdir($aimDir);
    }

    /**
     * 删除文件
     *
     * @param string $aimUrl
     * @return boolean
     */
    function unlinkFile($aimUrl) {
        if (file_exists($aimUrl)) {
            if(unlink($aimUrl)){
                return true;
            }else{
                throw new \Exception('系统操作失败');
            }
        } else {
            return false;
        }
    }

    /**
     * 复制文件夹
     *
     * @param string $oldDir
     * @param string $aimDir
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function copyDir($oldDir, $aimDir, $overWrite = false) {
        if(!$aimDir){return false;}
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        $oldDir = str_replace('', '/', $oldDir);
        $oldDir = substr($oldDir, -1) == '/' ? $oldDir : $oldDir . '/';
        if (!is_dir($oldDir)) {
            return false;
        }
        if (!file_exists($aimDir)) {
            FileUtil :: createDir($aimDir);
        }
        $dirHandle = opendir($oldDir);
        while (false !== ($file = readdir($dirHandle))) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (!is_dir($oldDir . $file)) {
                FileUtil :: copyFile($oldDir . $file, $aimDir . $file, $overWrite);
            } else {
                FileUtil :: copyDir($oldDir . $file, $aimDir . $file, $overWrite);
            }
        }
        return closedir($dirHandle);
    }

    /**
     * 复制文件
     *
     * @param string $fileUrl
     * @param string $aimUrl
     * @param boolean $overWrite 该参数控制是否覆盖原文件
     * @return boolean
     */
    function copyFile($fileUrl, $aimUrl, $overWrite = false) {
        if (!file_exists($fileUrl)) {
            return false;
        }
        if (file_exists($aimUrl) && $overWrite == false) {
            return false;
        } elseif (file_exists($aimUrl) && $overWrite == true) {
            FileUtil :: unlinkFile($aimUrl);
        }
        $aimDir = dirname($aimUrl);
        FileUtil :: createDir($aimDir);
        if(copy($fileUrl, $aimUrl)){
            return true;
        }else{
            throw new \Exception('系统操作失败');
        }
    }
    
    /**
     * 删除文件夹中24小时前的文件
     * @param unknown $aimDir
     * @param number $timeout
     * @return boolean
     */
    function clearFileByTime($aimDir,$timeout=86400) {  //删除文件夹中24小时前的文件
        if(!$aimDir){return false;}
        $aimDir = substr($aimDir, -1) == '/' ? $aimDir : $aimDir . '/';
        if (!is_dir($aimDir)) {
            return false;
        }
        $dirHandle = opendir($aimDir);
        $time = time()-$timeout;
        while(false !== ($file = readdir($dirHandle))){
            if($file == '.' || $file == '..'){
                continue;
            }
            $fullpath = $aimDir.$file;
            if(is_dir($fullpath)){
                $this -> clearFileBytime($fullpath,$timeout);
                //试图删除空文件夹
                rmdir($fullpath);
            }else{
                if(filemtime($fullpath)<$time){
                    $this -> unlinkFile($fullpath);
                }
            }
        }
        closedir($dirHandle);
    }
}