<?php
namespace Home\Controller;
use Common\ORG\Page;
use Common\Controller\BaseController;
	/**
	 * @desc 短消息发送
	 * @author songweiqing
	 */
	class MessagesController extends BaseController{
		protected $uid;
		public function __construct(){
			$this->uid = $_SESSION['userInfo']['id'];
			$this->className = 'Message';
		}
		
		/**
		 * @desc首页
		 */
		public function index(){
			//R('Admin/MessageConfig/sendSysMessage',array($this->uid,'Messages_index'));
			$map['uid']=$this->uid;
			if($_GET['readStatus'] && $_GET['readStatus']=='readed'){
				$map['status']=1;
			}else{
				$map['status']=0;
			}
			$counts=D($this->className)->getCount($map);
			import("Common.ORG.Page");
			$Page=new Page($counts,10);
			$show=$Page->show();
			$messageList=D($this->className)->getDataList($map,'','create_time desc',"{$Page->firstRow},{$Page->listRows}");
			$this->assign('show',$show);
			$this->assign('messageList',$messageList);
			$this->display();
		}
		
		/**
		 * @desc ajax删除
		 */
		public function delByAjax(){
			$id=intval($_POST['id']);
			$id=D($this->className)->delById($id);
			if($id){
				$this->ajaxReturn($id,1,1);
			}
		}
		
		/**
		 * @desc 查看信息 
		 */	
		public function showMessage(){
			R('Admin/MessageConfig/sendSysMessage',array($this->uid,'Messages_showMessage',30));
			$id=intval($_GET['id']);
			$map['id']=$id;
			$map['status']=1;
			$messageInfo=D('Message')->getDataById($id);
			if($messageInfo){
				D('Message')->saveDataById($map);
				$this->assign('messageInfo',$messageInfo);
				$this->display();
			}else{
				$this->error('该条信息可能已经缺失，请联系管理员');
			}
		}
	}

?>
