<?php
/**
* 路由配置信息
* 具体配置请参考http://wiki.intra.sina.com.cn/pages/viewpage.action?pageId=5509598
* example:
*   static public $baseUrl = array(
        APP_EXAMPLE=>'local.dagger.com/mv',
        APP_ADMIN=>''
    );

    static public $config = array(
        APP_EXAMPLE=>array(
            'blog'=>array(
                'view'=>'<id?\d+>'
            ),
            'default'=>array(
                'view'=>'<author?a_\d+:a_>/<status?s_\d+:s_>'
            )
        ),
        APP_ADMIN => array(
        ),
    );
*/
class RouterConfig {
    static public $baseUrl = array(
        APP_EXAMPLE=>'',
        APP_ADMIN=>''
    );

    static public $config = array(
        APP_EXAMPLE=>array(
            'default'=>array(
                'view'=>''
            )
        ),
        APP_ADMIN => array(
        ),
    );

    private function __construct() {
        return;
    }
}
