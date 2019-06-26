<?php
namespace UNI\tools;
class dataChecker{
	
	public $data;
	public $checkRules;
	public $error;
	public $checkToken;
	
	public function __construct($data, $checkRules){
		$this->data       = $data;
		$this->checkRules = $checkRules;
	}
	
	public function check(){
		foreach($this->checkRules as $k => $rule){
			if(!isset($this->data[$k])){$this->error = $rule[2]; return false;}
			if(is_array($rule[0])){
				foreach($rule as $ruleNew){
					$methodName = 'check'.ucfirst($ruleNew[0]);
					if(!method_exists($this, $methodName)){pgExit('数据检查规则配置错误1');}
					$res = $this->$methodName($this->data[$k], $ruleNew[1]);
					if(!$res){$this->error = $ruleNew[2]; return false;}
				}
			}else{
				$methodName = 'check'.ucfirst($rule[0]);//拼接规则名称,并大写字母
				if(!method_exists($this, $methodName)){die('检查规则不存在!');} //判读是否有类方法
				$res = $this->$methodName($this->data[$k], $rule[1]);
				if(!$res){$this->error = $rule[2]; return false;}
			}
		}
		return true;
	}
	
	//字符串及长度检查
	public function checkString($checkData, $checkRule){
		$num=mb_strlen(trim($checkData),'UTF8');
		$checkRule= explode(",",$checkRule);
		if($num >=$checkRule[0] and $num <=$checkRule[1]){
			return true;
		}else{
			return false;
		}
		//return preg_match('/^.{'.$checkRule.'}$/Uis', trim($checkData));
	}
	
	//整数检查
	public function checkIsInt($checkData, $param = null){
		return preg_match('/^\-?[0-9]+$/', $checkData);
	}
	
	//整数及长度检查
	public function checkInt($checkData, $checkRule){
		return preg_match('/^\-?[0-9]{'.$checkRule.'}$/', $checkData);
	}
	
	//整数及区间
	public function checkBetweend($checkData, $checkRule){
		if(!$this->checkIsInt($checkData)){return false;}
		$checkRules = explode(',', $checkRule);
		if($checkData > $checkRules[1] || $checkData < $checkRules[0]){return false;}
		return true;
	}
	
	//数值区间
	public function checkBetween($checkData, $checkRule){
		$checkRules = explode(',', $checkRule);
		if($checkData > $checkRules[1] || $checkData < $checkRules[0]){return false;}
		return true;
	}
	
	//小数检查
	public function checkIsFloat($checkData, $param = null){
		return preg_match('/^(\d+)\.(\d+)$/', $checkData);
	}
	
	//小数及区间检查
	public function checkBetweenf($checkData, $checkRule){
		if(!$this->checkIsFloat($checkData)){return false;}
		$checkRules = explode(',', $checkRule);
		if($checkData > $checkRules[1] || $checkData < $checkRules[0]){return false;}
		return true;
	}
	
	//小数及小数位数检查
	public function checkFloatLenght($checkData, $checkRule){
		if(!$this->checkIsFloat($checkData)){return false;}
		return preg_match('/^(\d+)\.(\d{'.$checkRule.'})$/', $checkData);
	}
	
	//大于
	public function checkGt($checkData, $checkRule){
		return ($checkData > $checkRule);
	}
	
	//大于等于
	public function checkGtAndSame($checkData, $checkRule){
		return ($checkData >= $checkRule);
	}
	
	//小于
	public function checkLt($checkData, $checkRule){
		return ($checkData < $checkRule);
	}
	
	//小于等于
	public function checkLtAndSame($checkData, $checkRule){
		return ($checkData <= $checkRule);
	}
	
	//等于
	public function checkSame($checkData, $checkRule){
		return ($checkData == $checkRule);
	}
	
	//不等于
	public function checkNotSame($checkData, $checkRule){
		return ($checkData != $checkRule);
	}
	
	//邮箱
	public function checkEmail($checkData, $checkRule){
		if(!$checkData){return true;}
		return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $checkData);
	}
	
	//手机号
	public function checkPhone($checkData, $checkRule){
		if(!$checkData){return true;}
		return preg_match('/^13[0-9]{9}$|14[0-9]{9}$|15[0-9]{9}$|18[0-9]{9}$|17[0-9]{9}$/', $checkData);
	}
	
	//url
	public function checkUrl($checkData, $checkRule){
		if(!$checkData){return true;}
		//return preg_match('/^(\w+:\/\/)?\w+(\.\w+)+.*$/', $checkData);
		return preg_match('/^((ht|f)tps?):\/\/[\w\-]+(\.[\w\-]+)+([\w\-.,@?^=%&:\/~+#]*[\w\-@?^=%&\/~+#])?$/', $checkData);
	}
	
	//邮编
	public function checkZipcode($checkData, $checkRule){
		if(!$checkData){return true;}
		return preg_match('/^[0-9]{6}$/', $checkData);
	}
	
	//正则
	public function checkReg($checkData, $checkRule){
		return preg_match('/^'.$checkRule.'$/', $checkData);
	}
	
	//必须填写
	public function checkMust($checkData, $checkRule){
		if(!$checkData){return false;}
		return true;
	}
	
}