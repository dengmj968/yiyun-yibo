<?php
namespace Admin\Controller; 
use Common\ORG\Page;
	class MessageController extends CommonController{
		public function setClassName(){
			$this->className = str_replace('Action','',__CLASS__);
		}
		
		/**
		 * 首页
		 */
		public function index(){
			if($_POST['title']){
				$map['title']=array('like','%'.$_POST['title'].'%');
			}
			if($_POST['id']){
				$map['id']=intval($_POST['id']);
			}
			import("Common.ORG.Page");
			$count=D($this->className)->getCount($map);
			$Page=new Page($count,10);
			$show=$Page->show();
			$messageList=D($this->className)->getDataList($map,'','create_time desc',"{$Page->firstRow},{$Page->listRows}");
			$this->assign('show',$show);
			$this->assign('messageList',$messageList);
			$this->display();
		}
		
		/**
		 * 信息添加
		 */
		public function add(){
			$this->display();
		}
		
		/**
		 *  保存信息
		 */
		public function saveMessage(){
			$count_old = D($this->className)->getCount();
			if($_POST['toemail']){
				$emails=explode(',',trim(str_replace(array('，','。'),',',$_POST['toemail']),','));
			}
				$values=trim($this->combineSql($_POST, $emails),',');;
				$sql="insert into message(sendName,title,content,create_time,status,uid,sid) values".$values;	
				$returnId=D($this->className)->query($sql); 
				$count_new = D($this->className)->getCount();
				if($count_new && $count_new > $count_old){
					$this->success("发送成功");
				}else{
					$this->error("发送失败");
				}
		}
		
		/**
		 * 组合成SQL,仅适用于该模块
		 * @param array $arr
		 */
		public function combineSql($mes,array $arr){
			if(!$arr) return false;
			$sendName=$_SESSION['admininfo']['email'];
			$sid=$_SESSION['admininfo']['uid']?$_SESSION['admininfo']['uid']:-1;
			$time=time();
			foreach($arr as $_key => $_val){
				$values.="('系统消息','".$mes['title']."','".$mes['content']."',".$time.",0,".$_val.",".$sid."),";	
			}
			return $values;
		}
		
		/**
		 *  删除信息
		 */
		public function delByIdAjax(){

			$id=intval($_GET['id']);
			$id=D($this->className)->delById($id);
			if($id){
				$this->ajaxReturn($id,'success',1);
			}
		
		}
		
		/**
		 * 编辑信息
		 */
		public function editMessage(){
			$id=intval($_GET['id']);
			$messageInfo=D($this->className)->getDataById($id);
			$this->assign('data',$messageInfo);
			$this->display();
		}
		
		/**
		 * 保存编辑
		 */
		public function saveEditMessage(){
			$id=D($this->className)->saveDataById($_POST);
			if($id){
				$this->success('修改成功');
			}else{
				$this->error("修改失败");
			}
		}
		
	}

