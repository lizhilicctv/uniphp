<?php
namespace uni\tools;
class verifyCode{
    private $width;                                //图片宽度
    private $height;                               //图片高度
    public  $bgcolor         = array(255,255,255); //背景颜色
    public  $codeColor       = array(0, 0, 0);     //验证码颜色
    public  $fontSize        = 20;                 //验证码字符大小
    private $totalChars      = 4;                  //总计字符数
    private $numbers         = 1;                  //数字形式字符数量
    private $securityCode;                         //验证码内容
    public  $fontFamily      = null;               //字体文件路径
    public  $noise           = true;               //绘制干扰
    public  $sessionName     = 'UNIVcode';          //验证码在Session中储存的名称
    private $img             = null;               //绘图资源
    public  $noiseNumber     = 6;
    
	public function __construct($width = 88, $height = 30, $totalChars = 4, $numbers = 1, $fontFamily = 'AMBROSIA.ttf'){
		//随机字体
		$fontFamily=mt_rand(1,6).'.ttf';
		
		$this->fontFamily    = UNI_IN.'fonts'.UNI_DS.$fontFamily;
        $this->width         = $width;
        $this->height        = $height;
        $this->totalChars    = $totalChars;
        $this->numbers       = $numbers;
        if($this->fontFamily == null) throw new \Exception('验证码字体设置错误');
		if(!is_file($this->fontFamily)) throw new \Exception('验证码字体文件不存在');
    }
    
	private function setChars() {
        $strall = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for($i=0;$i<($this->totalChars-$this->numbers);$i++){$text[] = $strall[mt_rand(0,20)];}
        for($i=0;$i<$this->numbers;$i++){$text[] = mt_rand(2,9);}
        shuffle($text);
        $this->securityCode = implode('',$text);
        header('Content-type:image/png');
        $this->img = imagecreatetruecolor($this->width,$this->height);
    }
    
    public function draw() { 
        $this->setChars();
        setSession($this->sessionName, strtolower($this->securityCode));
        $bgColor = imagecolorallocate($this->img,$this->bgcolor[0],$this->bgcolor[1],$this->bgcolor[2]);
        imagefill($this->img,0,0,$bgColor);
        if($this->noise){$this->writeNoise();}
		//设置字体颜色随机
		foreach($this->codeColor as $k=>$v){
			$this->codeColor[$k]=mt_rand(0,150);
		}
        $textColor = imagecolorallocate($this->img,$this->codeColor[0],$this->codeColor[1],$this->codeColor[2]);
		//这样需要调整角度,产生随机效果
		 preg_match_all("/./u", $this->securityCode,$arr);
		foreach($arr[0] as $k=>$v){
			$textFffset = imagettfbbox($this->fontSize,0,$this->fontFamily,$v);
			if($k==0){
				$fx = intval(($this->width - ($textFffset[2] - $textFffset[0]))/2) -($this->width/6*2);
			}elseif($k==1){
				$fx = intval(($this->width - ($textFffset[2] - $textFffset[0]))/2) -($this->width/8*1);
			}elseif($k==2){
				$fx = intval(($this->width - ($textFffset[2] - $textFffset[0]))/2) +($this->width/8*1);
			}else{
				$fx = intval(($this->width - ($textFffset[2] - $textFffset[0]))/2) +($this->width/6*2);
			}
			$fy = $this->height - ($this->height - $this->fontSize)/2;
			imagefttext($this->img,$this->fontSize,mt_rand(-25,25),$fx,$fy,$textColor,$this->fontFamily,$v);
		}
		
        imagepng($this->img);
        imagedestroy($this->img);
    }

	private function writeNoise() {
        $code  = '0123456789abcdefghjkmnpqrstwxyz';
        for($i = 0; $i < $this->noiseNumber; $i++){
            $noiseColor = imagecolorallocate($this->img, mt_rand(150,225), mt_rand(150,225), mt_rand(150,225));
            for($j = 0; $j < 2; $j++){
                imagestring($this->img, 5, mt_rand(-10, $this->width),  mt_rand(-10, $this->height), $code[mt_rand(0, 29)], $noiseColor);
            }
        }
    }
}


