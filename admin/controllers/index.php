<?php
use UNI\tools\Db;
class indexController extends uni{
	
	//__init 函数会在控制器被创建时自动运行用于初始化工作，如果您要使用它，请按照以下格式编写代码即可：
	
	public function __init(){
		//dump(111);
	}
	
	
	public function index(){
		echo u('index','wo');
	}
	public function wo(){
		$li=$_GET;
		dump($li);
	}
}