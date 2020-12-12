<?php
use UNI\tools\Db;
class indexController extends uni{
	public function __init(){
		parent::__init();
	}
	public function index(){
     $this->display('index',['name' => 'UNI 框架']);
	}
}