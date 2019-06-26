<?php
//缓存我已经改了和tp一样了基本用法是
//cache('kk',$data)  cache('kk')  cache('kk',null)  cache('rm_all');
namespace uni\cache;
class fileCacher{
	private static $cacher = null;
	private $cacheDir = 'caches';
	
	private function __construct($config){
		$this->cacheDir = UNI_IN.'runtime/';
		if(!is_dir($this->cacheDir)){mkdir($this->cacheDir, 0777, true);}
	}
	
	public static function getInstance($config){
		if(self::$cacher == null){self::$cacher = new fileCacher($config);}
		return self::$cacher;
	}
	
	public function get($name){
		$cacheFile = $this->cacheDir.md5($name).'.php';
		if(!is_file($cacheFile)){return false;}
		$cacheData = require $cacheFile;
		$cacheData = unserialize($cacheData);
		if($cacheData['expire'] < time()){return false;}
		return $cacheData['data'];
	}
	
	public function set($name, $data, $expire){
		$cacheFile = $this->cacheDir.md5($name).'.php';
		$cacheContent = '<?php
$data = <<<EOF
';
		$cacheData = array(
			'data'   => $data,
			'expire' => time() + $expire
		);
		$cacheData = str_replace('\\', '\\\\', serialize($cacheData));
		$cacheData = str_replace('$', '\$', $cacheData);
		$cacheContent .=  $cacheData.'
EOF;
return $data;';
		file_put_contents($cacheFile, $cacheContent);
		return true;
	}
	
	public function removeCache($name){
		$cacheFile = $this->cacheDir.md5($name).'.php';
		if(!is_file($cacheFile)){return true;}
		unlink($cacheFile);
		return true;
	}
	
	public function clearCache(){
		$files = scandir($this->cacheDir);
		foreach($files as $v){
			if($v != '.' && $v != '..'){
				$cacheUrl = $this->cacheDir.$v;
				if(is_file($cacheUrl)){
					@unlink($cacheUrl);
				}
			}
		}
		return true;
	}
	
	public function close(){
		
	}
}