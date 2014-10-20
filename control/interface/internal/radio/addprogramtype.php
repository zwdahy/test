<?php
/**
 * Project:     电台管理后台接口
 * File:        addclassification.php
 * 
 * 添加分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/addprogramtype.php
 * @copyright sina.com
 * @author zhanghu <zhanghu@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class addClassification extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}



		$this->para['infos'] = request::post('infos', 'STR');
if(!empty($this->para['infos'])){//更新排序
}else{
		$this->para['program_type'] = request::post('program_type', 'STR');
        if(empty($this->para['program_type'])){
			$this->display(array('errno' => -1, 'errmsg' => '类别不能不写啊 亲'), 'json');
			exit;
        }
}
		$this->para['upuid'] = request::post('upuid', 'STR');		// 更新人UID
		// $this->para['uptime'] = request::post('uptime', 'STR');		// 更新时间
		return true;
	}
    protected function action() {
        $obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');

        if(!empty($this->para['infos'])){
            $infos = explode(',', $this->para['infos']);
            foreach($infos as $info){
                $sort_programid = explode('|', $info);
                $res = $obj->updateRadioProgramTypeSort($sort_programid[1], $sort_programid[0]);
            }
            $data = array(
                'errno' => 1,
                'errmsg' => '成功',
                'result' =>$res
            );
            $this->display($data, 'json');
            return true;
        }else{

            $name = $this->para['program_type'];
            //判断分类是否已存在
            $rs = $obj->getRadioProgramTypeList();
            if(count($rs['result'])>0){
                $types = array();
                foreach($rs['result'] as $v){
                    $types[] = $v['program_type'];
                }
                if(in_array($name,$types)){
                    unset($types);
                    global $_LANG;
                    $data = array(
                        'errno' => -9,
                        'errmsg' => '此类别已存在，不需重复添加'
                    );
                    $inarray = 1;
                }
            }	
            if($inarray!=1){
                date_default_timezone_set('PRC');
                $now =time();
                $date = date("Y-m-d H:i:s",$now); 
            /* $args = array(
                'classification_name' => $name,			
                'upuid' => $this->para['upuid'],
                'uptime' => $date
            ); */
                $result = $obj->insertRadioProgramType($name);
                $data = array();
                if($result['errorno'] == 1) {
                    $data = array(
                        'errno' => 1,
                        'errmsg' => '成功',
                        'result' => $result['classification_name']
                    );
                } else {
                    global $_LANG;
                    $data = array(
                        'errno' => -9,
                        'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
                    );
                }
            }

        }	
		$this->display($data, 'json');
		return true;
	}
}
new addClassification(RADIO_APP_SOURCE);
?>
