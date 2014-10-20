<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 文件操作基类
 * @author          chenshuoshi <shuoshi@staff.sina.com.cn>
 * @time            2011/11/24 14:00
 * @version         Id: 1.0
 */

class BaseModelSimpleStorage {
    /**
     * 项目名
     * @var string 
     */
    private static $project = S3_PROJECT_NAME;

    /**
     * s3 分配的access key
     * @var string
     */
    private static $accesskey = S3_ACCESS_KEY;

    /**
     * s3 分配的secret key
     * @var string
     */
    private static $secretkey = S3_SECRET_KEY;

    /**
     * s3 资源描述符
     * @var resource
     */
    private static $s3Handle;

    /**
     * 构造函数，初始化资源描述符
     */
    public function __construct(){
        if(!(self::$s3Handle instanceof SinaStorageService)){
            self::$s3Handle = SinaStorageService::getInstance(self::$project, self::$accesskey, self::$secretkey, true);
            self::$s3Handle->setAuth(true);
        }
    }

    /**
     * 上传文件
     * @param string $key       路径
     * @param mixed $content    文件内容
     * @param int $length       文件大小
     * @param string $mimeType  文件类型
     */
    public function plainWrite ($key, $content, $length, $mimeType) {
        $key = ltrim($key, '/');
        for($i=0; $i<3; $i++){
            try {
                $ret = self::$s3Handle->uploadFile($key, $content, $length, $mimeType, $result);
                if (SINA_EDGE_KEY !== false) {
                    $edge = new BaseModelSinaEdge();
                    $ret = $edge->purge(S3_BASE_URL.'/'.$key);
                }
                BaseModelCommon::debug($result, "upload {$key} to s3");
                if (!$ret) {
                    //BaseModelLog::sendLog(BaseModelErrorCode::S3_UPLOAD_FAIL, array('key'=>$key, 'ret'=>$ret, 'result'=>$result));
                    BaseModelMessage::errLite(array('code'=>$ret, 'msg'=>'上传失败', 'data'=>$result));
                    return false;
                }
                return true;
            } catch (Exception $e) {
                if ($i>=2) {
                    //BaseModelLog::sendLog(BaseModelErrorCode::S3_UPLOAD_ERROR, array('key'=>$key, 'msg'=>$e->getMessage()));
                    BaseModelMessage::errLite(array('code'=>$ret, 'msg'=>'上传失败', 'data'=>$result));
                    return false;
                }
            }
        }
    }

    /**
     * 上传文件
     * @param string $key 路径
     * @param string $filename 文件名
     * @param string $mimeType 文件mime type
     */
    public function write($key, $filename, $mimeType=''){
        if (!is_file($filename)) {
            BaseModelMessage::showError('请上传文件');
        }
        $key = ltrim($key, '/');
        if($mimeType === ''){
            if (function_exists('mime_content_type')) {
                $mimeType = mime_content_type($filename);
            } else {
                BaseModelMessage::showError('请设置文件的mime type');
            }
        }
        $this->filename = $filename;
        $content = file_get_contents($this->filename);
        $length = filesize($this->filename);
        return $this->plainWrite($key, $content, $length, $mimeType);
    }

    /**
     * 根据key(路径)获得s3返回的获取地址
     * @param string $key 路径
     */
    public function getFileUrl($key, $auth=false){
        $key = ltrim($key, '/');
        self::$s3Handle->setAuth($auth);
        for($i=0; $i<3; $i++){
            try{
                if(self::$s3Handle->getFileUrl($key, $result)){
                    BaseModelCommon::debug($result, "get [{$key}] from S3");
                    return $result;
                }
                return false;
            }catch(Exception $e){
                if($i==2){
                    BaseModelLog::sendLog(BaseModelErrorCode::S3_DOWNLOAD_ERROR, array('key'=>$key, 'msg'=>$e->getMessage()));
                    return false;
                }
            }
        }
        self::$s3Handle->setAuth(true);
    }

    /**
     * 验证资源是否有效
     * @param string $key 路径
     * @param string $mimeType 要验证的文件mime type
     */
    public function isValid($key, $mimeType){
        $result = Http::head(S3_BASE_URL.'/'.$key);
        if($result['Content-Type'] == $mimeType){
            return true;
        }
        return false;
    }
} 

class SinaEdgeError extends Exception
{
}

class BaseModelSinaEdge
{
    private $_kid;
    private $_key;

    private $_headers;
    private $_args;
    private $_uri;
    private $_host;

    private $_error_msg;
    private $_timeout;

    public function __construct($kid = SINA_EDGE_KEY , $key = SINA_EDGE_SECRET_KEY) 
    {
        $this->_kid = $kid; 
        $this->_key = $key;

        $this->_headers = array();
        $this->_args = array();
        $this->_uri = "";

        $this->_error_msg = "";
        $this->_time_out = 5;

        $this->_host = SINA_EDGE_API_HOST;
    }

    public function setTimeout($t) {
        $this->_time_out = $t;
    }

    public function getErrorMessage() {
        $ret = $this->_error_msg;
        $this->_error_msg = "";
        return $ret;
    }

    public function add_headers($headers, $overide=True) {
        foreach($headers as $k=>$v) {
            if (in_array($k, $this->_headers) || !$overide) {
                continue;
            }
            $this->_headers[$k] = $v;
        } 

        if (in_array('Host', $this->_headers)) {
            $this->_host = $this->_headers['Host'];
        }
    }

