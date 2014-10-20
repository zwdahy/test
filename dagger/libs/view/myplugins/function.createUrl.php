<?php
function smarty_function_createUrl($param, &$smarty)
{
    $project = $param['project'];
    $state = $param['state'];
    $action = $param['action'];
    $baseUrl = $param['baseUrl'];
    $params = $param['params'];
    $delParams = $param['delParams'];
    $addParams = $param['addParams'];
    $baseUrl = $param['baseUrl'];
    if(!empty($delParams)){
        return Router::delUrlParams($state, $action, $delParams, $project, $baseUrl);
    }
    if(!empty($addParams)){
        return Router::addUrlParams($state, $action, $addParams, $project, $baseUrl);
    }
    return Router::createUrl($state, $action, $params, $project, $baseUrl);
}
