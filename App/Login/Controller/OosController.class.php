<?php
namespace Login\Controller;
use Think\Controller;
class OosController extends Controller{
	public $deploy;
	public function _initialize(){
		$deployList = D('Deploy')->getDataList();

		foreach($deployList as $key=>$val){
			$deploy[$val['key']] = $val['value'];
		}
		$this->deploy = $deploy;
		$this->assign('deploy',$this->deploy);
		
		if($_SESSION['userInfo']){
			$this->assign('userInfo',$_SESSION['userInfo']);
		}
	}
	
	public function checkLoginKey(){
		$token = json_decode(base64_decode($_GET['token']),true);
		if( md5($token['userInfo']['id'].'_'.$this->deploy['USER_KEY']) != $token['key']){
			return false;
		}
		$_SESSION['userInfo'] = $token['userInfo'];
	}
	
	/**
	 * 退出登录
	 */
	public function quitLogin(){
		if($_SESSION['userInfo']){
			session_destroy();
			unset($_SESSION['userInfo']);
		}
		if($_SESSION['adminUserInfo']){
			session_destroy();
			unset($_SESSION['adminUserInfo']);
		}
		$this->redirect("/Home/Index");
	}
}