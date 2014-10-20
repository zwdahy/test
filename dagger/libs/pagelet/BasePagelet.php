<?php

interface pagelet {
    public function run($params);
}

class BasePagelet implements pagelet {

    /**
     * @var $view 向pagelet中注册的变量数组
     */
    public $view;

    /**
     * @var $stack pagelet栈
     */
    private static $stack = array();

    /**
     * pagelet逻辑执行函数
     */
    public function run($params){
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    protected function setView($key, $value) {
        $this->view[$key] = $value;
    }

    /**
     * @param string $tplFile
     */
    protected function display($tplFile) {
        echo $this->fetch($tplFile);
    }

    /**
     * @param string $tplFile
     */
    protected function fetch($tplFile) {
        $tpl = new BaseView();
        $tpl->assign($this->view);
        return trim($tpl->fetch($tplFile));
    }

    /**
     * @params array $params
     */
    public static function factory($params) {
        $pageletId = BaseModelCommon::getFormatName($params['id'].'_pagelet', 'class');
        //if(!DEBUG && !isset($_GET['nojs'])) {
        //暂时关闭bigpipe
        if(false) {
            self::$stack[$pageletId] = $params['params'];
            echo '<div id="pagelet_'.strtolower($pageletId).'"></div>';
        } else {
            self::render($pageletId, $params['params']);
        }
    }

    /**
     * @params string $pageletId
     * @params array $params
     */
    public static function render($pageletId, $params=array()) {
        $pagelet = new $pageletId;
        $pagelet->run($params);
    }

    /**
     * bigpipe 执行函数
     */
    public static function bigpipe() {
        //pop stack and call pagelet render
        foreach(self::$stack as $pageletId=>$params){
            ob_start();
            self::render($pageletId, $params);
            $content = ob_get_contents();
            ob_end_clean();
            echo $content;
        }
    }
}
