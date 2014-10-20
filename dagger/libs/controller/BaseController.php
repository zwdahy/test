<?php
abstract class BaseController {

    /**
     * 
     * 模板变量 
     */
    private $view = array();

    /**
     *
     * 控制器
     */
    protected static $state;

    /**
     *
     * 控制器方法
     */
    protected static $action;

    public function __construct($state, $action) {
        self::$state = $state;
        self::$action = $action;
    }

    /**
     *
     * 控制器执行
     */
    public function runCommand() {
        //请求方法和来源
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                break;
            case 'POST':
                if (BaseModelSwitch::check('postRefererCheck') === true) {
                    $forbid = true;
                    foreach ($_SERVER['SERVER_ACCEPT_REFERER'] as $referer) {
                        if (strpos($_SERVER['HTTP_REFERER'], $referer) !== false) {
                            $forbid = false;
                            break;
                        }
                    }
                    if ($forbid) {
                        BaseModelMessage::showError('请求源不允许');
                    }
                }
                break;
            default:
                BaseModelMessage::showError('请求方法不允许');
        }
        $action = BaseModelCommon::getFormatName(self::$action);
        if(in_array($action, array('runCommand', 'setView', 'display', 'fetch'))){
            $controllerName = BaseModelCommon::getFormatName(self::$state, 'class');
            BaseModelCommon::debug($controllerName .'Controller类中方法'.$action.'为基类方法不能使用，您现在指向的app-controller为：app/'.Configure::$app . '/controller/', 'error');
            BaseModelMessage::showError("action : {$action}不存在", 'index.php');
        }
        if( method_exists($this, $action) ) {
            call_user_func_array(array(&$this, $action),array());
        } else {
            $controllerName = BaseModelCommon::getFormatName(self::$state, 'class');
            BaseModelCommon::debug($controllerName .'Controller类中不存在你调用的方法'.$action.'，您现在指向的app-controller为：app/'.Configure::$app . '/controller/', 'error');
            $action = htmlspecialchars($action);//防止XSS
            BaseModelMessage::showError("action : {$action}不存在", 'index.php');
        }
    }

    /**
     *
     * 设置模版变量
     * @param string $key  模板变量名
     * @param mixed $value 模板变量值
     */
    protected function setView($key, $value) {
        $this->view[$key] = $value;
    }

    /**
     *
     * 显示模版
     * @param string $tplFile
     * @return 
     */
    protected function display($tplFile) {
        if (in_array($_REQUEST['format'], array('json', 'xml'))) {
            BaseModelMessage::showSucc('获取数据', $this->view); 
        }
        echo $this->fetch($tplFile);
    }

    /**
     *
     * 返回解析内容
     * @param string $tplFile
     * @return html
     */
    protected function fetch($tplFile) {
        $tpl = new BaseView();
        $tpl->assign($this->view);
        return $tpl->fetch($tplFile);
    }

    /**
     * 
     * 重定向后改变url
     * @param string $url 指定的url
     */
    protected function redirectTo($url) {
        header('Location: '.$url);
        exit();
    }

    /**
     *
     * 重定向后不改变url
     * @param string $state 指定的state
     * @param string $action 指定的action
     * @param array $params 参数数组
     */
    protected function forward($state, $action, $params) {
        self::$state = $state;
        self::$action = $action;
        $_GET = $params;
        $this->runCommand();
    }



}
?>
