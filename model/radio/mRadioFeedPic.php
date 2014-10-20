<?php
/**
 * 特殊Feed缩略图模型（用ImageMagick库生成的）
 *
 * @copyright  copyright(2012) weibo.com all rights reserved
 * @author  yugang2@staff.sina.com.cn
 * @package  model
 */
include_once SERVER_ROOT . 'tools/image/fImage.php';

class mRadioFeedPic extends model {
    /**
     * 背景图的相对位置
     */
    const BG_PIC  = "view/images/background.png";
    const BG_PIC_RADIO  = "view/images/backgroundradio.png";
    const ICON_PIC  = "view/images/dj_icon.png";

    /**
     * 
     * 生成电台缩略图
     * @param array $radioinfo
     */
    public function generateThumbnail(array $radioinfo) {
       	if(empty($radioinfo)){
       		throw new Exception("电台信息为空","10");
       	}
        //第1步：创建背景图对象
        if(!empty($radioinfo['img_path'])){
        	$bgpic = SERVER_ROOT . self::BG_PIC_RADIO;
        }else{
        	$bgpic = SERVER_ROOT . self::BG_PIC;
        }
        
        $resource = NewMagickWand();
        MagickReadImage($resource, $bgpic);
        //第2步：将电台信息写到背景图的图板上
        self::writeRadioInfo( $resource, $radioinfo );
        //第3步：上传生成的图片并输出相应的地址
        $filename = self::get_upload_picfile();
        MagickWriteImage($resource, $filename);
        $pid = fImage::uploadImage($filename, 'gif', ADMIN_UID);
        $url = fImage::getUrlByPids($pid,'large');
        BaseModelCommon::debug($url,'upload_pic');
        if(!empty($url)){
            return $url[$pid];
        }else{
            return '';
        }
    }
	    
	   /**
     * 
     * 生成电台缩略图,动态高清图系统
     * @param array $radioinfo
     */
    public function generateThumbnailPre(array $radioinfo) {
       	if(empty($radioinfo)){
       		throw new Exception("电台信息为空","10");
       	}
        //第1步：创建背景图对象
        if(!empty($radioinfo['img_path'])){
        	$bgpic = SERVER_ROOT . self::BG_PIC_RADIO;
        }else{
        	$bgpic = SERVER_ROOT . self::BG_PIC;
        }
        
        $resource = NewMagickWand();
        MagickReadImage($resource, $bgpic);
        //第2步：将电台信息写到背景图的图板上
        self::writeRadioInfo( $resource, $radioinfo );
		 header("Content-type: image/gif");
		MagickEchoImageBlob($resource);
    }

	/**
     * 
     * 生成电台logo缩略图
     * @param array $radioinfo
     */
    public function generateRadioThumbnail($radiopic) {
       	if(empty($radiopic)){
       		throw new Exception("电台logo地址为空","10");
       	}
        //第1步：创建背景图对象
        $bgpic = $radiopic;
        $resource = NewMagickWand();
        MagickReadImage($resource, $bgpic);
        //第2步：将图片缩放到合适大小
		$format = MagickGetImageFormat($resource);
		MagickScaleImage($resource,98,98);
		MagickSetFormat($resource, $format);
        //第3步：上传生成的图片并输出相应的地址
        $filename = self::get_upload_picfile();
        MagickWriteImage($resource, $filename);
        $pid = fImage::uploadImage($filename, 'gif', ADMIN_UID);
        $url = fImage::getUrlByPids($pid,'large');
        BaseModelCommon::debug($url,'upload_pic');
        if(!empty($url)){
            return $url[$pid];
        }else{
            return '';
        }
    }
	
