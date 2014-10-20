<?php
class DaggerAutoLoad{

    /**
     * 框架核心组件
     */
    private static $coreClass = array(
        'PEAR_Error'=>array('name'=>'PEAR'),
        'SSOClient'=>'',
        'SSOCookie'=>'',
        'PHPUnit_Framework_TestCase'=>'',
        'SinaStorageService'=>array('path'=>array(PATH_SINA_SERVICE, 'SinaStorageService/')),
        'FirePHP'=>array('path'=>array(PATH_LIBS_MODEL, 'FirePHPCore/'), 'postfix'=>'.class'),
        'PHPExcel'=>array('path'=>array(PATH_LIBS_MODEL, 'PHPExcel/')),
        'PHPMailer'=>array('path'=>array(PATH_LIBS_MODEL, 'PHPMailer/'), 'postfix'=>'.class'),
        'Smarty'=>array('path'=>array(PATH_LIBS_VIEW), 'postfix'=>'.class'),
        'Configure'=>PATH_CONFIG,
        'SysInitConfig'=>PATH_CONFIG,
        'DBConfig'=>PATH_CONFIG,
        'RouterConfig'=>PATH_CONFIG,
        'DictConfig'=>PATH_CONFIG,
        'RedisConfig'=>PATH_CONFIG,
        'SSOConfig'=>PATH_CONFIG,
        'EncryptConfig'=>PATH_CONFIG,
        'BaseModelCommon'=>PATH_LIBS_MODEL,
        'BaseModelCookie'=>PATH_LIBS_MODEL,
        'BaseModelCrypt'=>PATH_LIBS_MODEL,
        'BaseModelDB'=>PATH_LIBS_MODEL,
        'BaseModelDBConnect'=>PATH_LIBS_MODEL,
        'BaseModelDBException'=>array('path'=>array(PATH_LIBS_MODEL, 'Exceptions/')),
        'BaseModelEncrypt'=>PATH_LIBS_MODEL,
        'BaseModelErrorCode'=>PATH_LIBS_MODEL,
        'BaseModelExcel'=>PATH_LIBS_MODEL,
        'BaseModelException'=>array('path'=>array(PATH_LIBS_MODEL, 'Exceptions/')),
        'BaseModelFile'=>PATH_LIBS_MODEL,
        'BaseModelFilter'=>PATH_LIBS_MODEL,
        'BaseModelHttp'=>PATH_LIBS_MODEL,
        'BaseModelIp'=>PATH_LIBS_MODEL,
        'BaseModelImage'=>PATH_LIBS_MODEL,
        'BaseModelLog'=>PATH_LIBS_MODEL,
        'BaseModelMailer'=>PATH_LIBS_MODEL,
        'BaseModelMemcache'=>PATH_LIBS_MODEL,
        'BaseModelMessage'=>PATH_LIBS_MODEL,
        'BaseModelPage'=>PATH_LIBS_MODEL,
        'BaseModelRedis'=>PATH_LIBS_MODEL,
        'BaseModelRouter'=>PATH_LIBS_MODEL,
        'BaseModelRouterCompatible'=>PATH_LIBS_MODEL,
        'BaseModelSession'=>PATH_LIBS_MODEL,
        'BaseModelSimpleStorage'=>PATH_LIBS_MODEL,
        'BaseModelSwitch'=>PATH_LIBS_MODEL,
        'BaseModelUnitTest'=>PATH_LIBS_MODEL,
        'BaseModelValidate'=>PATH_LIBS_MODEL,
        'BaseModelWeibo'=>PATH_LIBS_MODEL,
        'BaseModelWeiboClient'=>PATH_LIBS_MODEL,
        'BaseModelWeiboLogin'=>PATH_LIBS_MODEL,
        'BaseModelXHProfRun'=>PATH_LIBS_MODEL,
        'BaseModelXML'=>PATH_LIBS_MODEL,
        'BaseModelXPath'=>PATH_LIBS_MODEL,
        'BaseView'=>PATH_LIBS_VIEW,
        'BaseController'=>PATH_LIBS_CTL,
        'BasePagelet'=>PATH_LIBS_PLT,
    );

    /**
     * 框架加载器，用以结合其它模块的autoloader。
     * 具体使用方法可以参考框架的Smarty.class.php
     * @param string $autoloader 其它autoloader函数名
     */
    public static function register($autoloader){
        $loaders = spl_autoload_functions();
        foreach($loaders as $loader){
            spl_autoload_unregister($loader);
        }
        spl_autoload_register($autoloader);
        foreach($loaders as $loader){
            spl_autoload_register($loader);
        }
    }

