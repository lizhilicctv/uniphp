<?php
namespace UNI\tools;
class page{
	public $totalRows;
	public $eachPage;
	public $maxPage;
	public $limit;
	public $currentPage = 1;
	public $firstPage;
	public $prePage;
	public $listPage = array();
	public $nextPage;
	public $lastPage;
	public $skipPage;
	public function __construct($totalRows, $eachPage = 10){ //第一个是总数,第二个是每页多少条
		$totalRows < 1 ? $this->maxPage = 1 : $this->maxPage = ceil($totalRows/$eachPage); //获取最大页码
		$this->totalRows = $totalRows; //总数
		$this->eachPage  = $eachPage; //每页数量
		//修正当前页码,获得当前页码
		if(UNI_PAGE < 1){
			$this->currentPage = 1;
		}else if(UNI_PAGE > $this->maxPage){
			$this->currentPage = $this->maxPage;
		}else{
			$this->currentPage = UNI_PAGE;
		}
		//获取URL,获得url 地址
		if(UNI_URL != ''){
			if(UNI_SROOT){
				$this->currentUrl = UNI_SROOT.'/'.UNI_C.'/'.UNI_M.'/'.UNI_URL;
			}else{
				$this->currentUrl = UNI_SROOT.UNI_C.'/'.UNI_M.'/'.UNI_URL;
			}
		}else{
			if(UNI_SROOT){
				$this->currentUrl = UNI_SROOT.'/'.UNI_C.'/'.UNI_M;
			}else{
				$this->currentUrl = UNI_SROOT.UNI_C.'/'.UNI_M;
			}
			
		}
		$this->limit     = ' limit '.(($this->currentPage - 1) * $eachPage).','.$eachPage;
		$suffix = APP_CONFIG["suffix"] ? APP_CONFIG["suffix"] : '/';
		$getsRec         = $this->addGet(); //获取get变量 ,用于 上面的url和页码和get变量 进行拼接 获取地址
		$this->firstPage = '/'.$this->currentUrl.'/page_1'.$suffix.$getsRec;
		$this->prePage   =$this->currentPage - 1 <=0 ? '' : '/'.$this->currentUrl.'/page_'.($this->currentPage - 1).$suffix .$getsRec;
		$this->nextPage  =$this->currentPage + 1 >$this->maxPage ? '' :  '/'.$this->currentUrl.'/page_'.($this->currentPage + 1).$suffix .$getsRec;
		$this->lastPage  = '/'.$this->currentUrl.'/page_'.$this->maxPage.$suffix.$getsRec;
		//分页列表
		if($this->currentPage <= 3){ //当前页码
			$start = 1; $end = 5;
		}else{
			$start = $this->currentPage - 2; $end = $this->currentPage + 2;
		}
		if($end > $this->maxPage){$end = $this->maxPage;}
		if($end - $start < 5){$start = $end - 4;}
		if($start < 1){$start = 1;}
		for($i = $start; $i <= $end; $i++){
			$this->listPage[$i] = '/'.$this->currentUrl.'/page_'.$i.$suffix.$getsRec; //获取中间过渡列表地址
		}
		
		//跳转分页
	//	$this->skipPage = '<select onchange="location.href=\''.$this->currentUrl.'/page_\'+this.value+\''.$suffix.$getsRec.'\';">';
		$this->skipPage = '<select >';
		for($i = 1; $i <= $this->maxPage; $i++){
			if($i == $this->currentPage){
				$this->skipPage .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
			}else{
				 $this->skipPage .= '<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$this->skipPage .= '</select>';
	}
	
	public function pager(){ //这个是一般默认效果
		$data=array('firstPage'=>$this->firstPage, 'prePage'=>$this->prePage ,'currentPage'=>$this->currentPage,'listPage'=>$this->listPage, 'nextPage'=>$this->nextPage, 'lastPage'=>$this->lastPage);
		foreach($data as $k=>$v){
			if(is_array($v)){
				foreach($v as $k1=>$v1){
					$data[$k][$k1]=str_replace("//","/",$v1);
				}
			}else{
				$data[$k]=str_replace("//","/",$v);
			}
		}
		return $data;
	}
	
	public function skipPager(){ //这个是下拉选框效果 ,这个没有测试呢,先不使用了,使用的 时候在测试一遍
		return $this->skipPage;
	}
	
	public function addGet(){
		if(empty($_GET)){return '';}
		$str = '?';
		if($_GET["pathInfo"]){unset($_GET["pathInfo"]);}
		foreach($_GET as $k => $v){
			$str = $str . $k . '=' . $v . '&';
		}
		if(substr($str, -1)=='?'){$str=substr($str, 0, -1);}
		return rtrim($str, '&');
	}
	
}