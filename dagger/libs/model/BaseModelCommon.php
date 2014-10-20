<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * @abstract        通用函数
 * @author          wangxin <wangxin3@staff.sina.com.cn> 
 * @since           2011/3/2 11:48
 * @version         1.0
 */
class BaseModelCommon {

    static private $onlineDebugData = array();
    
    /**
     * 禁止被实例化，静态调用
     */
    private function __construct() {
        return;
    }

    /**
     * 二维数组根据自动字段排序
     * @param array $data 需要排序的数组
     * @param string $orderby_key 依据字段
     * @param string $type 排序方式，desc|asc
     * @return array 排序完成数组
     */
    static public function arrayOrderBy($arr, $orderby_key, $type = 'ASC') {
        $col = array();
        foreach ($arr as $key => $value)
        {
            $col[$key] = $value[$orderby_key];
        }

        $type = (strtoupper($dir) == "ASC" ? SORT_ASC : SORT_DESC);

        array_multisort($col, $type, $arr);
        return $arr;
    }

    /**
     * 转码函数
     * @param Mixed $data 需要转码的数组
     * @param String $dstEncoding 输出编码
     * @param String $srcEncoding 传入编码
     * @param bool $toArray 是否将stdObject转为数组输出
     * @return Mixed
     */
    static public function convertEncoding($data, $dstEncoding, $srcEncoding, $toArray=false) {
        if ($toArray && is_object($data)) {
            $data = (array)$data;
        }
        if (!is_array($data) && !is_object($data)) {
            $data = mb_convert_encoding($data, $dstEncoding, $srcEncoding);
        } else {
            if (is_array($data)) {
                foreach($data as $key=>$value) {
                    if (is_numeric($value)) {
                        continue;
                    }
                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data[$key]);
                    $data[$keyDstEncoding] = $valueDstEncoding;
                }
            } else if(is_object($data)) {
                $dataVars = get_object_vars($data);
                foreach($dataVars as $key=>$value) {
                    if (is_numeric($value)) {
                        continue;
                    }
                    $keyDstEncoding = self::convertEncoding($key, $dstEncoding, $srcEncoding, $toArray);
                    $valueDstEncoding = self::convertEncoding($value, $dstEncoding, $srcEncoding, $toArray);
                    unset($data->$key);
                    $data->$keyDstEncoding = $valueDstEncoding;
                }
            }
        }
        return $data;
    }

    /**
     * 递归创建目录，SAE平台不生效
     * @param string $pathname 需要创建的目录路径
     * @param int $mode 创建的目录属性，默认为700
     * @return void
     */
    public static function recursiveMkdir($pathname, $mode=0700) {
        if (PLATFORM == 'sae') {
        } else {
            is_dir(dirname($pathname)) || self::recursiveMkdir(dirname($pathname), $mode);
            return is_dir($pathname) || mkdir($pathname, $mode);
        }
    }

    /**
     * 返回程序开始到调用函数处的执行时间
     * @return string 运行此函数调用的时间
     */
    static public function getRunTime() {
        return sprintf("%0.3f", microtime(true) - STARTTIME) . " s";
    }

    /**
     * 调试信息打印
     * @param mixed $value 需要打印的调试信息
     * @param string $type 需要打印的调试信息的类型，默认为：DEBUG
     * @param bool/int $verbose 是否缩略输出，默认为false，但可以制定缩略长度
     * @return void
     */
    static public function debug($value, $type = 'DEBUG', $verbose = false) {
        if (defined("DAGGER_DEBUG")) {
            //调试时正则匹配需要输出的内容
            $debugTypeFilter = isset($_GET[DEBUG_ARG_NAME]) ? $_GET[DEBUG_ARG_NAME] : (isset($_COOKIE['dagger_debug_type']) ? $_COOKIE['dagger_debug_type'] : '');
            $debugArgs = array_filter(explode(';', $debugTypeFilter));
            if (empty($debugArgs)) {
                $output = true;
            } else {
                foreach ($debugArgs as $arg) {
                    $output = (strpos($arg, '!') !== false) && $arg = ltrim($arg, '!');;
                    if (preg_match("/^{$arg}/", $type)) { 
                        $output = !$output;
                        break;
                    }
                }
            } 
            if ($output) {
                if (defined('QUEUE')) {
                    self::queueOut($type, $value, $verbose);
                } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') !== false) {
                    if ($type === 'db_sql' && preg_match("/^UPDATE|DELETE|INSERT|REPLACE|ALTER|TRUNCATE|CREATE/i", $value)) {
                        FirePHP::getInstance(true)->warn($value, $type);
                    } elseif ($type === 'db_sql_result' ) {
                        FirePHP::getInstance(true)->table('db_sql_result', $value);
                    } elseif ($type === 'request_return') {
                        FirePHP::getInstance(true)->table('request_result', $value);
                    } elseif ($type === 'trace') {
                        FirePHP::getInstance(true)->trace($value);
                    } elseif ($type === 'error') {
                        FirePHP::getInstance(true)->error($value);
                    } elseif ($type === 'info') {
                        FirePHP::getInstance(true)->info($value);
                    } elseif ($type === 'warn') {
                        FirePHP::getInstance(true)->warn($value);
                    } else {
                        FirePHP::getInstance(true)->log($value, $type);
                    }
                } else {
                    // 预留debug信息输出
                }
            }
        } 
        if (!empty($_COOKIE['dagger_online_debug'])) {
            self::$onlineDebugData[$type] = $value;
        }   
    }

    public static function queueOut($type, $msg = '', $verbose = false) {
        echo date("[Y-m-d H:i:s]") . " {$type}: ";
        if (is_array($msg)) {
            echo "\n";
            PrintArr::out($msg, $verbose);
        } else {
            echo $msg;
        }
        echo "\n";
    }

    /**
     * online debug推送到监控中心
     * @return void
     */
    static public function sendOnlineDebug() {
        if (!empty($_COOKIE['dagger_online_debug'])) {
            $post = array(
                    'pid' => defined("PROJECT_ID") ? PROJECT_ID : 1,
                    'domain' => $_SERVER['HTTP_HOST'],
                    'debug_msg' => json_encode(self::$onlineDebugData),
                    'html' => ob_get_contents(),
                    'request' => json_encode(apache_request_headers()),
                    'response' => json_encode(apache_response_headers()),
                    'client_ip' => Ip::getClientIp()
                    );
            BaseModelHttp::post("http://i.alarm.mix.sina.com.cn/?s=report&a=debug&format=json", http_build_query($post));
        }
    }

    /**
     * 类/函数名构造
     * @param $name String 传入名
     * @param $type String 类型：function | class
     * @return string 类/函数名
     */
    static public function getFormatName($name, $type = 'function') {
        $name = explode('_', $name);
        for($i = 0, $len = count($name); $i < $len; $i++ ) {
            if ($type != "class" && $i == 0) {
                $name[$i] = strtolower($name[$i]);
            } else {
                $name[$i] = ucfirst(strtolower($name[$i]));
            }
        }
        return implode('', $name);
    }


    /**
     * checkbox选框值格式化
     * @param $arr arr 传入多选数组
     * @return string 格式化后的字符串
     */
    static public function checkboxStrEncode($arr) {
        $str = 'SELECTED:[';
        if(is_array($arr)){
            foreach($arr as $v){
                $str .= "'".$v."',";
            }
        }
        $str = rtrim($str, ",");
        $str .= ']';
        return $str;
    }

    /**
     * checkbox选框值反格式化
     * @param $arr arr 传入格式化后的字符串
     * @return string 多选数组
     */
    static public function checkboxStrDecode($str) {
        $arr = $matches = array();
        preg_match("/^SELECTED:\[(.*)\]$/", $str, $matches);
        $str = str_replace('\'','"',"[".$matches[1]."]");
        $arr = json_decode($str,true);
        return $arr;
    }

    /**
     * 根据二维数组字段生成一维数组
     * @param array $arr 传入的二位数组
     * @param string $key 作为key的字段
     * @param string $value 作为value的字段
     * @return string 构造完成的一维数组
     */
    static public function createArr($arr, $key, $value) {
        $newArr = array();
        foreach ($arr as $v) {
            $newArr[$v[$key]] = $v[$value];
        }
        return $newArr;
    }
}

