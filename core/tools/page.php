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
	public function __construct($totalRows, $eachPage = 10){
		$totalRows < 1 ? $this->maxPage = 1 : $this->maxPage = ceil($totalRows/$eachPage);
		$this->totalRows = $totalRows;
		$this->eachPage  = $eachPage;
		//修正当前页码
		if(UNI_PAGE < 1){
			$this->currentPage = 1;
		}else if(UNI_PAGE > $this->maxPage){
			$this->currentPage = $this->maxPage;
		}else{
			$this->currentPage = UNI_PAGE;
		}
		//获取URL
		if(UNI_URL != ''){
			$this->currentUrl = UNI_SROOT.UNI_C.'/'.UNI_M.'/'.UNI_URL;
		}else{
			$this->currentUrl = UNI_SROOT.UNI_C.'/'.UNI_M;
		}
		$suffix = APP_CONFIG["suffix"] ? APP_CONFIG["suffix"] : '/';
		$this->limit     = ' limit '.(($this->currentPage - 1) * $eachPage).','.$eachPage;
		$getsRec         = $this->addGet();
		$this->firstPage = $this->currentUrl.'/page_1'.$suffix.$getsRec;
		$this->prePage   = $this->currentUrl.'/page_'.($this->currentPage - 1).$suffix .$getsRec;
		$this->nextPage  = $this->currentUrl.'/page_'.($this->currentPage + 1).$suffix .$getsRec;
		$this->lastPage  = $this->currentUrl.'/page_'.$this->maxPage.$suffix.$getsRec;
		//分页列表
		if($this->currentPage <= 3){
			$start = 1; $end = 6;
		}else{
			$start = $this->currentPage - 2; $end = $this->currentPage + 3;
		}
		if($end > $this->maxPage){$end = $this->maxPage;}
		if($end - $start < 5){$start = $end - 5;}
		if($start < 1){$start = 1;}
		for($i = $start; $i <= $end; $i++){
			$this->listPage[$i] = $this->currentUrl.'/page_'.$i.$suffix.$getsRec;
		}
		//跳转分页
		$this->skipPage = '<select onchange="location.href=\''.$this->currentUrl.'/page_\'+this.value+\''.$suffix.$getsRec.'\';">';
		for($i = 1; $i <= $this->maxPage; $i++){
			if($i == $this->currentPage){
				$this->skipPage .= '<option value="'.$i.'" selected="selected">'.$i.'</option>';
			}else{
				 $this->skipPage .= '<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$this->skipPage .= '</select>';
	}
	
	public function pager(){
		return array($this->firstPage, $this->prePage ,$this->listPage, $this->nextPage, $this->lastPage);
	}
	
	public function skipPager(){
		return $this->skipPage;
	}
	
	public function addGet(){
		if(empty($_GET)){return '';}
		$str = '?';
		foreach($_GET as $k => $v){
			$str = $str . $k . '=' . $v . '&';
		}
		return rtrim($str, '&');
	}
}