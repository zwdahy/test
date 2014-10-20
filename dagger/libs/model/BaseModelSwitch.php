<?php
class BaseModelSwitch
{
    //post提交referer限制开关
    protected static $postRefererCheck = true;
    //主库操作post限制开关
    protected static $masterDbPostOnly = true;

    public static function set ($switch, $value=true) {
        $r = new ReflectionClass(__CLASS__);
        try {
            $r->setStaticPropertyValue("\0*\0{$switch}", $value);
        } catch (Exception $e) {
            BaseModelMessage::errLite(array('msg'=>"{$switch}开关不存在"));
        }
    }

    public static function open ($switch) {
        $r = new ReflectionClass(__CLASS__);
        try {
            $r->setStaticPropertyValue("\0*\0{$switch}", true);
        } catch (Exception $e) {
            BaseModelMessage::errLite(array('msg'=>"{$switch}开关不存在"));
        }
    }

    public static function close ($switch) {
        $r = new ReflectionClass(__CLASS__);
        try {
            $r->setStaticPropertyValue("\0*\0{$switch}", false);
        } catch (Exception $e) {
            BaseModelMessage::errLite(array('msg'=>"{$switch}开关不存在"));
        }
    }

    public static function check($switch, $cmpare=true) {
        $r = new ReflectionClass(__CLASS__);
        try {
            $s = $r->getStaticPropertyValue("\0*\0{$switch}");
            return $s === $cmpare;
        } catch (Exception $e) {
            BaseModelMessage::errLite(array('msg'=>"{$switch}开关不存在"));
        }
    }
}
