<?php
namespace UNI\tools;
class Db{
	protected static $_instance = null;
    protected $dsn; //连接数据
    protected $pdo; //类连接
	protected $table; //表名
	protected $prefix;  //表前缀
    protected $where;  //条件
	protected $join;  //联表操作
	protected $alias;  //别名操作
	protected $Page;//第几页
	protected $Rows; //多少条
	public $eachPage; //真正分页使用
    /**
     * 构造
     *
     * @return MyPDO
     */
    private function __construct($table)
    {
		$this->table=$table;
		$config=include '../core/config/database.php';
		$this->prefix=$config['prefix'];
        try {
            $this->dsn = 'mysql:host='.$config['hostname'].';dbname='.$config['database'];
            $this->pdo = new \PDO($this->dsn, $config['username'], $config['password']);
            $this->pdo->exec('SET character_set_connection='.$config['charset'].', character_set_results='.$config['charset'].', character_set_client=binary');
        } catch (PDOException $e) {
            $this->outputError($e->getMessage());
        }
    }
	//防止克隆
    private function __clone() {}
    
    /**
     实例化类
     */
    public static function name($table)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($table);
        }
        return self::$_instance;
    }
	//添加操作
	public function add($data = null){
		if(empty($data)){throw new \Exception('添加数据为空');}
		if(!is_array($data)){throw new \Exception('插入数据错误','插入数据应为一个一维数组');}
		$data['in_time']=time();
		$data['up_time']=time();
		$this->checkFields("$this->prefix$this->table", $data);
		$this->sql   = "insert into `$this->prefix$this->table` (";
		$fields      = array(); $placeHolder = array(); $insertData  = array();
		foreach ($data as $k => $v){$fields[] = "`$k`"; $placeHolder[] = "?"; $insertData[] = $v;}
		$this->sql .= implode(', ', $fields).') values ('.implode(', ', $placeHolder).');';
		$this->pretreatment = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($insertData);
		self::$_instance = null;
		return $this->pdo->lastInsertId();
	}
	//删除操作
	public function del(){
		if(empty($this->where)){throw new \Exception('请设置where删除条件');}
		$where              = $this->getWhere();
		$this->sql          = "delete from `$this->prefix$this->table` {$where[0]};";
		$this->pretreatment = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($where[1]);
		self::$_instance = null;
		return $this->pretreatment->rowCount();
	}
	//更新操作
	public function update($data=null){
		if(is_null($data)){throw new \Exception('更新数据为空');}
		if(empty($data) || !is_array($data)){throw new \Exception('update($data) 函数的参数应该为一个一维数组');}
		if(empty($this->where)){throw new \Exception('请设置where删除条件');}
		$data['up_time']=time();
		$this->checkFields("$this->prefix$this->table", $data);
		$where = $this->getWhere();
		$this->sql   = "update `$this->prefix$this->table` set ";
		$updateData  = array();
    	foreach ($data as $k => $v){$this->sql .= "`$k` = ?, "; $updateData[] = $v;}
		$this->sql   = substr($this->sql, 0,-2).$where[0].';';
		$updateData  = array_merge($updateData, $where[1]);
		$this->pretreatment = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($updateData);
		self::$_instance = null;
		return $this->pretreatment->rowCount();
	}
	//字段自增
	public function field($filedName, $addVal=1){
		if(is_array($filedName)) $this->outputError("更新不能为数组格式");
		$this->checkFields("$this->prefix$this->table", $filedName);
		$addVal    = intval($addVal);
		$where = $this->getWhere();
		$this->sql   = "update `$this->prefix$this->table` set `{$filedName}` = `{$filedName}` + {$addVal}  ";
		$this->sql   = substr($this->sql, 0,-2).$where[0].';';
		dump($this->sql);
		$updateData  = array();
		$updateData  = array_merge($updateData, $where[1]);
		dump($updateData);
		$this->pretreatment = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($updateData);
		self::$_instance = null;
		return $this->pretreatment->rowCount();
	}
	

	//查询一条操作
	public function get($fields = null){
		$preArray            = $this->prepare($fields);
		$this->sql           = $preArray[0];
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		self::$_instance = null;
		return $this->pretreatment->fetch(\PDO::FETCH_ASSOC);
	}
	//查询字段值
	public function value($fields = null){
		if(strpos($fields,',')) $this->outputError("只能查询一个字段");
		$this->checkFields("$this->prefix$this->table", $fields);
		$preArray            = $this->prepare($fields);
		$this->sql           = $preArray[0];
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		self::$_instance = null;
		$res=$this->pretreatment->fetch(\PDO::FETCH_ASSOC);
		return $res[$fields];
	}
	//获取分页
	public function fields($fields = null){
		$preArray    = $this->prepare($fields, false);		
		$this->sql   = $preArray[0];
    	if(is_null($this->eachPage)){
			$this->sql .= $this->getLimit().';';
		}else{
			if(empty($this->totalRows)){
	    		$mode         = '/^select .* from (.*)$/Uis';
	    		preg_match($mode, $this->sql, $arr_preg);
				$sql          = 'select count(*) as total from '.$arr_preg['1'];
				if(strpos($sql, 'group by ')){$sql = 'select count(*) as total from ('.$sql.') as witCountTable;';}
	    		$pretreatment = $this->pdo->prepare($sql);
	    		$pretreatment->execute($preArray[1]);
	    		$arrTotal     = $pretreatment->fetch(\PDO::FETCH_ASSOC);
	    		$pager        = new page($arrTotal['total'], $this->eachPage);
			}else{
				$pager        = new page($this->totalRows, $this->eachPage);
			}
    		$this->sql   .= $pager->limit.';';
    	}
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		if(is_null($this->eachPage)){
			self::$_instance = null;
			return $this->pretreatment->fetchAll(\PDO::FETCH_ASSOC);
		}else{
			$this->eachPage = null;
			self::$_instance = null;
			return array($this->pretreatment->fetchAll(\PDO::FETCH_ASSOC), $pager);
		}
	}
	public function paginate($eachPage = 10, $totalRows = 0){
		$this->eachPage  = $eachPage;
		$this->totalRows = $totalRows;
		return $this;
	}
	//获取多条
	public function getAll($fields = null){
		$this->checkFields("$this->prefix$this->table", $fields);
		$preArray    = $this->prepare($fields, false);		
		$this->sql   = $preArray[0];
		if($this->Page > 0){
			$page=$this->Page-1;
			$this->sql   .= 'LIMIT '.$page.','.$this->Rows;
		}
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		return $this->pretreatment->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	//这个是分页专用查询字段
	public function prepare($fields, $limit = true){
		$this->checkFields("$this->prefix$this->table", $fields);
		$exeArray = array();
    	$join = $this->getJoin();
    	if(!empty($join)){is_null($fields) ? $sql = 'select * from '.$this->prefix.$this->table.' '.$join.' ' : $sql = 'select '.$fields.' from '.$this->prefix.$this->table.' '.$join.' ';}else{is_null($fields) ? $sql = 'select * from '.$this->prefix.$this->table.' ' : $sql = 'select '.$fields.' from '.$this->prefix.$this->table.' ';}
    	$where = $this->getWhere();
    	if(!is_null($where)){$sql .= $where[0]; $exeArray = $where[1];}
		$limit ? $sql .= $this->getGroup().$this->getOrder().$this->getLimit().';' : $sql .= $this->getGroup().$this->getOrder();
		self::$_instance = null;
    	return array($sql,$exeArray);
	}
	
	
	//查询多少条
	public function count(){
		$this->sql = "select count(*) as `total` from `$this->prefix$this->table` ";
		$where = $this->getWhere(); $this->sql.= $where[0].';';
		$return =$this->pdo->query($this->sql);
		$return = $return->fetch(\PDO::FETCH_ASSOC);
		if(empty($return['total'])){return 0;}
		self::$_instance = null;
		return $return['total'];
	}
	
	//联表操作
	public function getJoin(){
		if(empty($this->join)){return null;}
		$return = $this->join;
		$this->join = null;
		return $return;
	}
	//分组
	public function group($group){
		$this->group = $group; return $this;
	}
	
	public function getGroup(){
		if(empty($this->group)){return null;}
		$group = $this->group;
		$this->group = null;
		return ' group by '.$group.' ';
	}
	
	public function order($order){
		$this->order = $order; return $this;
	}
	
	public function getOrder(){
		if(empty($this->order)){return null;}
		$return  = 'order by '.$this->order.' ';
		$this->order = null;
		return $return;
	}
	
	public function join($join_table,$where,$type='left'){
		if($this->join == null){
			$this->join =' as '.$this->alias.' '.$type.' join '.$this->prefix.$join_table.' on '.$where;
		}else{
			$this->join .=' '.$type.' join '.$this->prefix.$join_table.' on '.$where;
		}
		return $this;
	}

	
	public function limit($length,$start=0){
		$this->limit = array($start, $length);
		return $this;
	}
	
	public function getLimit(){
		if(empty($this->limit)){return null;}
		$return = ' limit '.$this->limit[0].','.$this->limit[1].' ';
		$this->limit = null;
		return $return;
	}
	
	public function page($Page = 1, $Rows = 10){ //第一页,获取10条
		$this->Page  = $Page;
		$this->Rows = $Rows;
		return $this;
	}
	
	//支持自定义sql 语句
	public function query($sql, $execute = null){
		$this->pretreatment = $this->pdo->prepare($sql);
		return $this->pretreatment->execute($execute);
	}
	
	
	//这个是别名
	public function alias($alias){
		$this->alias = $alias;
		return $this;
	}
	
	//分解条件
	public function getWhere(){
		if(empty($this->where)){return null;}
		$return = array(' where '.$this->where[0].' ', $this->where[1]);
		$this->where = null;
		return $return;
	}
	//获取条件
	public function where($where, $array){
		$this->where[0] = $where;
		is_array($array) ? $this->where[1] = $array : $this->where[1] = array($array);
		return $this;
	}
	/**
     * checkFields 检查指定字段是否在指定数据表中存在
     */
    private function checkFields($table, $arrayFields)
    {
        $fields = $this->getFields($table);
		if(!is_array($arrayFields)){
			if (!in_array($arrayFields, $fields) and !!$arrayFields) {
			    $this->outputError("数据表内找不到 `$arrayFields` 字段");
			}
		}else{
			foreach ($arrayFields as $key => $value) {
			    if (!in_array($key, $fields)) {
			        $this->outputError("数据表内找不到 `$key` 字段");
			    }
			}
		}
    }
	  /* getFields 获取指定数据表中的全部字段名
     *
     * @param String $table 表名
     * @return array
     */
    private function getFields($table)
    {
        $fields = array();
        $recordset = $this->pdo->query("SHOW COLUMNS FROM $table");
        $this->getPDOError();
        $recordset->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $recordset->fetchAll();
        foreach ($result as $rows) {
            $fields[] = $rows['Field'];
        }
        return $fields;
    }
    
    /**
     * getPDOError 捕获PDO错误信息
     */
    private function getPDOError()
    {
        if ($this->pdo->errorCode() != '00000') {
            $arrayError = $this->pdo->errorInfo();
            $this->outputError($arrayError[2]);
        }
    }
	
	//自己定义抛出错误//这里需要更多定义
	public function outputError($messages){
		die('错误信息为:'.$messages);
	}
	
	function __destruct(){

	//echo "对象被销毁了";

	}
}