    public function add_args($args, $overide=True) {
        foreach($args as $k=>$v) {
            if (in_array($k, $this->_args) || !$overide) {
                continue;
            }
            $this->_args[$k] = $v;
        } 

        if (in_array('kid', $this->_args)) {
            $this->_kid = $this->_args['kid'];
        }
    }

    private function _build_ssig() {
        $string_to_sign = $this->_method . "\n";
        $string_to_sign .= $this->_host . "\n";
        $string_to_sign .= $this->_uri . "\n";
        $args = array();

        $keys = array_keys($this->_args);
        sort($keys);

        foreach($keys as $k) {
            if ($k != 'ssig')
                $args []= "$k=".$this->_args[$k];
        }

        $string_to_sign .= implode('&', $args);
        #echo "stringtosign:\n".$string_to_sign."\n";

        $ssig = base64_encode(hash_hmac('sha1', $string_to_sign, $this->_key, True));
        $ssig = substr($ssig, 5, 10);

        return $ssig;
    }

    public function _submit($url, $args = array(), $headers = array()) {
        $new_args = array();

        $this->_method = 'POST';

        $elts = parse_url($url);

        if (!$elts) {
            throw new SinaEdgeError("bad url[$url]");
        }

        $this->_uri = $elts['path'];
        $this->_host = $elts['host'];

        if ($elts['query']) {
            foreach(explode('&', $elts['query']) as $arg) {
                $kv = explode('=', $arg);
                $key = $kv[0];
                $value = count($kv) > 1 ? $kv[1] : '';
                $new_args[$key] = $value;
            }
        }

        $this->add_headers($headers);
        $this->add_args($new_args);
        $this->add_args($args);
        $this->_args['kid'] = $this->_kid;

        if (!in_array('timestamp', $this->_args)) {
            $timestamp = (string)time(NULL);
            $this->_args['timestamp'] = $timestamp;
        }

        $args = $this->_args;
        $args['ssig'] = $this->_build_ssig();

        $context = array(
            'http'=>array(
                'method' => 'POST',
                'content' => http_build_query($args),
                'timeout' => $this->_time_out
    		));

        //echo "context: \n"; print_r($context);echo "\n";
        $context = stream_context_create($context);

        $result = file_get_contents($url, false, $context);
        if (!$result) {
            throw new SinaEdgeError("connection error");
        }

        $info = json_decode($result, True);
        if (!is_array($info)) {
            throw new SinaEdgeError("bad server response data: \"$result\"");
        }

        if ($info['code'] != '0') {
            throw new SinaEdgeError("request failed: \"".$info['message']."\"");
        }

        return $info;
    }

    public function purge($urls = false) {
        $this->_error_msg = "";

        try {
            if (!$urls) {
                throw new SinaEdgeError("url required");
            }

            if (!is_array($urls)) {
                $urls = array($urls);
            }

            $urls = json_encode($urls);
            $this->_submit($this->_host.'/object/purge', array('url' => $urls));
            return true;
        }
        catch (SinaEdgeError $e) {
            
            $this->_error_msg = "SinaEdge::purge() error, ".$e->getMessage();
            return false;
        }
    }


    public function prefetch($urls = false) {
        $this->_error_msg = "";

        try {
            if (!$urls) {
                throw new SinaEdgeError("url required");
            }

            if (!is_array($urls)) {
                $urls = array($urls);
            }

            $urls = json_encode($urls);
            $this->_submit($this->_host.'/object/prefetch', array('url' => $urls));

            return true;
        }
        catch (SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::prefetch() error, ".$e->getMessage();
            return false;
        }
    }

    public function create_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True,
                            'channel' => True,
                            'origin' => True,
                            'ignoreqs' => False,
                            'check_url' => True,
                            'cb' => False);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/create';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::create_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function delete_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True,
                            'cb' => False);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/delete';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::delete_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function modify_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True,
                            'param' => True,
                            'cb' => False);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/modify';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::modify_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function enable_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True,
                            'cb' => False);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/enable';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::enable_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function disable_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True,
                            'cb' => False);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/disable';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::disable_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function show_app($params) {
        $this->_error_msg = "";
        $acceptable = array('app_name' => True);
        try{
            foreach ($params as $k => $v) {
                if  (isset($acceptable[$k])) {
                    $acceptable[$k] = False;
                    continue;
                }
                throw new SinaEdgeError("invalid parameter '$k'");
            }

            foreach ($acceptable as $k => $v) {
                if ($v) {
                    throw new SinaEdgeError("missing parameter '$k'");
                }
            }

            $url = $this->_host. '/app/show';
            return $this->_submit($url, $params);
        }
        catch(SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::show_app() error, ".$e->getMessage();
            return False;
        }
    }

    public function health_check($app_channel = false) {
        $this->_error_msg = "";
        try {
            if (!$app_channel) {
                throw new SinaEdgeError("channel required");
            }

            if (!is_string($app_channel)) {
                throw new SinaEdgeError("channel should be a string"); 
            }

            $app_channel = json_encode($app_channel);
            $this->_submit($this->_host.'/channel/check', array('channel' => $app_channel));

            return true;
        }
        catch (SinaEdgeError $e) {
            $this->_error_msg = "SinaEdge::health_check() error, ".$e->getMessage();
            return false;
        }
    }
}

