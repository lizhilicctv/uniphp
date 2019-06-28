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
	public $pageRows; //真正分页使用
	public $getLimit; //这个用于数据
	public $limit; //这个用于数据
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
   //实例化类
    public static function name($table)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($table);
        }
        return self::$_instance;
    }
	//添加操作
	//Db::name('test')->add(['name'=>34]);
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
	//Db::name('lizhili')->where('id = ?', 1)->del();
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
	//Db::name('lizhili')->where('id =?', 1)->update(['name'=>33]);
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
	//字段自增 这个是update的一种 自增
	//Db::name('lizhili')->where('id = ?',2)->field('name');
	public function field($filedName, $addVal=1){ 
		if(is_array($filedName)) $this->outputError("更新不能为数组格式");
		$this->checkFields("$this->prefix$this->table", $filedName);
		$addVal    = intval($addVal);
		$where = $this->getWhere();
		$this->sql   = "update `$this->prefix$this->table` set `{$filedName}` = `{$filedName}` + {$addVal}  ";
		$this->sql   = substr($this->sql, 0,-2).$where[0].';';
		$updateData  = array();
		$updateData  = array_merge($updateData, $where[1]);
		$this->pretreatment = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($updateData);
		self::$_instance = null;
		return $this->pretreatment->rowCount();
	}

	//查询一条操作 //可以使用jion 但是注意 条件前缀
	//Db::name('test')->where('id = ?',2)->get();
	//Db::name('test')->where('id > ?',6)->get();
	//Db::name('test')->where('id = ?',6)->get('id,name');
	public function get($fields = null){ 
		if($fields){$fields=$this->setfields($fields);}
		$preArray            = $this->prepare($fields);
		$this->sql           = $preArray[0];
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		self::$_instance = null;
		return $this->pretreatment->fetch(\PDO::FETCH_ASSOC);
	}
	//查询字段值 这个是查询的一种,查询一个字段
	//Db::name('test')->where('id = ?',2)->value('id');
	public function value($fields = null){ 
		if(strpos($fields,',')) $this->outputError("只能查询一个字段");
		$this->checkFields("$this->prefix$this->table", $fields);
		if($fields){$fields=$this->setfields($fields);}
		$preArray            = $this->prepare($fields);
		$this->sql           = $preArray[0];
		
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		self::$_instance = null;
		$res=$this->pretreatment->fetch(\PDO::FETCH_ASSOC);
		$fields=trim($fields,'`');//去掉 tab 上面的符号
		return $res[$fields];
	}
	
	
	//获取分页 下面两个是用于分页的,web分页 //每页5个,调出所有数据
	//Db::name('test')->paginate(5)->fields('id,name');
	//下面已经写好样式,前台直接调用就行 echo $this->res[1]; 
	public function paginate($PageRows = 10, $totalRows = 0){ //第一个数据是,每页展示多少条,第二个是,总数据值,可以选填
		$this->pageRows  = $PageRows;
		$this->totalRows = $totalRows;
		return $this;
	}
	public function fields($fields = null){
		if($fields){$fields=$this->setfields($fields);}
		$preArray    = $this->prepare($fields, false);		
		$this->sql   = $preArray[0];
		if(empty($this->totalRows)){
			$mode         = '/^select .* from (.*)$/Uis';
			preg_match($mode, $this->sql, $arr_preg);
			$sql          = 'select count(*) as total from '.$arr_preg['1'];
			if(strpos($sql, 'group by ')){$sql = 'select count(*) as total from ('.$sql.') as witCountTable;';}
			$pretreatment = $this->pdo->prepare($sql);
			$pretreatment->execute($preArray[1]);
			$arrTotal     = $pretreatment->fetch(\PDO::FETCH_ASSOC);
			$pager        = new page($arrTotal['total'], $this->pageRows);
		}else{
			$pager        = new page($this->totalRows, $this->pageRows);
		}
		$this->sql   .= $pager->limit.';';
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		$this->pageRows = null;
		self::$_instance = null;
		$data=$pager->pager();
		$html='<style type="text/css">.uni-pager{max-width:600px;margin:0 auto;font-family: "微软雅黑"; color:#333;}.uni-pager a{display:inline-block; background:#F5F5F5; padding:0px 10px; height:30px; line-height:30px;  margin:3px; border-radius:2px;}.uni-pager a:hover{background:#2F4056; text-decoration:none; color:#FFF;}.uni-pager .uni-current{background:#5FB878 !important; color:#FFF !important;}</style>';
		$html.='<div style="width:600px; padding:0 50px; margin:20px auto;"> <div class="uni-pager"><a href="'.$data['firstPage'].'">首页</a>';
		$html.='<a href="'.$data['prePage'].'">上一页</a>';
        foreach($data['listPage'] as $k => $v){
            if($k == $data['currentPage']){
                $html.='<a href="'.$v.'" class="uni-current">'.$k.'</a>';
            }else{
                $html.='<a href="'.$v.'">'.$k.'</a>';
            }
        }
        $html.='<a href="'.$data['nextPage'].'">下一页</a>';
		$html.='<a href="'.$data['lastPage'].'">尾页</a></div></div>';
		return array($this->pretreatment->fetchAll(\PDO::FETCH_ASSOC),$html,$data);
		
	}
	
	//这个是分页专用查询字段 这个用来查询指定字段的,get()和getall() 里面的
	//这里规定,字段写法为'id,name'
	public function prepare($fields, $limit = true){
		$exeArray = array();
    	$join = $this->getJoin();
    	if(!empty($join)){is_null($fields) ? $sql = 'select * from '.$this->prefix.$this->table.' '.$join.' ' : $sql = 'select '.$fields.' from '.$this->prefix.$this->table.' '.$join.' ';}else{is_null($fields) ? $sql = 'select * from '.$this->prefix.$this->table.' ' : $sql = 'select '.$fields.' from '.$this->prefix.$this->table.' ';}
    	$where = $this->getWhere();
    	if(!is_null($where)){$sql .= $where[0]; $exeArray = $where[1];}
		$limit ? $sql .= $this->getGroup().$this->getOrder().$this->getLimit().';' : $sql .= $this->getGroup().$this->getOrder();
		self::$_instance = null;
    	return array($sql,$exeArray);
	}
	
	//这个用于api 的分页 第一个参数为页码,第二个为每页几个
	//Db::name('test')->page(3,3)->getall();
	public function page($Page = 1, $Rows = 10){ //第一页,获取10条
		$this->Page  = $Page;
		$this->Rows = $Rows;
		return $this;
	}
	
	//获取多条,这个有好几种写法
	//Db::name('test')->getall();
	//Db::name('test')->where('id > ?',10)->getall();
	// Db::name('article') //这个写法没有错误,但是注意默认是左联,
	// ->alias('a')
	// ->join('cate c','c.id = a.cateid')
	// ->join('test t','t.id = a.id')
	// ->order('a.id desc')->getall();
	public function getAll($fields = null){
		//$this->checkFields("$this->prefix$this->table", $fields); 多表就不能检查了
		if($fields){$fields=$this->setfields($fields);}
		$preArray    = $this->prepare($fields, false);		
		$this->sql   = $preArray[0];
		if($this->Page > 0){
			$page=($this->Page-1) * $this->Rows;
			$this->sql   .= 'LIMIT '.$page.','.$this->Rows;
		}
		if(!!$data = $this->getLimit()){
			$this->sql  .= $data;
		}
		$this->pretreatment  = $this->pdo->prepare($this->sql);
		$this->pretreatment->execute($preArray[1]);
		self::$_instance = null;
		return $this->pretreatment->fetchAll(\PDO::FETCH_ASSOC);
	}
	//查询多少条 这个是聚合函数,统计使用
	//Db::name('test')->count();
	//Db::name('test')->where('id > ? and id < ?',[7,10])->count();
	public function count(){
		$this->sql = "select count(*) as `total` from `$this->prefix$this->table` ";
		$where = $this->getWhere(); 
		$this->sql.= $where[0].';';
		$this->pretreatment =$this->pdo->prepare($this->sql);
		$this->pretreatment->execute($where[1]);
		$return = $this->pretreatment->fetch(\PDO::FETCH_ASSOC);
		self::$_instance = null;
		if(empty($return['total'])){return 0;}
		return $return['total'];
	}
	
	//这个是别名 下面三个是,方法,真正的调用,在get 和getall里面
	// Db::name('article')
	// ->alias('a')
	// ->join('cate c','c.id = a.cateid')
	// ->order('a.id desc')->get();
	public function alias($alias){
		$this->alias = $alias;
		return $this;
	}
	//联表操作 
	public function getJoin(){
		if(empty($this->join)){return null;}
		$return = $this->join;
		$this->join = null;
		return $return;
	}
	//下面用于联表操作
	public function join($join_table,$where,$type='left'){
		if($this->join == null){
			$this->join =' as '.$this->alias.' '.$type.' join '.$this->prefix.$join_table.' on '.$where;
		}else{
			$this->join .=' '.$type.' join '.$this->prefix.$join_table.' on '.$where;
		}
		return $this;
	}
	
	//分组 //这里没有编辑,没有使用
	public function group($group){
		$this->group = $group; return $this;
	}
	public function getGroup(){
		if(empty($this->group)){return null;}
		$group = $this->group;
		$this->group = null;
		return ' group by '.$group.' ';
	}
	
	//下面两个是用于排序的 这个是用于多条查询的
	//Db::name('test')->order('id desc')->getall();
	public function order($order){
		$this->order = $order; return $this;
	}
	public function getOrder(){
		if(empty($this->order)){return null;}
		$return  = 'order by '.$this->order.' ';
		$this->order = null;
		return $return;
	}
	
	//下面两个是用于查询条数的
	//Db::name('test')->where('id > ?',9)->order('id desc')->limit(3)->getall();
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
	
	//支持自定义sql 语句 自定义查询 //这里我默认是进行查询操作,如果需要其他操作在更新
	//Db::name('name')->query('SELECT * FROM lizhili_admin WHERE id = ?',1);
	public function query($sql, $execute = null){
		if(!is_array($execute)){$execute=[$execute];}
		$this->pretreatment = $this->pdo->prepare($sql);
		$this->pretreatment->execute($execute);
		return $this->pretreatment->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	//分解条件 如果有两个条件,where这么写 where('id > ? and id < ?',[7,10])
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
	
     //checkFields 检查指定字段是否在指定数据表中存在
     //下面两个方法是拥有检查字段是否存在,这里需要在继续写
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
	 // getFields 获取指定数据表中的全部字段名
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
    //getPDOError 捕获PDO错误信息 
    private function getPDOError()
    {
        if ($this->pdo->errorCode() != '00000') {
            $arrayError = $this->pdo->errorInfo();
            $this->outputError($arrayError[2]);
        }
    }
	//这个是为了把字段添加上`` tab 上面的符号
	public function setfields($data){
		$data=explode(",", $data);
		$data=array_filter($data);
		$res='';
		foreach($data as $k=>$v){
			if(!strpos($v,'.')){
				$res.='`'.$v.'`,';
			}else{
				$res.=$v.',';
			}
		}
		return substr($res, 0, -1);
	}
	//自己定义抛出错误//这里需要更多定义
	public function outputError($messages){
		die('错误信息为:'.$messages);
	}
	
	function __destruct(){
	//echo "对象被销毁了";
	}
}