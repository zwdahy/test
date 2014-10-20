<?php
function dir_list($dir) {
    $dir_list = array();
    $d = scandir($dir);
    foreach ($d as $v) {
        if (strpos($v, ".") !== 0) {
            if (is_dir($dir .'/'. $v)) {
                $tmp = dir_list($dir .'/'. $v);
                $dir_list = array_merge($dir_list, $tmp);
            } else {
                $dir_list[] = $dir .'/'. $v;
            }
        }
    }
    return $dir_list;
}
$dir_list = dir_list('.');
foreach ($dir_list as $v) {
    if (strpos($v, '.php') !== FALSE || strpos($v, '.html') !== FALSE || strpos($v, '.js') !== FALSE || strpos($v, '.css') !== FALSE) {
        $c = file_get_contents($v);
        $c = str_ireplace("charset=utf-8", "charset=GBK", $c);//template
        $c = str_ireplace("ENT_QUOTES,'UTF-8'", "ENT_QUOTES,'GB2312'", $c);//BaseView.php
        $c = str_ireplace("'DB_CHARS', 'utf8'", "'DB_CHARS', 'GBK'", $c);//SysInitConfig.php
        $c = str_ireplace("public static function showSucc(\$msg, \$data=array(), \$otherData=array(), \$url='', \$t=3, \$ie='', \$oe='utf-8') {", "public static function showSucc(\$msg, \$data=array(), \$otherData=array(), \$url='', \$t=3, \$ie='gbk', \$oe='utf-8') {", $c);//BaseModelMessage.php
        $c = str_ireplace("public static function showError(\$msg, \$data=array(), \$code=11, \$url='', \$t=3, \$ie='', \$oe='utf-8') {", "public static function showError(\$msg, \$data=array(), \$code=11, \$url='', \$t=3, \$ie='gbk', \$oe='utf-8') {", $c);//BaseModelMessage.php
        $c = str_ireplace("private static function message(\$code, \$msg, \$data, \$url, \$t, \$otherData=array(), \$ie='', \$oe='utf-8') {", "private static function message(\$code, \$msg, \$data, \$url, \$t, \$otherData=array(), \$ie='gbk', \$oe='utf-8') {", $c);//BaseModelMessage.php
        $c = str_ireplace("//系统define", "header('Content-Type:text/html;charset=gbk');\nif(strtolower(\$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {\n    \$_GET = BaseModelCommon::convertEncoding(\$_GET, 'GBK', 'UTF-8');\n    \$_POST = BaseModelCommon::convertEncoding(\$_POST, 'GBK', 'UTF-8');\n    \$_REQUEST = BaseModelCommon::convertEncoding(\$_REQUEST, 'GBK', 'UTF-8');\n}\n\n//系统define", $c);//index.php
        $c = str_ireplace("static public function debug(\$value, \$type = 'DEBUG', \$verbose = false) {\n        if (defined(\"DAGGER_DEBUG\")) {", "static public function debug(\$value, \$type = 'DEBUG', \$verbose = false) {\n        if (defined(\"DAGGER_DEBUG\")) {\n            \$value = BaseModelCommon::convertEncoding(\$value, 'UTF-8', 'GBK');\n            \$type = BaseModelCommon::convertEncoding(\$type, 'UTF-8', 'GBK');", $c);//Common::debug()
        $c = iconv("UTF-8", "GBK", $c);
        $new_path = str_replace("./", "../gbk.dagger.sina.com.cn/", $v);
        $dir_name = dirname($new_path);
        if (!is_dir($dir_name)) {
            recursiveMkdir($dir_name);
        }
        file_put_contents($new_path, $c);
    }
}
echo "succ";
function recursiveMkdir($pathname, $mode=0700) {
	is_dir(dirname($pathname)) || recursiveMkdir(dirname($pathname), $mode);
    return is_dir($pathname) || mkdir($pathname, $mode);
}
?>
