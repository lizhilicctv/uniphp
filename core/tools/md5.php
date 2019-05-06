<?php
namespace uni\tools;
class md5{
	
	public static function toMd5($str){
		$mdpass   = md5(md5($str));
		$sets     = mt_rand(10, 30);
		$text_new = mt_rand(10, 99);
		$pass1    =  substr($mdpass, 0, $sets);
		$pass2    =  substr($mdpass, $sets, (32-$sets));
		$newpass  = $pass1.$text_new.$pass2.$sets;
		return $newpass;
	}
	
	public static function getMd5($password){
		$sets  = substr($password, 34, 2);
		$pass1 = substr($password, 0, $sets);
		$pass2 = substr($password, $sets+2, (34-$sets-2));
		$newpass = $pass1.$pass2;
		return $newpass;
	}
}