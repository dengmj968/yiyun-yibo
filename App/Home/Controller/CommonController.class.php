<?php
namespace Home\Controller;
use Common\Controller\BaseController;
/**
 * 加载用户相关数据，以及用户权限控制等
 * @author 宋小平
 * create time 2014-04-11
 */
class CommonController extends BaseController{
	protected $userInfo = array();
	
	public function init(){
		if( method_exists($this,'isLogin') ){
			$this->isLogin();
		}
	}

    /**
     * 重写base方法的初始化方法，添加权限过滤(non-PHPdoc)
     * @see BaseAction::_initialize()
     */
    function isLogin(){
		if(!$_SESSION['userInfo']['id']){
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			die;
		}else{
		    $this->userInfo = $_SESSION['userInfo'];
			$this->assign('userInfo',$_SESSION['userInfo']);
		}
    }
    
    /**
     * 获取用户信息，为空的跳转到登陆页面
     * @return array
     */
    protected function getUserInfo(){
    	if($_SESSION["userInfo"] && $_SESSION['userInfo']['id']){
    		return  $_SESSION["userInfo"];
    	}else{
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			die;
    	}
    }
    

 
}