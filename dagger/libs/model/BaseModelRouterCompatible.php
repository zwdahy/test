<?php
/**
 * @Copyright (c) 2011-, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * Router基类 可兼容原框架apache路由重写
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
 *
 * @modified by     chen shuoshi <shuoshi@staff.sina.com.cn>
 * @time            2011/11/5 11:10
 * @version         Id: 1.0
 * @description     支持线上已有路由
 *                  ROUTER = 0 路由关闭
 *                  ROUTER = 1 线上路由
 *                  ROUTER = 2 新路由
*/

class BaseModelRouterCompatible {
    private static $interface = false;
    private static function match($rule, $param){
        list($paramKey, $paramVal) = explode('?', rtrim(ltrim($rule, '<'), '>'));
        $prefix = '';
        if(strpos($paramVal, ':') !== false){
            list($regx, $prefix) = explode(':', $paramVal);
        }else{
            $regx = $paramVal;
        }
        preg_match("/{$regx}/", $param, $matches);
        if(!empty($matches[0])){
            $param = substr($param, strlen($prefix));
            $_GET[$paramKey] = $param;
            return true;
        }
        return false;
    }
    
    /*
     * 静态化路由函数，重写请在继承类中修改
     * @return void
     */
    static public function route() {
        $uri = $_SERVER['REQUEST_URI'];
        if(ROUTER == 0 || empty(RouterConfig::$config[Configure::$app])){
          /**
           * 未开启路由，什么都不做
           */
        }else{
            preg_match('/^\/(.*?).php/', $uri, $match);
            if(isset($_GET['q']) || !empty($match[0])){
                BaseModelCommon::debug($_SERVER['QUERY_STRING'], 'real_query');
                $paramsArr = explode('/', trim($_GET[QUERY], '/'));
                if (in_array($_GET[STATE], array('api','interface','iframe')) && $paramsArr[0]) {
                    BaseModelCommon::debug('STATE为api、interface、iframe自动忽略', 'url_rewrite');
                    if (in_array($_GET[STATE], array('api','interface')) && $_REQUEST['format'] == '') {
                        $_REQUEST['format'] = 'json';
                        BaseModelCommon::debug('format:json', 'get-param-set');
                    }
                    $_GET[STATE] = $_GET[ACTION];
                    $action = array_shift($paramsArr);
                    $_GET[ACTION] = $action;
                    BaseModelCommon::debug(STATE . ':' . $_GET[ACTION], 'get-param-set');
                    BaseModelCommon::debug(ACTION . ':' . $action, 'get-param-set');
                }
                $i = 0;
                if (is_array(RouterConfig::$config[Configure::$app][$state][$action])) {
                    foreach (RouterConfig::$config[Configure::$app][$state][$action] as $v) {
                        if (!empty($paramsArr[$i])) {
                            $_GET[$v] = $paramsArr[$i];
                            BaseModelCommon::debug($v . ':' . $paramsArr[$i], 'get-param-set');
                        }
                        $i++;
                    }
                }
            }else{
                //对于中文，已经变为urlencode，参数化的时候需要先decode出来
                $uri = urldecode($uri);
                BaseModelCommon::debug($uri, 'URI');
                //从uri中过滤掉key value查询串
                $uri = array_shift(explode('?', $uri));
                //从URI中去除baseurl中的多级目录
                $baseUrlArr = explode('/', RouterConfig::$baseUrl[Configure::$app], 2);
                if(!empty($baseUrlArr[1])){
                    $baseUrl = '/'.trim($baseUrlArr[1], '/').'/';
                    $uri = str_replace($baseUrl, '/', $uri);
                }
                //将uri变为参数数组
                $paramsArr = explode('/', trim($uri, '/'));
                //从paramsArr中去除api、iframe、interface
                if(in_array($paramsArr[0], array('api', 'iframe', 'interface'))){
                    self::$interface = array_shift($paramsArr);
                    if ($_REQUEST['format'] == '') {
                        $_REQUEST['format'] = 'json';
                    }
                }
                $defaultState = array_pop(array_keys(RouterConfig::$config[Configure::$app]));
                if(empty($paramsArr[0])){
                    /**
                     * 没有任何请求参数使用默认state的默认action
                     */
                    if(!isset($_GET[STATE])){
                        $_GET[STATE] = $defaultState;
                    } 
                    if(!isset($_GET[ACTION])){
                        $defaultAction = array_pop(array_keys(RouterConfig::$config[Configure::$app][$_GET[STATE]]));
                        $_GET[ACTION] = $defaultAction;
                    }
                }else{
                    /**
                    * 从URI的第一个参数开始，搜索RouterConfig中的配置项。
                    * 如果不能匹配则使用默认配置，对于state和action来说，默认配置为最后一项。
                    * 不能匹配的URI参数尝试匹配下一个RouterConfig配置项
                    */
                    $configArr = RouterConfig::$config[Configure::$app];
                    if(isset($configArr[$paramsArr[0]])){
                        $_GET[STATE] = $paramsArr[0];
                        array_shift($paramsArr);
                    }else{
                        $_GET[STATE] = $defaultState;
                    }
                    $configArr = $configArr[$_GET[STATE]];
                    $defaultAction = array_pop(array_keys(RouterConfig::$config[Configure::$app][$_GET[STATE]]));

                    if(isset($configArr[$paramsArr[0]])){
                        $_GET[ACTION] = $paramsArr[0];
                        array_shift($paramsArr);
                    }else{
                        $_GET[ACTION] = $defaultAction;
                    }
                    $configArr = explode('/', $configArr[$_GET[ACTION]]);

                    while(!empty($paramsArr)){
                        if(self::match($configArr[0], $paramsArr[0])){
                            array_shift($paramsArr);
                        }
                        array_shift($configArr);
                        if(empty($configArr)){
                            if(!empty($paramsArr)){
                                BaseModelMessage::showError("不能匹配的URI：[state]:{$_GET[STATE]} [action]:{$_GET[ACTION]}");
                            }
                            BaseModelCommon::debug($paramsArr, 'not_match_parameters');
                            break;
                        }
                    }
                }
                BaseModelCommon::debug($_GET, 'get_parameters');
            }
        }
    }

