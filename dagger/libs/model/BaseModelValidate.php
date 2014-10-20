<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 数据验证基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
*/
class BaseModelValidate {
    private function __construct() {
        return false;
    }

    /**
     *   * 数据验证
     *   * @params stinrg 
     *   * @return void
     */
    static public function check($value, $type, $validate, $length) {
        list($notEmpty, $validate) = explode("_", $validate);
        if ($value === '') {
            if ($notEmpty) {
                return '不能为空';
            } else {
                return true;//该值可以为空,不需要检测
            }
        }
		if ($validate == ''){
			return true;//不需要检测该值类型
		}
        //按字段类型判断
        switch ($type) {
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'bigint':
                if (!is_numeric($value)) {
                    return '不是一个数字';
                } elseif (strlen($value) > $length) {
                    return '数字超出了范围';
                }
                break;
            case 'bool':
                if (!is_array($value, array(true, false))) {
                    return '必须是一个bool型';
                }
                break;
            case 'float':
            case 'double':
                if (!is_numeric($value)) {
                    return '不是一个数字';
                }
                break;
            case 'char':
            case 'varchar':
            case 'text':
            case 'varchar':
                if (strlen($value) > $length) {
                    return '字符串超出了范围';
                }
                break;
            case 'datetime':
                if (!preg_match("/\d{1,4}-\d{1,2}-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}/", $value)) {
                    return '时间格式应为：2011-03-02 19:01:00';
                }
                break;
            case 'date':
                if (!preg_match("/\d{1,4}-\d{1,2}-\d{1,2}/", $value)) {
                    return '日期格式应为：2011-03-02';
                }
                break;
            case 'time':
                if (!preg_match("/\d{1,2}:\d{1,2}:\d{1,2}/", $value)) {
                    return '时间格式应为：19:01:00';
                }
                break;
            case 'year':
                if (!preg_match("/\d{1,4}/", $value)) {
                    return '年份格式应为：2011';
                }
                break;
        }
        /**
         * 按指定类型判断
         * forward_static_call(array('Validate', "is".ucfirst($validate)), $value);5.3之后支持
         * 这里forward_static_call是不会重置class information，如果call_user_func使用parent、
         * self、static等关键字可以起到同样的效果
         */
        if(class_exists('Validate')){
            return call_user_func('Validate::is'.ucfirst($validate), $value);
        }
        return call_user_func('self::is'.ucfirst($validate), $value);
    }
    //检测是否为数字
    static protected function isNumber($value) {
    	return is_numeric($value) ? true : '必须为一个数字';
    }
    
    //检查是否为移动电话
    static protected function isMobile($value) {
    	return preg_match("/1[3-8]\d{9}/",$value) ? true : '必须为一个有效的手机号';
    }
    
    //检查是否为有效年龄
    static protected function isAge($value) {
    	return (is_numeric($value) && $value >= 0 && $value < 250) ? true : '必须为0到250岁的有效年龄';
    }
    
    //检查是否为有效邮政编码
    static protected function isPostcode($value) {
    	return preg_match('/^[1-9]\d{5}$/', $value) ? true : '必须为有效邮政编码';
    }
    
