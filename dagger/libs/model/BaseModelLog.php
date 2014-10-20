<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * LOG基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
*/

class BaseModelLog {
    
    const PATH_LOG_FILE = 'admin_log/log';

    /**
     * 日志写入
     * @param mixed $params 可以是key/value的数组，也可以是字符串
     */
    static public function writeLite ($params) {
        $time = date("Y-m-d H:i:s");
        ob_start();
        if (is_array($params)) {
            PrintArr::out($params);
        } else if (is_object($params)) {
            PrintArr::out((array)$params);
        } else {
            echo $params."\n";
        }
        $contents = ob_get_contents();
        ob_end_clean();
        $str = $time . "\n" . $contents . "\n";
        $file = new BaseModelFile(self::PATH_LOG_FILE, 'cache');
        $file->writeTo($str);
    }
	
    /**
     * 日志写入，默认写入文件
     * 各自项目可以创建数据表继承重写本函数
     * @param $user     操作者
     * @param $ip       操作IP
     * @param $pk       操作数据主键
     * @param $action   操作动作
     * @param $status   操作结果状态 0|1
     * @param $desc     描述
     * @return void
     */
    static public function write($user, $ip, $pk, $action, $status, $desc = '') {
        $time = date("Y-m-d H:i:s");
        $str = $time . "\t" . $user . "\t" . $ip . "\t" . $pk . "\t" . $action . "\t" . $status . "\t" . $desc . "\n";
        $file = new BaseModelFile(self::PATH_LOG_FILE, 'log');
        $file->writeTo($str);
    }
    
    /**
     * 将数组转为字符串记录日志使用
     * @param $arr array 需要记录日志的数组
     * @return string
     */
    static public function arrayToLog($arr) {
        $str = '';
        if (is_array($arr)) {
            foreach ($arr as $k=>$v) {
                $str .= "[{$k}]:" . $v . " ";
            }
        }
        return $str;
    }

    /**
     * 向监控大厅提交报警
     * @param int $type 报警类型
     * @param string $msg 报警消息
     *
     */
    static public function sendLog($type, $msg){
        $trace = debug_backtrace();
        $myself = array_shift($trace);
        $t = date('Y-m-d H:i:s');
        if(!empty($trace[0])){
            $lastCall = $trace[0];
            $msg = "time: $t\nfile: {$lastCall['file']}\nfrom: {$lastCall['class']}{$lastCall['type']}{$lastCall['function']}()\nline: {$lastCall['line']}\nmsg: {$msg}\n";
        }else{
            $lastCall = $myself;
            $msg = "time: $t\nfile: {$lastCall['file']}\nline: {$lastCall['line']}\nmsg: {$msg}\n";
        }
        $file = new BaseModelFile(self::PATH_LOG_FILE, 'log');
        $file->writeTo($msg);
        //BaseModelHttp::post(MSGCENTER, array('type'=>$type, 'msg'=>$msg));
    }
}