    /**
     * 绘制电台信息
     * @param resource $bg MagickWand对象
     * @param array $radioinfo 电台信息
     */
    private function writeRadioInfo( &$bg, array $radioinfo ) {
        if( !$radioinfo ){
            throw new Exception('电台信息不得为空！','10');
        }
        //创建PixelWand和DrawingWand对象
        $dwand = NewDrawingWand();
        $pwand = NewPixelWand();

        //标题及字体
        PixelSetColor($pwand, "#666666");
        $font = self::get_font("msyh.ttf");
        DrawSetFont($dwand, $font);
        DrawSetFontSize($dwand, 14);
        DrawSetFillColor($dwand, $pwand);
        DrawSetGravity($dwand, MW_NorthWestGravity);
        //todo 长标题需要折分成2行
        // $title = mb_strimwidth($radioinfo['title'], 0, 18, '','UTF-8');
		$title = htmlspecialchars_decode($radioinfo['title'], ENT_QUOTES);
		if(!empty($title)){
			$title = self::mb_wordwrap($title,10);
			MagickAnnotateImage($bg, $dwand, 121, 8, 0, $title[0]);
			if(!empty($title[1])){
				MagickAnnotateImage($bg, $dwand, 121, 26, 0, $title[1]);
			}
		}
		  if(!empty($radioinfo['img_path'])){
			$radiopic = $radioinfo['img_path'];
			$radioImage = NewMagickWand();
			MagickReadImage($radioImage, $radiopic);
			$format = MagickGetImageFormat($radioImage);
			MagickScaleImage($radioImage,98,98);
			MagickSetFormat($radioImage, $format);
			MagickCompositeImage($bg,$radioImage,MW_OverCompositeOp,10,10);
		  }
	
        //dj信息
        if(!empty($radioinfo['dj'])){
				//dj icon图标
				$iconpic = SERVER_ROOT . self::ICON_PIC;
				$iconImage = NewMagickWand();
				MagickReadImage($iconImage, $iconpic);
			
				//dj 名字
				//todo dj名字取前2个，分行显示
				$dj = htmlspecialchars_decode($radioinfo['dj'][0], ENT_QUOTES);
				$dj1 = htmlspecialchars_decode($radioinfo['dj'][1], ENT_QUOTES);
			if(!empty($dj)){	
				if(mb_strlen($dj,'UTF-8')>10){
					 $dj = mb_substr($dj,0,10,'UTF-8');
				}
			}
			if(!empty($dj1)){
				if(mb_strlen($dj1,'UTF-8')>10){
					 $dj1 = mb_substr($dj1,0,10,'UTF-8');
				}
			}
				PixelSetColor($pwand, "#999999");
				$font = self::get_font("simsun.ttc");
				DrawSetFont($dwand, $font);
				DrawSetFontSize($dwand, 13);
				DrawSetFillColor($dwand, $pwand);
				DrawSetGravity($dwand, MW_NorthWestGravity);
			if($title[1]){
				MagickCompositeImage($bg,$iconImage,MW_OverCompositeOp,121,49);
				MagickAnnotateImage($bg, $dwand, 140, 47, 0, $dj );
				if(!empty($dj1)){
					MagickAnnotateImage($bg, $dwand, 140, 65, 0, $dj1 );
				}
			}else{
					MagickCompositeImage($bg,$iconImage,MW_OverCompositeOp,121,32);
					MagickAnnotateImage($bg, $dwand, 140, 30, 0, $dj );
				if(!empty($dj1)){
					MagickAnnotateImage($bg, $dwand, 140, 48, 0, $dj1 );
				}
			}
        }else{
				PixelSetColor($pwand, "#999999");
				$font = self::get_font("simsun.ttc");
				DrawSetFont($dwand, $font);
				DrawSetFontSize($dwand, 13);
				DrawSetFillColor($dwand, $pwand);
				DrawSetGravity($dwand, MW_NorthWestGravity);
				$radio_title = htmlspecialchars_decode($radioinfo['radio_title'], ENT_QUOTES);
				if(!empty($radio_title)){
					$radio_title = self::mb_wordwrap($radio_title,10);
					if($title[1]){
						MagickAnnotateImage($bg, $dwand, 121, 44, 0, $radio_title[0]);
						if(!empty($radio_title[1])){
							MagickAnnotateImage($bg, $dwand, 121, 61, 0, $radio_title[1]);
						}
					}else{	
						MagickAnnotateImage($bg, $dwand, 121, 29, 0, $radio_title[0]);
						if(!empty($radio_title[1])){
						MagickAnnotateImage($bg, $dwand, 121, 44, 0, $radio_title[1]);
						}
					}
				
				}
		}
    }

