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
 * @description     添加createUrl；添加默认state和默认action；参数配置正则；
*/

class BaseModelRouter{
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
        Configure::getDefaultApp();
        if (ROUTER == 0 || empty(RouterConfig::$config[Configure::$app])) {
            if (isset($_GET[APP])) {
                Configure::$app = $_GET[APP];
            }
        } else {
            $uri = str_replace('/index.php', '', $_SERVER['REQUEST_URI']);
            //对于中文，已经变为urlencode，参数化的时候需要先decode出来
            $uri = urldecode($uri);
            BaseModelCommon::debug($uri, 'URI');
            //从uri中过滤掉key value查询串
            $uri = array_shift(explode('?', $uri));
            $uriArr = explode('/', trim($uri, '/'));
            $uriArrWithoutApp = array();
            //判断是否选择了app
            foreach ($uriArr as $uripart) {
                if (strpos($uripart, APP_PREFIX) === 0) {
                    Configure::$app = substr($uripart, 4);
                } else {
                    $uriArrWithoutApp[] = $uripart;
                }
            }
            $uri = '/'.implode('/', $uriArrWithoutApp).'/';
            //从URI中去除baseurl中的多级目录
            $baseUrlArr = explode('/', RouterConfig::$baseUrl[Configure::$app], 2);
            if(!empty($baseUrlArr[1])){
                $baseUrl = '/'.trim($baseUrlArr[1], '/').'/';
                $uri = str_replace($baseUrl, '/', $uri);
            }
            Common::debug($uri, 'uriNoApp');
            //将uri变为参数数组
            $paramsArr = explode('/', trim($uri, '/'));
            //从paramsArr中去除api、iframe、interface
            if (in_array($paramsArr[0], array('api', 'iframe', 'interface'))) {
                self::$interface = array_shift($paramsArr);
                if ($_REQUEST['format'] == '') {
                    $_REQUEST['format'] = 'json';
                }
            }
            $defaultState = array_pop(array_keys(RouterConfig::$config[Configure::$app]));
            if (empty($paramsArr[0])) {
                /**
                 * 没有任何请求参数使用默认state的默认action
                 */
                if (!isset($_GET[STATE])) {
                    $_GET[STATE] = $defaultState;
                }
                if (!isset($_GET[ACTION])) {
                    $defaultAction = array_pop(array_keys(RouterConfig::$config[Configure::$app][$_GET[STATE]]));
                    $_GET[ACTION] = $defaultAction;
                }
            } else {
                /**
                * 从URI的第一个参数开始，搜索RouterConfig中的配置项。
                * 如果不能匹配则使用默认配置，对于state和action来说，默认配置为最后一项。
                * 不能匹配的URI参数尝试匹配下一个RouterConfig配置项
                */
                $configArr = RouterConfig::$config[Configure::$app];
                if (isset($configArr[$paramsArr[0]])) {
                    $_GET[STATE] = $paramsArr[0];
                    array_shift($paramsArr);
                } else {
                    $_GET[STATE] = $defaultState;
                }
                $configArr = $configArr[$_GET[STATE]];
                $defaultAction = array_pop(array_keys(RouterConfig::$config[Configure::$app][$_GET[STATE]]));

                if (isset($configArr[$paramsArr[0]])) {
                    $_GET[ACTION] = $paramsArr[0];
                    array_shift($paramsArr);
                } else {
                    $_GET[ACTION] = $defaultAction;
                }
                $configArr = explode('/', $configArr[$_GET[ACTION]]);

                while (!empty($paramsArr)) {
                    if (self::match($configArr[0], $paramsArr[0])) {
                        array_shift($paramsArr);
                    }
                    array_shift($configArr);
                    if (empty($configArr)) {
                        if (!empty($paramsArr)) {
                            Configure::init();
                            BaseModelMessage::showError("不能匹配的URI：[app]:{$paramsArr[0]} [state]:{$paramsArr[1]} [action]:{$paramsArr[2]} 请配置路由规则");
                        }
                        BaseModelCommon::debug($paramsArr, 'not_match_parameters');
                        break;
                    }
                }
            }
            BaseModelCommon::debug($_GET, 'get_parameters');
        }
        Configure::init();
    }

    private static function createNoRouterUrl($baseUrl, $state, $action, $params=array(), $project='') {
        $paramsArr = array();
        if (!empty($project)) {
            $paramsArr[APP] = $project;
        }
        $paramsArr[STATE]   = $state;
        $paramsArr[ACTION]  = $action;
        return $baseUrl.'?'.http_build_query(array_merge($paramsArr, (array)$params));
    }

    public static function createUrl($state, $action, $params=array(), $project='', $baseUrl='') {
        $projectSpecified = $hideState = $hideAction = false;
	if (empty($project)) {
	    if (!isset($params[APP])) {
		$app = Configure::$app;
	    } else {
		$projectSpecified = true;
		$app = $params[APP];
		unset($params[APP]);
	    }
	} else {
	    $projectSpecified = true;
	    $app = APP_PREFIX.$project;
	}
	if (empty($baseUrl)) {
	    if (!isset($params['baseUrl'])) {
		if (!empty(RouterConfig::$baseUrl[$app])) {
		    $baseUrl = RouterConfig::$baseUrl[$app];
		} else {
		    $baseUrl = $_SERVER['HTTP_HOST'];
		}
	    } else {
		$baseUrl = $params['baseUrl'];
		unset($params['baseUrl']);
	    }
	}
	if (strpos($baseUrl, 'http://') !== 0) {
	    $baseUrl = rtrim("http://{$baseUrl}", '/').'/';
	}
        if (self::$interface !== false) { 
            $baseUrl .= self::$interface.'/';
        }
	
        if (ROUTER == 0) {
            return self::createNoRouterUrl($baseUrl, $state, $action, $params, $app);
        } else {
            if (empty($state)) {
                /*将三元式变为if else，三元式效率较低
                $state = isset($params[STATE]) ? $params[STATE] : $defaultState;*/
                if (!isset($params[STATE])) {
                    $hideState = true;
                    $states = RouterConfig::$config[$app];
                    if (empty($states)) {
                        BaseModelMessage::showError('未指定state');
                    }
                    $state = array_pop(array_keys($states));
                } else {
                    $state = $params[STATE];
                    unset($params[STATE]);
                }
            }
            if (empty($action)) {
                /*$action = isset($params[ACTION]) ? $params[ACTION] : $defaultAction;*/
                if (!isset($params[ACTION])) {
                    $hideAction = true;
                    $actions = RouterConfig::$config[$app][$state];
                    if (empty($actions)) {
                        BaseModelMessage::showError('未指定action');
                    }
                    $action = array_pop(array_keys($actions)); 
                } else {
                    $action = $params[ACTION];
                    unset($params[ACTION]);
                }
            }
            $url = '';
            if (isset(RouterConfig::$config[$app][$state][$action])) {
                if ($projectSpecified === true) {
                    $url .= '/'.urlencode($app);
                }
                if ($hideState === false) {
                    $url .= '/'.urlencode($state);
                }
                if ($hideAction === false) {
                    $url .= '/'.urlencode($action);
                }
                $configArr = explode('/', RouterConfig::$config[$app][$state][$action]);
                if (!empty($configArr[0])) {
                    $confParamArr = array(); 
                    foreach ($configArr as $config) {
                        list($paramKey, $paramVal) = explode('?', rtrim(ltrim($config, '<'), '>'));
                        if (isset($params[$paramKey])) {
                            if(strpos($paramVal, ':')) {
                                list($regx, $prefix) = explode(':', $paramVal);
                            }
                            $confParamArr[] = urlencode($prefix.$params[$paramKey]);
                            unset($params[$paramKey]);
                        }
                    }
                    if (!empty($confParamArr)) {
                        $url .= '/'.implode('/', $confParamArr);
                    }
                }
                //'?'开始的key value查询串前以'/'结尾
                $url .= '/';
                if (!empty($params)) {
                    $url .= '?'.http_build_query($params);
                }
                return $baseUrl.$url;
            } else {
                return self::createNoRouterUrl($baseUrl, $state, $action, $params, $app);
            }
        }
    }

    public static function delUrlParams($state, $action, $params, $project){
        $delParams = $_GET;
        if (is_array($params)) {
            foreach ($params as $param) {
                unset($delParams[$param]);
            }
        } else {
            BaseModelMessage::showError(__FUNCTION__.'错误的参数');
        }
        /*
         * 在渲染页面时，$_GET[STATE]和$_GET[ACTION]将使用本次请求计算出的STATE和ACTION
         * 此时如果$state/$action不为空，则需要将$delParams[STATE]/$delParams[ACTION]去掉
         * 因为在createUrl时，将优先使用addParams中的$delParams[STATE]/$delParams[ACTION]
         * 作为STATE/ACTION
         */
        if (!empty($project)) {
            unset($delParams[APP]);
        }
        if (!empty($state)) {
            unset($delParams[STATE]);
        }
        if (!empty($action)) {
            unset($delParams[ACTION]);
        }
        return self::createUrl($state, $action, $delParams, $project);
    }

    public static function addUrlParams($state, $action, $params, $project) {
        $addParams = $_GET;
        if (is_array($params)) {
            foreach ($params as $k=>$v) {
                $addParams[$k] = $v;
            }
        } else {
            BaseModelMessage::showError(__FUNCTION__.'错误的参数');
        }
        /*
         * 在渲染页面时，$_GET[STATE]和$_GET[ACTION]将使用本次请求计算出的STATE和ACTION
         * 此时如果$state/$action不为空，则需要将$addParams[STATE]/$addParams[ACTION]去掉
         * 因为在createUrl时，将优先使用addParams中的$addParams[STATE]/$addParams[ACTION]
         * 作为STATE/ACTION
         */
        if (!empty($project)) {
            unset($addParams[APP]);
        }
        if(!empty($state)){
            unset($addParams[STATE]);
        }
        if(!empty($action)){
            unset($addParams[ACTION]);
        }
        return self::createUrl($state, $action, $addParams, $project);
    }
}
?>
