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
 * Example:  {$var|sina_photo:'thumbnail'}
 
 * @author   yunkun <yunkun@staff.sina.com.cn>
 * @version 1.0
 * @param string
 * @param string
 * @return string
 */
function smarty_modifier_sina_photo($pid, $type='thumbnail')
{
    /**
	$num = (hexdec(substr($pid, -2)) % 16) + 1;
	$url = sprintf(PHOTO_URL, $num, $type, $pid);
	return $url;*/
	$image = clsFactory::create(CLASS_PATH.'tools/image', 'ImageUrl','service');
	return $image->get_image_url($pid, $type);
}

/* vim: set expandtab: */

?>
