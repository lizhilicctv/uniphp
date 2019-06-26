<?php
return [
	'debug'=>true,
	'session'=>[
		'start'=>true,
		'type'=>'file',
		'dir'=>'session',
		'host'=>'tcp://127.0.0.1:6379'
	],
	'suffix'=>'.html', //默认后缀
	'router'=>false, //是否开启路由
	'__manage__'=>'/static/manage/',
	'__index__'=>'/static/index/'
];



