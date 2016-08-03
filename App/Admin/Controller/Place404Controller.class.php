<?php
namespace Admin\Controller; 
use Common\ORG\Page;
	class Place404Controller extends CommonController{
		const NUMS = 20;
		public function setClassName(){
			$this->className = 'Place404';
		}
		
		/**
		 * @desc 首页
		 */
		public function index(){
			if($_REQUEST['web_name']){
				$name = filter_str($_REQUEST['web_name']);
				$map['web_name'] = array('like','%'.$name.'%');
				$maps['web_name'] = $name;
			}
			if($_REQUEST['colors']){
				$map['colors'] = intval($_REQUEST['colors']);
				$maps['colors'] = intval($_REQUEST['colors']);
			}
			if(isset($_REQUEST['is_come'])){
				$map['is_come'] = intval($_REQUEST['is_come']);
				$maps['is_come'] = intval($_REQUEST['is_come']);
			}
			$p = $_REQUEST['p']?$_REQUEST['p']:1;
			$count = D($this->className)->getCount($map);
			import("Common.ORG.Page");
			$Page = new Page($count,self::NUMS);
			foreach($maps as $key=>$val) {
				$Page->parameter   .=   "$key=".urlencode($val).'&';
			}
			$show = $Page->show();
			$place404List = D($this->className)->getDataList($map,'','',$Page->firstRow . ',' . $Page->listRows);
			$this->assign('show',$show);
			$this->assign('p',$p);
			$this->assign('place404List',$place404List);
			$this->assign('maps',$maps);
			$this->display();
		}
		
		/**
		 * @desc 删除
		 */
		public function del(){
			$id = intval($_GET['id']);
			$del_id = D($this->className)->delById($id);
			if($del_id){
				echo $del_id;
			}
		}
		
		/**
		 * @desc 编辑页面
		 */
		public function edit(){
			$id = intval($_GET['id']);
			$place404Info = D($this->className)->getDataById($id);
			$place404Info['mid'] = is_null($place404Info['mid'])?array():explode(",",$place404Info['mid']);
			$projectList = D('Project')->getDataList();
			$this->assign('projectList',$projectList);
			$this->assign('place404Info',$place404Info);
			$this->display();
		}
		
		/**
		 * @desc 保存编辑
		 */
		public function saveEdit(){
			if(isset($_POST['mid']) && $_POST['mid'] != '' ){
				$_POST['mid'] = implode(",",$_POST['mid']);				
			}else{
				$_POST['mid'] = '';
			}
			$p = $_POST['p'] ? intval($_POST['p']) : 1;
			$id = D($this->className)->saveDataByID($_POST);
			if($id){
				$this->success('修改成功！','/Admin/Place404/index/p/'.$p);
			}else{
				$this->error( D($this->className)->getLastError() );
			} 
		}
		
		/**
		 * @desc 修改状态
		 */
		public function setStatus(){
			$map['id'] = intval($_POST['id']);
			$status = intval($_POST['status']);
			if($status == 2){
				$map['status'] = 3;
				$map['info'] = '禁用';
			}else{
				$map['status'] = 2;
				$map['info'] = '启用';
			}
			$update_id = D($this->className)->saveDataById($map);
			if($update_id){
				$this->ajaxReturn($map,'修改成功',1);
			}
		}
		
		/**
		 * @desc 展示专题实例
		 */
		
		public function show(){
			
			
			$this->display();
		}
		

		public function changeFieldData(){
			$field = $_GET['field'];
			$id = intval($_GET['id']);
			$data[$field] = $_GET['is_come'];
			D($this->className)->where("id = {$id}")->save($data);
			echo $_GET['is_come'];
		}
	}
	