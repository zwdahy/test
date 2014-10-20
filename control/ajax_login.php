<?php

/**
 * Project:     Sapps
 * File:        
 * 
 * 
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author wangchao <wangchao@staff.sina.com.cn>
 * @package Sapps
 * @date 2010-9-1
 * @version 1.1
 */
require_once PATH_ROOT."/apps/sso/SSOConfig.php";
class Ajaxlogin extends control {
	
	public function checkPara(){
		$this->para['framelogin'] = request::post('framelogin',INT);
		if(!isset($this->para['framelogin'])) {
			$this->para['framelogin'] = request::get('framelogin',INT);
		}
		$this->para['callback'] = request::post('callback',STR);
		if(!isset($this->para['callback'])) {
			$this->para['callback'] = request::get('callback',STR);
		}
		$this->para['reason'] = request::post('reason',STR);
		if(!isset($this->para['reason'])) {
			$this->para['reason'] = request::get('reason',STR);
		}
		$this->para['retcode'] = request::post('retcode',INT);
		if(!isset($this->para['retcode'])) {
			$this->para['retcode'] = request::get('retcode',INT);
		}
		return true;
	}
	
	public function xssCallBackCheck($callback,$isframelogin=FALSE)
	{
	  if ($isframelogin)//已有<script 等，屏蔽(
	  {
	    $target = array('(','\50','%28','\x28','+');
	  }
	  else//屏蔽<
	  {
	    $target = array('<','\74','%3C','\x3C','+');
	  }
	  return str_ireplace($target,'',$callback);
	}
	
	public function action(){
		//兼容post为null的情况
		if($_POST == false || $_POST == null){
                        $_POST = array();
        }
		$sso = new SSOClient();
		$sso->setConfig("use_vf", true);
		$sso->setReturntype('META');
		$framelogin = $this->para['framelogin'];
		header("Content-Type: text/html;charset=utf-8");
		if (!empty($framelogin)) {
			$htmlHeader =  "<html><head><script language='javascript'>";
			$htmlFooter = "</script></head><body></body></html>";
		}
		$callback = $this->para['callback'];
		
		$callback =  $this->xssCallBackCheck($callback,$framelogin);//过滤特殊字符
		
		//$noRedirect = $_REQUEST['noredirect'];
		$noRedirect = 0;//一定跳过去验证
		
		$js = "";
		$arrUserInfo = array();
		//$jsoner = new Services_JSON();
		if($sso->isLogined($noRedirect)){
			$arrUserInfo['result'] = true;
			if ($sso->getLoginType()) { // 确实登录了，返回用户信息
				$userInfo = $sso->getUserInfo();
				$arrUserInfo['userinfo']['uniqueid'] = $userInfo['uniqueid'];
				$arrUserInfo['userinfo']['userid'] = $userInfo['userid'];
				$arrUserInfo['userinfo']['displayname'] = $userInfo['displayname'];
				//.....
			}
			//$js = $callback .'('.$jsoner->encode($arrUserInfo).');';
			$js = $callback.'('.json_encode($arrUserInfo).');';
		} else{
			$arrUserInfo['result'] = false;
			$errno = $sso->getErrno();
			$errmsg = $sso->getError();
			if(empty($errno)) {
				$arrUserInfo['errno'] = $this->para['retcode'];
			} else {
				$arrUserInfo['errno'] = $sso->getErrno();
			}
			if(empty($errmsg)) {
				$arrUserInfo['reason'] = htmlspecialchars($this->para['reason'],ENT_QUOTES); 
			} else {
				$arrUserInfo['reason'] = htmlspecialchars($sso->getError(),ENT_QUOTES);
			}
			$arrUserInfo['reason'] = $arrUserInfo['errno'] == SSOClient::E_SYSTEM?"系统繁忙，请稍后再试": iconv('GBK','UTF-8',$arrUserInfo['reason']);
			//$js = $callback .'('.$jsoner->encode($arrUserInfo).');';
			$js = $callback .'('.json_encode($arrUserInfo).');';
		}
		echo $htmlHeader. $js . $htmlFooter;
	}
}
new Ajaxlogin('srv.sapps');
 
?>
