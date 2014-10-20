<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * HTTP基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @editer          xuyan <xuyan4@staff.sina.com.cn>
 * @time            2012/7/10 08:50
 * @version         Id: 1.0
*/

class BaseModelHttp {

    const DAGGER_HTTP_USERAGENT = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 Dagger/1.0';
    const DAGGER_HTTP_TIMEOUT = 3;
    const DAGGER_HTTP_MAXREDIRECT = 2;

    private function __construct(){
        return;
    }
    
    static public function post($req, $post, array $header = array(), $timeout = self::DAGGER_HTTP_TIMEOUT, $maxredirect = self::DAGGER_HTTP_MAXREDIRECT){
        $args['req'] = $req;
        $args['post'] = $post;
        $args['header'] = $header;
        $args['timeout'] = $timeout;
        $args['maxredirect'] = $maxredirect;
        return self::_http_exec($args);
    }

    static public function get($req, array $header = array(), $timeout = self::DAGGER_HTTP_TIMEOUT, $maxredirect = self::DAGGER_HTTP_MAXREDIRECT) {
        $args['req'] = $req;
        $args['header'] = $header;
        $args['timeout'] = $timeout;
        $args['maxredirect'] = $maxredirect;
        return self::_http_exec($args);
    }

    //
    static public function head($req, $timeout = self::DAGGER_HTTP_TIMEOUT, $maxredirect = self::DAGGER_HTTP_MAXREDIRECT) {
        $args['req'] = $req;
        $args['timeout'] = $timeout;
        $args['maxredirect'] = $maxredirect;
        $args['headOnly'] = true;
        return self::_http_exec($args);
    }


    static public function header($req, $post = array(), array $header = array(), $timeout = self::DAGGER_HTTP_TIMEOUT, $maxredirect = self::DAGGER_HTTP_MAXREDIRECT) {
        $args['req'] = $req;
        $args['post'] = $post;
        $args['header'] = $header;
        $args['timeout'] = $timeout;
        $args['maxredirect'] = $maxredirect;
        $args['headOnly'] = true;
        return self::_http_exec($args);
    }
    /**
     *   * 发送请求不等待接收
     *   * by wangxin3
     *   * @param $req string 发送请求url
     *   * @return void
     */
    public static function sendRequest($req, $host='') {
        $url = self::_makeUri($req);
        $urlArr = parse_url($url);
        $fp = @fsockopen($urlArr['host'], 80, $errno, $errstr, 1);
        if ($fp) {
            stream_set_timeout($fp, 1);         
            $out = "GET {$urlArr['path']}?{$urlArr['query']} HTTP/1.1\r\n";
            if (!empty($host)) {
                $out .= "Host: {$host}\r\n";
            } else {
                $out .= "Host: {$urlArr['host']}\r\n";
            }
            $out .= "User-Agent: ". self::DAGGER_HTTP_USERAGENT ."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);
            fclose ($fp);
        }
    }
    
    
    /**
     * 发送请求获取结果
     * @param $args['req'] mix 发送请求url，必传参数 **
     * @param $args['post'] mix 发送请求post数据
     * @param $args['header'] array 发送请求自定义header头，$args['header'] = array('Host: www.dagger.com')
     * @param $args['timeout'] int 发送请求超时设定
     * @param $args['maxredirect'] int 发送请求最大跳转次数
     * @param $args['cookie'] string 发送请求cookie
     * @param $args['headOnly'] bool 发送请求是否只抓取header头
     * @return mix 失败返回false，成功返回抓取结果
     */
    private static function _http_exec($args) {

        if(!function_exists('curl_init')) {
            BaseModelMessage::showError('服务器没有安装curl扩展！');
        }

        // $args['req'] = isset($args['req']) ? $args['req'] : array();
        $args['post'] = isset($args['post']) ? $args['post'] : array();
        $args['header'] = isset($args['header']) ? $args['header'] : array();
        $args['timeout'] = isset($args['timeout']) ? intval($args['timeout']) : self::DAGGER_HTTP_TIMEOUT;
        $args['maxredirect'] = isset($args['maxredirect']) ? intval($args['maxredirect']) : null;
        $args['cookie'] = isset($args['cookie']) ? $args['cookie'] : '';
        $args['headOnly'] = isset($args['headOnly']) ? $args['headOnly'] : false;

        defined("DAGGER_DEBUG") && $startRunTime = microtime(true);
        $url = self::_makeUri($args['req']);
        if(empty($url)) {
            BaseModelMessage::showError('页面抓取请求url不能为空');
        }

        // mc处理
        $mc = new BaseModelMemcache();
        $mc_http_key_suffix = md5(strpos($url, '?') ? substr($url, 0, strpos($url, '?')) : $url);
        $mc_http_false_key = "http_false_" . $mc_http_key_suffix;// 存放最近连续失败累计时间、次数、最后一次正确结果。
        $mc_http_lock_key = "http_lock_" . $mc_http_key_suffix;
        $mc_lock = $mc->get($mc_http_lock_key);
        if($mc_lock !== false) {
            if (defined("DAGGER_DEBUG")) {
                $runTime = microtime(true) - $startRunTime;
                $runTime = sprintf("%0.2f", $runTime * 1000) . " ms";
                BaseModelCommon::debug(array(array('运行时间', '执行结果'), array($runTime, '接口在10秒内出现20次错误，锁定30秒返回false')), 'request_return');
            }
            return false;
        }
        
        $args['header'][] = 'Expect:'; // 解决100问题
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $args['header']);
        curl_setopt($ch, CURLOPT_USERAGENT, self::DAGGER_HTTP_USERAGENT);
        curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if(!empty($args['post'])) {
            if(is_array($args['post'])) {
                $args['post'] = http_build_query($args['post']);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $args['post']);
        }

