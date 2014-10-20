<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 信息提示基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
 */

class BaseModelMessage {
    private function __construct() {
        return;
    }

    public static function succLite($args=array()){
        $args['msg'] = isset($args['msg']) ? $args['msg'] : '';
        $args['data'] = isset($args['data']) ? $args['data'] : array();
        $args['url'] = isset($args['url']) ? $args['url'] : '';
        $args['t'] = isset($args['t']) ? $args['t'] : 3;
        $args['ie'] = isset($args['ie']) ? $args['ie'] : '';
        $args['oe'] = isset($args['oe']) ? $args['oe'] : 'UTF-8';
        $args['otherData'] = isset($args['otherData']) ? $args['otherData'] : array();
        self::message(0, $args['msg'], $args['data'], $args['url'], $args['t'], $args['otherData'], $args['ie'], $args['oe']);
    }

    public static function errLite($args=array()){
        $args['code'] = isset($args['code']) ? $args['code'] : 11;
        $args['msg'] = isset($args['msg']) ? $args['msg'] : '';
        $args['data'] = isset($args['data']) ? $args['data'] : array();
        $args['url'] = isset($args['url']) ? $args['url'] : '';
        $args['t'] = isset($args['t']) ? $args['t'] : 3;
        $args['ie'] = isset($args['ie']) ? $args['ie'] : '';
        $args['oe'] = isset($args['oe']) ? $args['oe'] : 'UTF-8';
        self::message($args['code'], $args['msg'], $args['data'], $args['url'], $args['t'], array(), $args['ie'], $args['oe']);
    }

    public static function showSucc($msg, $data=array(), $otherData=array(), $url='', $t=3, $ie='', $oe='UTF-8') {
        self::message(0, $msg, $data, $url, $t, $otherData, $ie, $oe);
    }

    public static function showError($msg, $data=array(), $code=11, $url='', $t=3, $ie='', $oe='UTF-8') {
        self::message($code, $msg, $data, $url, $t, array(), $ie, $oe);
    }

    private static function message($code, $msg, $data, $url, $t, $otherData=array(), $ie='', $oe='UTF-8') {
    
        $format = empty($_REQUEST['format']) ? '' : strtolower($_REQUEST['format']);
        $oe = $format === 'json' ? 'UTF-8' : $oe;// 标准的json只支持utf8中文
        $code = intval($code);
        // 转码
        if(!empty($ie) && !strcasecmp($ie, $oe)) {
            $msg = BaseModelCommon::convertEncoding($msg, $oe, $ie);
            $data = BaseModelCommon::convertEncoding($data, $oe, $ie);
            $otherData = BaseModelCommon::convertEncoding($otherData, $oe, $ie);
        }

        //如果传入了fields字段，返回结果只显示指定字段，fields内容使用半角逗号隔开
        if (!empty($_GET['fields']) && is_array($data) && !empty($data) && (!isset($data['0']) || (!empty($data['0']) && is_array($data['0'])))) {
            $newData = array();
            $fieldsArr = explode(',', $_GET['fields']);
            if(!isset($data['0'])) {
                $allowFieldsArr = array_keys($data);
                foreach ($fieldsArr as $v) {
                    if(empty($v) || !in_array($v, $allowFieldsArr)) {
                        continue;
                    }
                    $newData[$v] = $data[$v];
                }
            } else {
                $allowFieldsArr = array_keys(array_shift(each($data)));
                foreach ($fieldsArr as $v) {
                    if(empty($v) || !in_array($v, $allowFieldsArr)) {
                        continue;
                    }
                    foreach ($data as $kk => $vv) {
                        $newData[$kk][$v] = $vv[$v];
                    }
                }
            }
            $data = $newData;
        }
        
        // 依据不同格式选择性输出
        switch($format) {
            case 'xml':
                header("Content-Type: text/xml");
                $outArr = array();
                if (!is_array($msg)) {
                    $outArr['result']['status']['code'] = $code;
                    $outArr['result']['status']['msg'] = $msg;
                    if (is_array($otherData)) {
                        foreach ($otherData as $k=>$v) {
                            if (!in_array($k, array('status', 'data'))) {
                                $outArr['result'][$k] = $v;
                            }
                        }
                    }
                    $outArr['result']['data'] = $data;
                } else {
                    $outArr = $msg;
                }
                $xml = new BaseModelXML();
                echo $xml->encode($outArr);
            break;
            case 'json':
                $outArr = array();
                if (!is_array($msg)) {
                    $outArr['result']['status']['code'] = $code;
                    $outArr['result']['status']['msg'] = $msg;
                    if (is_array($otherData)) {
                        foreach ($otherData as $k=>$v) {
                            if (!in_array($k, array('status', 'data'))) {
                                $outArr['result'][$k] = $v;
                            }
                        }
                    }
                    $outArr['result']['data'] = $data;
                } else {
                    $outArr = $msg;
                }
                $json = json_encode($outArr);
                $callback = isset($_GET['callback']) ? $_GET['callback'] : '';
                if (preg_match("/^[a-zA-Z][a-zA-Z0-9_\.]+$/", $callback)) {
                    header('Content-Type: application/javascript');
                    echo $callback . "(" . $json . ");";
                } elseif ($callback) {
                    header('Content-Type: text/html');
                    echo 'callback参数包含非法字符！';
                } else {
                    header('Content-Type: application/json');
                    echo $json;
                }
            break;
            default:
                if (defined('QUEUE') || defined('EXTERN')) {
                    BaseModelCommon::queueOut($code, $msg);
                    return;
                }
                $tpl = new BaseView();
                $tpl->assign('msg', $msg);
                $tpl->assign('url', $url);
                $tpl->assign('t', $t);
                if ($code == 0) {
                    $tpl->display('message/message.html');
                } else {
                    $tpl->display('message/error.html');
                }
            break;
        }
        
        // 调试信息
        BaseModelCommon::debug(BaseModelCommon::getRunTime(), '页面执行时间');
        BaseModelCommon::sendOnlineDebug();
        ob_end_flush();
        exit;
    }
}