/**
 * 
 * @abstract        队列数组输出
 */
class PrintArr
{
    static private $layer = array(0);

    public static function out($value, $verbose=false){
        ob_start();
        self::p($value, $verbose);
        $arr = ob_get_contents();
        if(function_exists('posix_isatty')){
            if(posix_isatty(STDOUT)){
                $arr = str_replace(array('[', ']', '{', '}'), array("\033[0;32;1m[\033[0m", "\033[0;32;1m]\033[0m", "\033[0;37;44m{\033[0m", "\033[0;37;44m}\033[0m"), $arr);
            }
        }
        ob_end_clean();
        echo $arr;
    }

    private static function p($value, $verbose=false){
        if(is_array($value)){
            $i = array_pop(self::$layer);
            $i++;
            array_push(self::$layer, $i);
            foreach($value as $k=>$v){
                for($j=1; $j<$i; $j++){
                    echo '     ';
                }
                if(!is_array($v)){
                    if(is_numeric($verbose)){
                        $v = substr($v, 0, $verbose);
                    }else if($verbose === false){
                        $v = substr($v, 0, 30);
                    }
                    echo "[{$k}]=>[{$v}]\n";
                }else{
                    echo "[{$k}]=>{\n";
                    self::p($v);
                    for($j=1; $j<$i; $j++){
                        echo '     ';
                    }
                    echo "}\n";
                }
            }
            $i = array_pop(self::$layer);
            $i--;
            array_push(self::$layer, $i);            
        }
    } 
}
