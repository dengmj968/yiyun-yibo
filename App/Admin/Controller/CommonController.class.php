<?php
namespace Admin\Controller;
use Common\Controller\BaseController;
class CommonController extends BaseController{
	protected $userInfo = array();
	
	public function __construct(){
		parent::__construct();
		if( method_exists($this,'isLogin') ){
			$this->isLogin();
		}
	}
	
	/**
	 * 重写base方法的初始化方法，添加权限过滤(non-PHPdoc)
	 * @see BaseAction::_initialize()
	 */
	function isLogin(){
		if(!$_SESSION['userInfo']){
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			die;
		}else{
			if(!$_SESSION['adminUserInfo']){
				$this->redirect('/Login/Index/adminLogin');
			}else{
				$this->adminUserInfo = $_SESSION['adminUserInfo'];
				$this->assign('adminUserInfo',$_SESSION['adminUserInfo']);
			}
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