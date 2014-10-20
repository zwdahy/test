<?php
/**
 * 
 *	搜索
 * 
 * @package 
 * @author 高超6928<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * 2014/5/14
 * 返回的结果数组结构如下
 * 
 * 
 */
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioCheckStream extends dRadio{
	/**
	 * 通过epgid来判断m3u8流的好坏
	 * @param string $epgid
	 * @return 0/1	//1表示aac的 2表示ts 0表示坏的 false表示判断失败
	 */
	public function checkAllStream($online=1,$fromdb=false) {
		//缓存1分钟
		$key = MC_KEY_RADIO_CHECK_STREAM_AFTER;
		$tmp = $this->getCacheData($key);
		if(empty($tmp) || $tmp==false || $fromdb){
			//获取全部电台信息
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$tmp = $mRadio->getAllRadioList($online,true);
				$tmp = $tmp['result'];
				foreach($tmp as $k => &$v){
					$v['name'] = explode('|',$v['info']);
					$v['name'] = $v['name'][0];
					//根据epgid拼接链接判断好坏
	//				print '<pre>';
	//				print_r($v);
					//$url = 'http://wtv.v.iask.com/player/ovs1_rt_chid_'.$v['epgid'].'_br_3_pn_weidiantai_tn_0_sig_md5.m3u8';
//					$url = 'http://202.106.169.170/player/ovs1_idx_chid_'.$v['epgid'].'_br_200_fn_3_pn_weitv_sig_md5.m3u8';
					$url = 'http://202.106.169.170/player/ovs1_idx_chid_'.$v['epgid'].'_br_48_fn_3_pn_weitv_sig_md5.m3u8';
					$ctx = stream_context_create(array( 'http' => array( 'timeout' => 1 ) ) ); 
					$res = file_get_contents($url, 0, $ctx);
					// /9d.v.iask.com/663147/2014051617/663147c400k1400231190_05719.aac
					// /mtv.v.iask.com/material/black_2sec.ts
					$v['st'] = 0;
					if(strpos($res,'.aac')){
						$v['st'] = 1;//.aac
					}
					if(strpos($res,'/mtv.v.iask.com/material/black_2sec.ts')){
						$v['st'] = 1;//.ts
					}
					usleep(200);
				}
				unset($v);
//						error_log(strip_tags(print_r($tmp, true))."\n", 3, "/tmp/err.log");
				$this->setCacheData($key,$tmp,300);
		}
			return $tmp;
	}
}
?>
