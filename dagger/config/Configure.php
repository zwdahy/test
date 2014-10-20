<?php
class Configure {

    static public $app;//当前选择APP

    private function __construct() {
        return;
    }

    //配置项目选择规则
    final static public function getDefaultApp() {
        if( strpos($_SERVER['HTTP_HOST'], 'admin') !== FALSE ) {
            self::$app = APP_ADMIN;
        } else {
            self::$app = APP_EXAMPLE;
        }
    }

    final static public function init() {
        Common::debug(self::$app, 'choose_app');
        //app的基础目录
        $base = PATH_APP . self::$app . '/';   
        //app的controller目录
        $controllerPath = $base . 'controller/';
        //app的pagelet目录
        $pageletPath = $base . 'pagelet/';
        //app的templates目录
        $tempaltePath = $base . 'templates/';

        if (PLATFORM == 'sae') {
            //app的templats_c目录，SAE使用MC
            $templateCPath = "saemc://smartytpl/" . self::$app . '/templates_c/';
        } else {
            //app的templats_c目录
            $templateCPath = PATH_CACHE . self::$app . '/templates_c/';
            BaseModelCommon::recursiveMkdir($templateCPath);
        }

        define('PATH_APP_CTL', $controllerPath);        //app的controller
        define('PATH_APP_PLT', $pageletPath);
        define('PATH_APP_TPL', $tempaltePath);          //app的templates
        define('PATH_APP_TPC', $templateCPath);         //app的templats_c
    }
}
?>