    private static function createNoRouterUrl($state, $action, $params=array()){
        $baseUrl = sprintf($baseUrl, $_SERVER['HTTP_HOST']);
        $url = '';
        $paramsArr = array(STATE=>STATE."={$state}", ACTION=>ACTION."={$action}");
        if(!empty($params)){
            foreach($params as $k=>$v){
                $paramsArr[$k] = "{$k}={$v}";
            }
            $url .= '?'.implode('&', $paramsArr);
        }
        return $baseUrl.$url;
    }

    public static function createUrl($state, $action, $params=array()) {
        $baseUrl = 'http://%s';
        if(self::$interface !== false){ 
            $baseUrl .= self::$interface;
        }
        if(ROUTER == 0){
            return self::createNoRouterUrl($state, $action, $params);
        }else{
            $host = rtrim(ltrim(RouterConfig::$baseUrl[Configure::$app], '/'),'/');
            if($host === ''){
                $host = $_SERVER['HTTP_HOST'];
            }
            $baseUrl = sprintf($baseUrl, $host);
            $states = RouterConfig::$config[Configure::$app];
            if(empty($states)){
                return self::createNoRouterUrl($state, $action, $params);
            }
            $defaultState = array_pop(array_keys($states));
            if(empty($state)){
                /*将三元式变为if else，三元式效率较低
                $state = isset($params[STATE]) ? $params[STATE] : $defaultState;*/
                if(isset($params[STATE])){
                    $state = $params[STATE];
                    unset($params[STATE]);
                }else{
                    if(ROUTER == 0){
                        BaseModelMessage::showError('createUrl未指定state');
                    }else{
                        $state = $defaultState;           
                    }
                }
            }

            $actions = RouterConfig::$config[Configure::$app][$state];
            if(empty($actions)){
                return self::createNoRouterUrl($state, $action, $params);
            }
            $defaultAction = array_pop(array_keys($actions));
            if(empty($action)){
                /*$action = isset($params[ACTION]) ? $params[ACTION] : $defaultAction;*/
                if(isset($params[ACTION])){
                    $action = $params[ACTION];
                    unset($params[ACTION]);
                }else{
                    if(ROUTER == 0){
                        BaseModelMessage::showError('createUrl未指定action');
                    }else{
                        $action = $defaultAction;           
                    }
                }
            }

            if(ROUTER == 1){
                //apache重写为index.php?s=$1&a=$2&q=$3
                if(isset($params['q'])){
                    unset($params['q']);
                }
                $url = "/{$state}/{$action}/";
                if(!empty($params)){
                    foreach($params as $k=>$v){
                        $paramsArr[] = "{$k}={$v}";
                    }
                    $url .= '?'.implode('&', $paramsArr);
                }
                return $baseUrl.$url;
            }else{
                //apache重写为index.php/uri
                $url = '';
                if(isset(RouterConfig::$config[Configure::$app][$state][$action])){
                    $url = '';
                    $url .= "/{$state}";
                    if($action != $defaultAction){
                        $url .= "/{$action}";
                    }
                    $configArr = explode('/', RouterConfig::$config[Configure::$app][$state][$action]);
                    if(!empty($configArr[0])){
                        $confParamArr = array(); 
                        foreach($configArr as $config){
                            list($paramKey, $paramVal) = explode('?', rtrim(ltrim($config, '<'), '>'));
                            if(isset($params[$paramKey])){
                                if(strpos($paramVal, ':')){
                                    list($regx, $prefix) = explode(':', $paramVal);
                                }
                                $confParamArr[] = $prefix.$params[$paramKey];
                                unset($params[$paramKey]);
                            }
                        }
                        if(!empty($confParamArr)){
                            $url .= '/'.implode('/', $confParamArr);
                        }
                    }
                    //'?'开始的key value查询串前以'/'结尾
                    $url .= '/';
                    $extraParamArr = array();
                    if(!empty($params)){
                        foreach($params as $k=>$v){
                            $extraParamArr[] = $k.'='.$v;
                        }
                        $url .= '?'.implode('&', $extraParamArr);
                    }
                    return $baseUrl.$url;
                }
                return self::createNoRouterUrl($state, $action, $params);
            }
        }
    }

    public static function delUrlParams($params){
        $delParams = $_GET;
        if(is_array($params)){
            foreach($params as $param){
                unset($delParams[$param]);
            }
        }else{
            BaseModelMessage::showError(__FUNCTION__.'错误的参数');
        }
        return self::createUrl($state='', $action='', $delParams);
    }

    public static function addUrlParams($params){
        $addParams = $_GET;
        if(is_array($params)){
            foreach($params as $k=>$v){
                $addParams[$k] = $v;
            }
        }else{
            BaseModelMessage::showError(__FUNCTION__.'错误的参数');
        }
        return self::createUrl($state='', $action='', $addParams);
    }
}
