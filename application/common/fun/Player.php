<?php
namespace app\common\fun;

//播放器
class Player{
    
    /**
     * 播放器总入口
     * @param string $url
     * @param number $width
     * @param number $height
     * @param string $autoplay
     * @return string
     */
    public function play($url='',$width=600,$height=400,$autoplay=false){
        $width || $width=600;
        $height || $height=400;
        if(IN_WAP===true && $width>=400){
            $width = '100%';
            $height = '250';
        }
        if(is_numeric($width)){
            $width .= 'px';
        }
        if(is_numeric($height)){
            $height .= 'px';
        }
        
        if (preg_match('/^(http)/i', $url)&&!preg_match('/\.([a-z0-9]{3,5})$/i', $url)) {
        //if (strstr($url,'player.youku.com')) {
            return "<iframe src='{$url}' height='$height' width='$width' frameborder='0' allowfullscreen></iframe>";
        }
        
        if (!preg_match('/^(http|\/public)/i', $url)) {
            $url = tempdir($url);
        }
        
        if (strstr($url,'.swf')) {
            return $this->swfpay($url,$width,$height);
        }
        
        $content = $this->ckplayer($url,$width,$height);
        return $content;
    }
    
    /**
     * 默认使用CK播放器
     * @param string $url
     * @param string $width
     * @param string $height
     * @return string
     */
    private function ckplayer($url='',$width='',$height=''){
        static $array_id = 0;
        $array_id++;
        $js = 0;
        if($array_id==1){
            $js = '<script type="text/javascript" src="'.config('view_replace_str.__STATIC__').'/libs/ckplayer/ckplayer.js"></script>';
        }
        return "{$js}<div class='video{$array_id}' style='width: {$width};height: {$height};'></div>
                <script type='text/javascript'>
                	var videoObject = {
                		container: '.video{$array_id}', //“#”代表容器的ID，“.”或“”代表容器的class
                		variable: 'player{$array_id}',  //该属性必需设置，值等于下面的new chplayer()的对象
                		//poster:'pic/wdm.jpg',//封面图片
                		video:'{$url}'   //视频地址
                	};
                	var player{$array_id} = new ckplayer(videoObject);
                </script>";
    }
    
    /**
     * FLASH需要点击激活，所以要使用原始播放器
     * @param string $url
     * @param string $width
     * @param string $height
     * @return string
     */
    private function swfpay($url='',$width='',$height=''){
        return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0' width='{$width}' height='{$height}'>
<param name=movie value='{$url}' ref>
<param name=quality value=High>
<param name='_cx' value='12383'>
<param name='_cy' value='1588'>
<param name='FlashVars' value>
<param name='Src' ref value='{$url}'>
<param name='WMode' value='Window'>
<param name='Play' value='-1'>
<param name='Loop' value='-1'>
<param name='SAlign' value>
<param name='Menu' value='-1'>
<param name='Base' value>
<param name='AllowScriptAccess' value='always'>
<param name='Scale' value='ShowAll'>
<param name='DeviceFont' value='0'>
<param name='EmbedMovie' value='0'>
<param name='BGColor' value>
<param name='SWRemote' value>
<param name='MovieData' value>
<embed src='{$url}' quality=high pluginspage='http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash' type='application/x-shockwave-flash' width='{$width}' height='{$height}'>
</embed>
</object>";
    }
     
}