    /**
     * 文件引用器
     * @param string $prefixPath 文件所在文件夹绝对路径
     * @param string $name 文件名
     * @param strint $postfix 文件后缀
     */
    public static function includeFile($prefixPath,$name,$postfix='.php') {
        $_file = $prefixPath.$name.$postfix;
        if (is_file($_file)) {
            include_once($_file);
        } else if(!include_once($_file)) {
            BaseModelCommon::debug($name . '类include文件：' . $_file . '不存在，您现在指向的app为：app/'.Configure::$app.'/', 'error');
            $name = htmlspecialchars($name);
            BaseModelMessage::showError('class：'.$name.' not find');
        }
    }

    /**
     * 框架autoloader
     * @param string $name 要加载的类名
     */
    public static function loader($name) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]+$/', $name)) {
            exit();
        }
        if (isset(self::$coreClass[$name])) {
            if(!is_array(self::$coreClass[$name])){
                $path = self::$coreClass[$name];
            }else{
                if(isset(self::$coreClass[$name]['path'])){
                    $path = implode('', self::$coreClass[$name]['path']);
                }
                if(isset(self::$coreClass[$name]['name'])){
                    $name = self::$coreClass[$name]['name'];
                }
                if(isset(self::$coreClass[$name]['postfix'])){
                    $name .= self::$coreClass[$name]['postfix'];
                }
            }
            self::includeFile($path, $name);
        }/* else {
             * 对于外部使用Dagger，不要去找文件结尾为Controller、
             * DB或者其它任何没有被加载的文件
            if (preg_grep('/.*?Controller/', array($name))) {
                self::includeFile(PATH_APP_CTL, $name);
            } else if (preg_grep('/.*?DB/', array($name))) {
                self::includeFile(PATH_MODEL . 'db/', $name);
            } else if (preg_grep('/.*?Config/', array($name))) {
                self::includeFile(PATH_CONFIG, $name);
            } else if (preg_grep('/.*?Pagelet/', array($name))) {
                self::includeFile(PATH_APP_PLT, $name);
            } else {
                self::includeFile(PATH_MODEL, $name);
            }
        }*/
    }
}

spl_autoload_register(array('DaggerAutoLoad', 'loader'));

/**
 * 在firephp中捕获fatal异常，用以display_error
 * 不开的情况下调试网站
 */
function daggerFatalError() {
    $error = error_get_last();
    if (empty($error) || !(1 & $error['type'])) {
        // This error code is not included in error_reporting
        return false;
    }
    formatErrorInfo($error['type'], $error['message'], $error['file'], $error['line']);

    return true;
}

function formatErrorInfo($errno, $errstr, $errfile, $errline) {
    $errstr = strip_tags($errstr);
    $myerror = "$errstr in $errfile on line $errline";

    switch ($errno) {
        case 2:
            $myerror = "==Warning==" . $myerror;
            break;
        case 1:
            $myerror = "==Fatal error==" . $myerror;
            break;
        case 8:
            $myerror = "==Notice==" . $myerror;
            break;
        case E_USER_ERROR:
            $myerror = "==My error==" . $myerror;
            break;
        case E_USER_WARNING:
            $myerror = "==My warning==" . $myerror;
            break;
        case E_USER_NOTICE:
            $myerror = "==My notice==" . $myerror;
            break;
        default:
            $myerror = "==Unknown error type [$errno]==" . $myerror;
            break;
    }

    if (DEBUG) {
        if(defined("QUEUE")) {
            // echo $myerror . "\n";
        } else if(strpos($_SERVER['HTTP_USER_AGENT'], 'FirePHP') === false) {
            echo '<table cellspacing="0" cellpadding="1" border="1" dir="ltr" class="xdebug-error"><tbody><tr><th bgcolor="#f57900" align="left" colspan="5"><span style="background-color: #cc0000; color: #fce94f; font-size: x-large;">( ! )</span>'.$myerror.'</th></tr></tbody></table><br /><br />';
        }
    }
    BaseModelCommon::debug($myerror, 'error');

}

register_shutdown_function('daggerFatalError');

/**
 * 在firephp中捕获异常，用以display_error
 * 不开的情况下调试网站
 */
function daggerErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    formatErrorInfo($errno, $errstr, $errfile, $errline);

    /* Don't execute PHP internal error handler */
    return true;
}

set_error_handler("daggerErrorHandler");

