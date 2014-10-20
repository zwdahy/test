<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty cat modifier plugin
 *
 * Type:     modifier<br>
 * Name:     sina_photot<br>
 * Date:     6 28, 2010
 * Example:  {$uid|sina_icon:123:50}
 
 * @author   yunkun <yunkun@staff.sina.com.cn>
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_sina_icon($uid, $iconver, $size=50)
{
    /**
	$icons = sprintf(ICON_PATH, $uid%4+1, $uid, $size, $iconver);
	return $icons;*/
	$image = clsFactory::create(CLASS_PATH.'tools/image', 'ImageUrl','service');
    $icons = $image->get_icon_url($uid, $iconver, $size);
	return $icons;
}

/* vim: set expandtab: */

?>
