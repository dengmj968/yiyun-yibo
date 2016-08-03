<?php
namespace Login\Controller;
use Think\Controller;
class IndexController extends Controller{
	/**
	 * 后台管理员账号map表
	 * @var array
	 * 103 老白
	 * 201 琳琳
	 * 404 小平
	 * 78 老鹰
	 * 6830 邓明倦
	 * 3187 秋慧
	 * 9254 梁然然
     * 7472 LQH-CC测试
	 */
	private  $admin_array = array(103,201,404,78,6830,3187,9254,7472);
	protected $deploy;
	public function _initialize(){
		$deployList = D('Deploy')->getDataList();
		foreach($deployList as $key=>$val){
			$deploy[$val['key']] = $val['value'];
		}
		$this->deploy = $deploy;
		$this->assign('deploy',$this->deploy);
	}
	
	public function adminLogin(){
		$this->display();
	}
	
	public function checkAdminLogin(){
		if(!$_SESSION['userInfo']){
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			die;
		}else{
			if(!in_array($_SESSION['userInfo']['id'],$this->admin_array)){
				$this->error('您无权访问！');
			}
			if($_POST['user_name'] == $_SESSION['userInfo']['email'] && md5($_POST['password']) == $_SESSION['userInfo']['password']){
				$_SESSION['adminUserInfo'] = $_SESSION['userInfo'];
				$this->assign('adminUserInfo',$_SESSION['adminUserInfo']);
				$this->redirect('/Admin');
			}else{
				$this->error('用户名或者密码错误');
			}
		}
	}
	
	public function quitAdminLogin(){
		unset($_SESSION['adminUserInfo']);
		$this->redirect('/Admin');
	}
}