<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 数据操作DB扩展类，负责连接数据库。
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
 */

class BaseModelDBConnect
{
    static private $links = array();//数据库连接

    private function __construct()
    {
        return;
    }

    /**
     * 连接数据库，返回连接上的PDO对象
     * @param int $DBName    数据库名称
     * @param string $master_or_slave   master;主库|slave:从库
     * @return master db handle;
     */
    static public function connectDB($DBName, $master_or_slave = 'slave', $DBConfig = array(), $reConnect = false)
    {
        $master_or_slave != 'master' && $master_or_slave = 'slave';
        if (BaseModelSwitch::check('masterDbPostOnly') === true && $master_or_slave == 'master' && !empty($_SERVER['HTTP_HOST']) && $_SERVER['REQUEST_METHOD'] != 'POST') {
            BaseModelMessage::showError('请求的方法不允许');
        }
        $DBType = ENV;
        !in_array($DBType, array('dev','test','product')) && $DBType = 'product';
        empty($DBName) && $DBName = DB_DEFAULT;
        if (@constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave . "_HOST")) == '')
        {
            $DBType = 'product';
        }

        $phptype  = "mysql";
        $username = empty($DBConfig[$master_or_slave]['user']) ? constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave . "_USER")) : $DBConfig[$master_or_slave]['user'];
        $password = empty($DBConfig[$master_or_slave]['pass']) ? constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave ."_PASS")) : $DBConfig[$master_or_slave]['pass'];
        $hostspec = empty($DBConfig[$master_or_slave]['host']) ? constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave ."_HOST")) : $DBConfig[$master_or_slave]['host'];
        $port = !is_numeric($DBConfig[$master_or_slave]['port']) ? constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave ."_PORT")) : $DBConfig[$master_or_slave]['port'];
        $database = empty($DBConfig[$master_or_slave]['database']) ? constant(strtoupper("DB_" . $DBName . "_" . $DBType . "_" . $master_or_slave ."_DATABASE")) : $DBConfig[$master_or_slave]['database'];
        $charset = empty($DBConfig['charset']) ? DB_CHARS : strtolower($DBConfig['charset']);
        $db_key = md5(implode('-', array($hostspec, $port, $username, $database, $charset)));
        if (self::$links[$db_key] && !$reConnect)
        {
            return self::$links[$db_key];
        }
        else
        {
            /*
                        self::$links[$db_key] = new PDO($dsn, $username, $password);
             */
            $dsn = "mysql:dbname=$database;port=$port;host=$hostspec";
            $connectType = $reConnect ? 'db_reconnect' : 'db_connect';
            BaseModelCommon::debug($dsn."|username:$username|pw:***", $connectType);
            self::$links[$db_key] = mysqli_connect( $hostspec , $username , $password , $database , $port );
            if (self::$links[$db_key]) {
                $charset = $DBConfig['charset'] ? strtolower($DBConfig['charset']) : DB_CHARS;
                BaseModelCommon::debug("set names '".$charset."'", "db_set_char");
                /*
                self::$links[$db_key]->exec("set names '".$charset."'");
                 */
                mysqli_query(self::$links[$db_key], "set names '".$charset."'");
                return self::$links[$db_key];
            }
            else
            {
                return self::$links[$db_key] = null;
            }
        }
    }

    /**
     * 关闭数据库连接
     * @return voild;
     */
    static public function close_db(&$dbh)
    {
        if($dbh)
        {
            @mysqli_close($dbh);
        }
    }
}
?>