        if($args['headOnly']) {
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        
        if(!empty($args['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $args['cookie']);
        }

        if (ini_get('open_basedir') === '' && ini_get('safe_mode') === 'Off') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $args['maxredirect'] > 0);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $args['maxredirect']);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_HEADER, true);
            curl_setopt($rch, CURLOPT_NOBODY, true);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            do {
                curl_setopt($rch, CURLOPT_URL, $newurl);
                $header = curl_exec($rch);
                if (curl_errno($rch)) {
                    break;
                } else {
                    $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                    if (in_array($code, array(301,302,303,307))) {
                        preg_match('/Location:(.*?)\n/', $header, $matches);
                        $newurl = trim(array_pop($matches));
                    } else {
                    	break;
                    }
                }
            } while ($code && --$args['maxredirect']);
            curl_close($rch); 
            curl_setopt($ch, CURLOPT_URL, $newurl);
        }

        $ret = curl_exec($ch);
        if ($ret === false) {
            $ret = curl_exec($ch);//失败后重连一次
        }
        curl_close($ch);

        // mc缓存处理
        if($ret === false) {
            // 10秒钟内连续失败20次，30秒钟锁定，直接返回false;
            if(!$mc->add($mc_http_false_key, 1, 10)) {
                $falseCount = $mc->increment($mc_http_false_key);
                if($falseCount > 19) {
                    $mc->add($mc_http_lock_key, 1, 30);
                }
            }
        }
        
        // 抓取header时，解析header头
        if($args['headOnly'] && $ret !== false ) {
            $_headers = str_replace("\r", '', $ret);
            $_headers = explode("\n",$_headers);
            $ret = array();
            foreach($_headers as $value) {
                $_header = explode(': ', $value);
                if(!empty($_header[0])) {
                    if(empty($_header[1])) {
                        $ret['status'] = $_header[0];
                    } else {
                        $ret[$_header[0]] = $_header[1];
                    }
                }
            }
        }
        
        if(defined("DAGGER_DEBUG")) {
            $runTime = microtime(true) - $startRunTime;
            $runTime = sprintf("%0.2f", $runTime * 1000) . " ms";
            BaseModelCommon::debug(array(array('运行时间', '执行结果'), array($runTime, $ret)), 'request_return');
        }
        return $ret;

    }

    private static function _makeUri($req) {
        $url = '';
        if(is_array($req)) {
            switch (count($req)) {
                case 1:
                    $url = $req[0];
                break;
                case 2:
                    list($url, $params) = $req;
                    $paramStr = http_build_query($params);
                    if(strpos($url, '?') !== false) {
                        $url .= "&{$paramStr}";
                    } else {
                        $url .= "?{$paramStr}";
                    }
                break;
                default:
                    BaseModelMessage::errLite(array('msg'=>'url参数错误'));
            }
        } else {
            $url = $req;
        }
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($url, 'request_url');
        return $url;
    }
}
