<?php 
namespace Admin\Controller;
use Common\ORG\Page;
	class MessageConfigController extends CommonController{
		
		/**
		 * 系统发送消息配置首页
		 */
		public function index(){	
			if($_POST['id']){
				$map[D($this->className)->trueTableName.'.id']=intval($_POST['id']);
			}
			if($_POST['method_id']){
				$map[D($this->className)->trueTableName.'.method_id']=intval($_POST['method_id']);
			}
			if($_POST['title']){
				$map[D($this->className)->trueTableName.'.title']=array('like','%'.$_POST['title'].'%');
			}
            
			$count=D($this->className)->getCount($map);
			import("Common.ORG.Page");
			$Page=new Page($count,10);
			$show=$Page->show();
			$messageConfList=D($this->className)->getMessageConfigList($map,'',D($this->className)->trueTableName.'.update_time desc',"{$Page->firstRow},{$Page->listRows}");
			$this->assign('show',$show);
			$this->assign('messageConfList',$messageConfList);
            $methodList = D("Method")->getDataList();
            $this->assign('method',$methodList);            
			$this->display();
		}		
		
		/**
		 * 删除 
		 */		
		public function delByIdAjax(){
			$id=intval($_GET['id']);
			$id=D($this->className)->delById($id);
			if($id){
				$this->ajaxReturn($id,1,1);
			}
		}
		
		/**
		 * 添加页面
		 */
		public function addMessageConfig(){
		    $methodList = D("Method")->getDataList();
            $this->assign('method',$methodList);
			$this->display();
		}
		
		/**
		 * 添加信息 
		 */
		public function insertMessageConfig(){
			$id=D($this->className)->addMessageConfig($_POST);
			if($id){
				$this->success("添加成功","Admin/MessageConfig/index");
			}else{
				$this->error(D($this->className)->getLastError());
			}
		}
        
        /**
		 * 编辑页面 
		 */
		public function editMessageConfig(){
			$id=intval($_GET['id']);
            
			$messageConfInfo=D($this->className)->getDataById($id);
			$this->assign('data',$messageConfInfo);
            $methodList = D("Method")->getDataList();
            $this->assign('method',$methodList);
			$this->display();	
		}
		
		/**
		 * 更新信息
		 */
		public function saveMessageConfig(){
			$id=D($this->className)->saveMessageConfig($_POST);
			if($id){
				$this->success("更新成功","Admin/MessageConfig/index");
			}else{
				$this->error(D($this->className)->getLastError());
			}
		}		
		
		/**
		 *  系统提交信息
		 * 	@param $id  int 登陆用户的id
		 * 	@param $action string 类名_方法
		 * 	@param $expireTime int 过期时间 default 5 hours
		 */
		function sendSysMessage($id,$action,$expireTime=18000){
			if(!is_numeric($expireTime)){
				return true;
			}
			$receiveMessage=array();
			$action=strtolower($action);	
			$messageConfList=D($this->className)->select();
			foreach($messageConfList as $_key => $_val){
				if($action==$_val['action']){
					$receiveMessage= $messageConfList[$_key];
					break;
				}
			}
			$time=time();
			$expireMessage=D('Message')->where("uid={$id} and expireTime > {$time}")->select();
			if(!is_null($expireMessage)){
				return true;
			}
			if(!is_null($receiveMessage)){
				$data['title']=$receiveMessage['title'];
				$data['content']=$receiveMessage['content'];
				$data['status']=1;
				$data['create_time']=time();
				$data['expireTime']=time()+intval($expireTime);
				$data['uid']=$id;
				$returnId=D('Message')->addMessage($data);
				if($returnId){
					$datas['sid']=-1;
					$datas['rid']=$id;
					$datas['readStatus']=0;
					$datas['sendName']='系统信息';
					$datas['mid']=$returnId;
					$datas['time']=time();
					D('UserMessage')->addUserMessage($datas);
				}
			}
			return true;
		}		 
	}
?>