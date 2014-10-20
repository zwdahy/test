<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 文件操作基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
*/

class BaseModelFile {
    
    protected $path;
    
    protected $location;
    
    /*
     * @param $path 文件路径，相对路径，不用传入第一个斜线，如：abc/a.jpg
     * @param $location cache|data|vfs 默认为cache
     */
    public function __construct($path, $location = 'cache') {
        if (PLATFORM == 'sae') {
            $path = ltrim($path, "./");
        }
        switch ($location) {
            case 'data':
                $this->path = PATH_DATA . $path;
            break;
            case 'cache':
                $this->path = PATH_CACHE . $path;
            break;
            case 'log':
                $this->path = PATH_APPLOG . $path;
            break;
            default:
                $this->path = $path;
            break;
        }
        $this->location = $location;
    }
    
    /*
     * 内容读取
     * 错误返回false，正确返回读取内容
     */
    public function read() {
        BaseModelCommon::debug($this->path, 'file_read');
        return @file_get_contents($this->path);
    }
    
    /*
     * 内容写入
     * 错误返回false，正确返回写入字节数
     */
    public function write($str) {
        $dirName = dirname($this->path);
        BaseModelCommon::recursiveMkdir($dirName);
        BaseModelCommon::debug($this->path, 'file_write');
        return file_put_contents($this->path, $str);
    }
    
    /*
     * 内容追加
     * 错误返回false，正确返回写入字节数
     */
    public function writeTo($str) {
        if (PLATFORM == 'sae') {
            if ($this->location == 'log') {
                ini_set("display_errors","Off");
                sae_debug($str);
                ini_set("display_errors","On");
            } else {
                BaseModelMessage::showError("SAE不支持追加写入功能。");
            }
        } else {
            $dirName = dirname($this->path);
            BaseModelCommon::recursiveMkdir($dirName);
            $fp = fopen($this->path, "a");
            $num = fwrite($fp, $str);
            fclose($fp);
            return $num;
        }
    }
}
?>