    /**
     * 获取相应的字体的ttf文件
     * @param string $font 字体名称
     * @return 字体文件绝对路径
     */
    private function get_font($font="msyh.ttf") {
        ///拼接字体的绝对路径
    	if(isset($_SERVER['SINASRV_TTF_PATH'])){
        	$font_path = $_SERVER['SINASRV_TTF_PATH'];
        }else{
        	$font_path = '/usr/local/sinasrv2/lib/X11/fonts/TTF';
        }
        $font_path = rtrim($font_path, "/") . "/" . $font;

        if (!file_exists($font_path)) {
            throw new Exception('所要加载字体不存在！','10');
        }
        return $font_path;
    }
	   /**
     * 按长度截取字段
     * @param string $str 需要截取的字符串 $width 长度
     * @return 截取好的字符串数组
     */
  
	  private function mb_wordwrap($str='', $width=20){
		$str = preg_replace(array("/([^a-zA-Z0-9\-\.])([a-zA-Z0-9\-\.])/","/([a-zA-Z0-9\-\.])(([^a-zA-Z0-9\-\.]))/","/\s+/",),array("$1 $2","$1 $2"," "),$str);
		$data = array();
		if(empty($str) || mb_strlen($str, 'UTF-8') <= $width){
			$data[] = $str;
			return $data;
		}
		$return = '';
		$break = "\n";
		$str_width = mb_strlen($str, 'UTF-8');
		$last_space = false;
		for($i=0, $count=0; $i < $str_width; $i++, $count++){
			if(mb_substr($str, $i, 1, 'UTF-8') == " "){
				$last_space = $i;
			}
			if($count >= $width){
				if(!$last_space){
					$return .= $break;
					$count = 0;
				}else{
					
					$drop = $i - $last_space;
					if($drop > 0){
						$return = mb_substr($return, 0, -$drop, 'UTF-8');
					}
					$return .= $break;
					$i = $last_space;
					$last_space = false;
					$count = 0;
				}
			}
			$return .= mb_substr($str, $i, 1, 'UTF-8');
		}
		$return = explode("\n",$return);
		$ct = count($return);
		for ($k=0; $k<$ct; $k++) {
			$v = $return[$k];
			if(empty($v)){
				unset($return[$k]);
				continue;
			}
			$data[$k] = ltrim($v);
			if(!empty(	$return[$k]) && !empty($return[$k+1]) && (mb_strlen(trim($return[$k])) <= $width*2) && (mb_strlen(trim($return[$k+1])) <= $width*2) && (mb_strlen(trim($return[$k])) + mb_strlen(trim($return[$k+1])) <= $width*3) ){
				$len=mb_strlen($return[$k],'UTF-8') + mb_strlen($return[$k+1],"UTF-8");
				if($len<=($width*1.5))  {
					$data[$k] .= rtrim($return[$k+1]);
					unset($data[$k+1]);
					$k++;
				}
			}
		}
		$data = array_slice($data,0);
		return $data;
	}
    /**
     * 获取服务器图片临时存储方式
     */
    private function get_upload_picfile() {
        $uploaddir = $_SERVER['SINASRV_PRIVDATA_DIR'];
        $uploaddir = rtrim($uploaddir, "/") . "/tmp/";
        if (!is_dir($uploaddir)) {
            mkdir($uploaddir, 0775, true);
        }
        return $uploaddir . uniqid() . "_thumbnail.gif";
    }
}
?>