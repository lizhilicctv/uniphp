<?php
namespace uni\model; //使用命名空间
use UNI\tools\Db;
class manage extends \uni{
    public function __construct(){ //构造方法
		
    }
	public function isLogin(){
		if(!getSession('admin_name') or !getSession('admin_id')){
			return $this->error('请登陆后访问',u('login','index'));
		}
		$auth=new \UNI\tools\Auth();
		$url=UNI_C.'/'.UNI_M;
		if(getSession('admin_id')!=1){
			if($url!='index/index'){
				if($url!='index/main'){
					if(!$auth->check($url,getSession('admin_id'))){
						$this->error('没有权限',2);
					}
				}
			}
		}
		setSession('comment_header',Db::name('comment')->where('isopen = ?','-1')->count());
		setSession('message_header',Db::name('message')->where('isopen = ?','0')->count());
	}
}