    //检查是否为有效网址
    static protected function isUrl($value) {
		/* if (function_exists('filter_var')) {
			return filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) !== FALSE ? true : '必须为有效网址';
        } */
		$strict = false;
		$validChars = '([' . preg_quote('!"$&\'()*+,-.@_:;=') . '\/0-9a-z]|(%[0-9a-f]{2}))';
		$ip_pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
		$hostname_pattern = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
		$url_pattern = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)' . (!empty($strict) ? '' : '?') .
			'(?:' . $ip_pattern . '|' . $hostname_pattern . ')(?::[1-9][0-9]{0,3})?' .
			'(?:\/?|\/' . $validChars . '*)?' .
			'(?:\?' . $validChars . '*)?' .
			'(?:#' . $validChars . '*)?$/i';
		// return $url_return = preg_match($url_pattern, $value);
        if ($url_return = preg_match($url_pattern, $value) ) {
            return true;  
        }
        return '必须为有效网址';
    }
    
    //检查url是否为有效图片
    static protected function isImage($value) {
    	return (preg_match("/(jpg|gif|png)$/i", $value) && file_get_contents($value)) ? true : '必须为有效图片，格式：jpg、gif、png';
    }
    
    //检查url是否为有效资源
    static protected function isResource($value) {
    	return (preg_match("/^http::\/\/.*/i", $value) && file_get_contents($value)) ? true : '必须为有效资源';
    }
    
    //检查是否为有效日期
    static protected function isDatetime($value) {
    	return preg_match("/\d{1,4}-\d{1,2}-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}/",$value) ? true :  '时间格式应为：2011-03-02 19:01:00';
    }
    
    //检查是否为有效年份
    static protected function isYear($value) {
    	return (is_numeric($value) && $value >= 0 && $value < 9999) ? true : '必须为0至9999的有效年份';
    }
    
    //检查是否为有效月份
    static protected function isMonth($value) {
    	return (is_numeric($value) && $value > 0 && $value < 13) ? true : '必须为有效月份';
    }
    
    //检查是否为有效日期
    static protected function isDay($value) {
    	return (is_numeric($value) && $value > 0 && $value < 32) ? true : '必须为有效日期';
    }
    
    //检查是否为有效身份证,15位或者18位
    static protected function isIdcard($value) {
		$len = strlen($value);
		if ($len == 15){
			//"/\d{1,4}-\d{1,2}-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}/"
			// $Idcard_pattern = '/^([1-9]{0,1})?(\d){1,13}((\d)|x)?$/';
			$Idcard_pattern = '/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/';
		} else if ($len == 18){
			// $Idcard_pattern = '/^([1-9]{0,1})?(\d){1,16}((\d)|x)?$/';
			$Idcard_pattern = '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|(3[0-1]))\d{3}[\dx]$/';
		} else {
			return '必须为15位或者18位有效身份证号码';
		}
		if (preg_match($Idcard_pattern, $value)) {
            return true;
        }
        return '必须为有效身份证号码';

    }
    
    //检查是否为有效邮箱
    static protected function isEmail($value) {
		if (function_exists('filter_var')) {
			return filter_var($value, FILTER_VALIDATE_EMAIL) !== FALSE ? true : '必须为有效邮箱';
        }
		$hostname_pattern = '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)';
		$email_pattern = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@' . $hostname_pattern . '$/i';
		$email_return = preg_match($email_pattern, $value);
		$host_return = preg_match('/@(' . $hostname_pattern . ')$/i', $value, $regs);
		if ($email_return && $host_return) {
			if (function_exists('getmxrr') && getmxrr($regs[1], $mxhosts)) {
				return true;
			}
			if (function_exists('checkdnsrr') && checkdnsrr($regs[1], 'MX')) {
				return true;
			}
			return is_array(gethostbynamel($regs[1])) ? true : '必须为有效邮箱';
		}
		return '必须为有效邮箱';
    }
	
	//检查是否为IPv4
	static protected function isIPv4($value) {
		if (function_exists('filter_var')) {
			return filter_var($value, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV4)) !== FALSE ? true : '必须为有效IPv4地址';
		}
        $pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
        $ipv4_pattern = '/^' . $pattern . '$/';
        return preg_match($ipv4_pattern, $value) ? true : '必须为有效IPv4地址';
	}
	
	//检查是否为IPv6
	static protected function isIPv6($value) {
		if (function_exists('filter_var')) {
			return filter_var($value, FILTER_VALIDATE_IP, array('flags' => FILTER_FLAG_IPV6)) !== FALSE ? true : '必须为有效IPv6地址';
		}
        $pattern  = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}';
        $pattern .= '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})';
        $pattern .= '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})';
        $pattern .= '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)';
        $pattern .= '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
        $pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}';
        $pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|';
        $pattern .= '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}';
        $pattern .= '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))';
        $pattern .= '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})';
        $pattern .= '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)';
        $pattern .= '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]';
        $pattern .= '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})';
        $pattern .= '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';
        $ipv6_pattern = '/^' . $pattern . '$/';
        return preg_match($ipv6_pattern, $value) ? true : '必须为有效IPv6地址';
	}
}
?>
