<?php
define('UNI_START_MEMORY'    ,  memory_get_usage());//php占内存,释放变量显著
define('UNI_START_TIME'      ,  microtime(true));//毫秒
define('UNI_INDEX' 			, 'index.php'); //默认首页
define('UNI_V'     			,  '0.1');
define('UNI_DS'              ,  DIRECTORY_SEPARATOR);//分隔符
define('UNI_IN'              ,  dirname(__FILE__).UNI_DS);//当前文件的绝对路径。__FILE__魔术常量，包含文件名称
define('UNI_CONTROLLER'  , 'controllers');
define('UNI_VIEW'        , 'views');
define('UNI_MODEL'       , UNI_IN.'models');
define('UNI_TOOLS'       , 'tools');
function dump($var = null) {echo "<pre>";var_dump($var);echo "</pre>";}
if(is_file(UNI_IN.'config'.UNI_DS.'app.php')){define('APP_CONFIG', include "config/app.php");}else{throw new Exception("配置文件app.php,不存在");}
if(is_file(UNI_IN.'tools'.UNI_DS.'page.php')){include "tools/page.php";}else{throw new Exception("page类,不存在");}
if(is_file(UNI_IN.'tools'.UNI_DS.'db.php')){include "tools/db.php";}else{throw new Exception("db类,不存在");}
if(is_file(UNI_IN.'tools'.UNI_DS.'dataChecker.php')){include "tools/dataChecker.php";}else{throw new Exception("验证类,不存在");}
//模型
function model($modelName){
	$modelName = 'uni\\model\\'.$modelName;
	$model = new $modelName();
	return $model;
}
//自动加载
function __uniAutoLoad($className){
	$fileUri = UNI_IN.'tools/'.substr($className, 10).'.php';
	if(UNI_DS == '/'){$fileUri = str_replace('\\', '/', $fileUri);}
	if(is_file($fileUri)){require $fileUri;}
	//引入model
	$filemodel=UNI_IN.'model/'.substr($className,10).'.php';
	if(UNI_DS == '/'){$filemodel = str_replace('\\', '/', $filemodel);}
	if(is_file($filemodel)){require $filemodel;}
}
spl_autoload_register('__uniAutoLoad');
class uni{
	public    $gets;
	public function __construct(){
		if(is_file(UNI_PATH.UNI_DS.'base.php')){include UNI_PATH.UNI_DS.'base.php';}
	}
	public function __init(){
		//过滤POST
		if(!empty($_POST)){
			define('UNi_POST', true);
			$_POST = str_replace(array('<','>', '"', "'"),array('&lt;','&gt;', '&quot;', ''), $_POST); //过滤了post请求
		}else{
			define('UNi_POST', false);
		}
		//过滤GET
		if(!empty($_GET)){$_GET = str_replace(array('<','>', '"', "'"),array('&lt;','&gt;', '&quot;',''), $_GET);}
		if(!empty($this->gets)){$this->gets = str_replace(array('<','>', '"', "'"),array('&lt;','&gt;', '&quot;',''), $this->gets);}
	}
	public function index(){}
	public function display($tplName = null,$type=false){	
		if($type){
			$tplUrl = UNI_PATH.'/'.UNI_VIEW.'/'.$tplName;
			if(is_file($tplUrl)){include($tplUrl);}else{die("视图模版不存在");}
		}else{
			$tplUrl = is_null($tplName) ? UNI_PATH.'/'.UNI_VIEW.'/'.UNI_C.'/'.UNI_M.'.php' : UNI_PATH.'/'.UNI_VIEW.'/'.UNI_C.'/'.$tplName;
			if(is_file($tplUrl)){include($tplUrl);}else{die("视图模版不存在");}
		}
		die;
	}
	public function success($msg='',$url='',$wait=3,$code=1){	
		$this->gets=['msg'=>$msg,'url'=>$url,'wait'=>$wait,'code'=>$code];
		$tplUrl =  UNI_IN.'/template/dispatch_jump.php';
		if(is_file($tplUrl)){include($tplUrl);}
	}
	public function error($msg='',$url='',$wait=3,$code=0){	
		$this->gets=['msg'=>$msg,'url'=>$url,'wait'=>$wait,'code'=>$code];
		$tplUrl =  UNI_IN.'/template/dispatch_jump.php';
		if(is_file($tplUrl)){include($tplUrl);}
	}
}
//下面是自定义函数
//去除空白字符
function trimAll($str){$qian=array(" ","　","\t","\n","\r");$hou=array("","","","",""); return str_replace($qian,$hou,$str); }
function json($data){header('Content-type: application/json');exit(json_encode($data));}
function jump($jump=null){if(!$jump){header('location:'.UNI_SROOT);}else{header('location:'.$jump);} exit;}
//缓存方法
function cache($name,$data='uni_lizhili_123',$time=3600){ //默认过期时间为一个小时
	if(is_file(UNI_IN.'config'.UNI_DS.'cache.php')){$cache=include(UNI_IN.'config'.UNI_DS.'cache.php');}else{die('cache配置不存在!');}
	if(!$cache['start']){die('cache 没有开启');}
	if(is_file(UNI_IN.'cache'.UNI_DS.$cache['type'].'Cacher.php')){require_once UNI_IN.'cache'.UNI_DS.$cache['type'].'Cacher.php';}else{die($cache['type'].'缓存类文件不存在!');}
	$className = 'uni\\cache\\'.$cache['type'].'Cacher';
	$cacher   = $className::getInstance($cache);
	if($name=='rm_all'){//这里是进行清除全部操作
		return	$cacher->clearCache();
	}else{
		if($data == 'uni_lizhili_123'){ //这个是要读取缓存
			return	$cacher->get($name);
		}elseif($data==null){ //这个是要删除指定缓存
			return $cacher->removeCache($name);
		}else{ //这个是要设置缓存
			return	$cacher->set($name,$data,$time);
		}
	}
}
//成功跳转
function success($jump=null){if(!$jump){header('location:'.UNI_SROOT);}else{header('location:'.$jump);} exit;}
//开启session
function startSession(){
	switch(APP_CONFIG['session']['type']){
		case 'file' :
			if(!is_dir(UNI_IN.APP_CONFIG['session']['dir'])){mkdir((UNI_IN.APP_CONFIG['session']['dir']),0777,true);}
			session_save_path(UNI_IN.APP_CONFIG['session']['dir']); //设置session 路径
		break;
		case 'redis':
			//ini_get,ini_set php本身配置,配置php,配置缓存类型和地址
			ini_set("session.save_handler", "redis");
			ini_set("session.save_path", APP_CONFIG['session']['host']);
		break;
		default:
			if(!is_dir(UNI_IN.APP_CONFIG['session']['dir'])){mkdir((UNI_IN.APP_CONFIG['session']['dir']),0777,true);}
			session_save_path(UNI_IN.APP_CONFIG['session']['dir']); //设置session 路径
	}
	session_start();
	session_write_close();//关闭session堵塞
}
//设置 session
function setSession($name, $val){
	session_start();
	$_SESSION[$name] = $val;
	session_write_close();
}
//获取 session
function getSession($name){if(isset($_SESSION[$name])){return $_SESSION[$name];} return null;}
//销毁指定的session
function removeSession($name){
	if(empty($_SESSION[$name])){return null;}
	session_start();
	unset($_SESSION[$name]);
	session_write_close();
}
// 设置 cookie
function uniSetCookie($name, $val, $expire = 31536000){
	$expire += time();
	@setcookie($name, $val, $expire, '/');
	$_COOKIE[$name] = $val;
}
//获取 session
function uniGetCookie($name){if(isset($_COOKIE[$name])){return $_COOKIE[$name];} return null;}
//删除 cookie
function uniRemoveCookie($name){
	setcookie($name, 'null', time() - 1000, '/');
}
//路径解析
function u($c, $m, $params = '', $page = null){
	$suffix = APP_CONFIG['suffix'] ? APP_CONFIG['suffix'] : '/';
	$page = $page != null ? '/page_'.$page : '';
	if(is_array($params)){
		return UNI_SROOT.'/'.$c.'/'.$m.'/'.implode('/', $params).$page.$suffix;
	}else{
		if($params != ''){
			return UNI_SROOT.'/'.$c.'/'.$m.'/'.$params.$page.$suffix;
		}else{
			return UNI_SROOT.'/'.$c.'/'.$m.$page.$suffix;
		}
	}
}
//router //把路径分解为数组
function UNI_Router(){
	if(isset($_SERVER["REQUEST_URI"])){
		$path = $_SERVER["REQUEST_URI"];
		unset($_SERVER["REQUEST_URI"]);
	}else{
		$path = 'index/index';
	}
	if(APP_CONFIG['suffix']){$path = str_replace(APP_CONFIG['suffix'], '', $path);} //去掉后缀
	$router = explode('/', $path);//拆分成数组
	if(strpos($router[count($router)-1],'?') <> 0){$router[count($router)-1]=explode('?', $router[count($router)-1])[0];} //自己编写路由
	if(empty($router[0])){array_shift($router);}//如果第一个为空，删除第一个 
	if(APP_CONFIG['router']){
		$routerArray = require(UNI_PATH.'/router.php');
		if(array_key_exists($router[0], $routerArray)){ //检查引入数组里面的可以有router[0] 吗 
			$newRouter    = array(); 
			$newRouter[0] = $routerArray[$router[0]][0];
			$newRouter[1] = $routerArray[$router[0]][1];
			if(!empty($routerArray[$router[0]][2]) && is_array($routerArray[$router[0]][2])){
				$newRouter = array_merge($newRouter, $routerArray[$router[0]][2]);	//合并数组
			}
			define("UNI_PAGE",  1); //分页
			return $newRouter;
		};
	}
	$router[0] = isset($router[0]) ?  $router[0] : 'index';
	$router[1] = isset($router[1]) ?  $router[1] : 'index';
	for($i = 2; $i < count($router); $i++){
		if(preg_match('/^page_(.*)('.APP_CONFIG['suffix'].')*$/Ui', $router[$i], $matches)){ //正则router 生成新数组 //这里主要是分页
			define("UNI_PAGE",  intval($matches[1]));
			array_splice($router, $i, 1);
		}
	}
	if(!defined("UNI_PAGE")){define("UNI_PAGE",  1);}
	return $router;
}
function UNICost(){
	return array(
		round((microtime(true) - UNI_START_TIME) * 1000, 2),
		round((memory_get_usage() - UNI_START_MEMORY) / 1024, 2)
	);
}
// 跑一下控制台
function UNIRunLog(){
	$cost = UNICost();
	echo '<script>console.log("UNIphp : 控制器 : '.UNI_C.
		', 方法 : '.UNI_M.' - 运行时间 : '. $cost[0] .'毫秒, 占用内存 : ' . $cost[1] .'k");</script>';
}
// 开始运行
try{
	$includedFiles = get_included_files(); //返回数组,获取所有引入的文件
	if(count($includedFiles) < 3){exit;} //如果小于2,停止执行
	header('content-type:text/html; charset=utf-8');//设置编码
	if(APP_CONFIG['session']['start']){startSession(APP_CONFIG);}
	$router = UNI_Router(APP_CONFIG);//执行路由,获取真实路径
	if(substr($router[0],-4)=='.php'){
		array_splice($router, 0, 1);
	}
	$controllerName = $router[0] ?? 'index';//获取控制器
	$mode = '/^([a-z]|[A-Z]|[0-9])+$/Uis';
	$res  = preg_match($mode, $controllerName); //匹配(控制器，只能是字母和数字)
	if(!$res){$controllerName = 'index';} //如果是错误的,就让他控制器为index
	$controllerFile = UNI_PATH.'/'.UNI_CONTROLLER.'/'.$controllerName.'.php';// 控制器的位置
	if(!is_file($controllerFile)){ //这里是在来一遍
		$controllerName = 'index';
		$controllerFile = UNI_PATH.'/'.UNI_CONTROLLER.'/index.php';
	}
	require $controllerFile; //引入控制器
	define('UNI_C', $controllerName);
	$controllerName = $controllerName.'Controller';
	$controller = new $controllerName; //实例化类
	if(!$controller instanceof uni){throw new Exception('必须继承uni');} //判断有没有继承类
	$methodName = $router[1]?? 'index';//获取方法
	$res  = preg_match($mode, $methodName);//方法必须为字母和数字
	if(!$res){$methodName = 'index';}
	$graceMethods = array('__init', 'display', 'json','u', 'jump');
	if(in_array($methodName, $graceMethods)){$methodName  = 'index';}
	if(!method_exists($controller, $methodName)){$fang= $methodName;$methodName  = 'index';} //检查方法是否存在类中
	define('UNI_M', $methodName);
	//define('UNI_SROOT', str_replace(UNI_INDEX, '', $_SERVER['PHP_SELF'])); //解析路径,删除index.html
	if(substr(explode('/',$_SERVER['PHP_SELF'])[1],-4)=='.php' and explode('/',$_SERVER['PHP_SELF'])[1] != 'index.php'){define('UNI_SROOT', '/'.explode('/',$_SERVER['PHP_SELF'])[1]);}else{define('UNI_SROOT', '');}
	array_shift($router); //删除第一个元素
	array_shift($router);
	$controller->gets = $router; //获取参数
	if(isset($fang)){array_push($controller->gets,$fang);} //追加数组,这里方法是,url第二位不是方法的时候操作
	define('UNI_GETS', $controller->gets);//参数转成数值
	define('UNI_URL', implode('/', $router));//参数转成数值
	if(is_file(UNI_IN.UNI_DS.'common.php')){include(UNI_IN.UNI_DS.'common.php');} //执行自定义函数
	call_user_func(array($controller, '__init'));//执行控制下面的 init方法
	call_user_func(array($controller, $methodName));//执行方法
	//if(APP_CONFIG['debug']){UNIRunLog();}//执行
}catch(UNIException $e){$e->showBug();}