<?php
//自定义函数
function tonow($data){
	$data=time()-$data;
	//计算天数
    $days = intval($data/86400);
    //计算小时数
    $remain = $data%86400;
    $hours = intval($remain/3600);
    //计算分钟数
    $remain = $data%3600;
    $mins = intval($remain/60);
    //计算秒数
    $secs = $data%60;
	return $days.'天'.$hours.'小时'.$mins.'分钟'.$secs.'秒';
}
