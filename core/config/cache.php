<?php
return [
	'start'=>true,
	'type'          => 'file', //支持类型 : file [文件型], redis
	//以下配置为 redis 类型缓存的必须设置
	'host'          => '127.0.0.1', //主机地址
	'port'          => '6379'   , //端口  redis 一般为 6379
	'pre'           => 'uni_'    //缓存变量前缀
];



