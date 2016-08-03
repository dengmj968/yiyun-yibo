<?php
namespace Admin\Controller;
use Common\ORG\Page;
    /**
 	 * @desc 关键字分类
     * @athor liuqiuhui
     * @create_date 2014-11-19
 	 */
	class KeywordsCategoryController extends CommonController{

		/**
		 * @desc 列表页
		 */
		public function index(){
		    // 引入分类页
			import("Common.ORG.Page");
            // 获取数据总条数
			$count = D($this->className)->getCount();
            // 设置每页显示条数
			$Page = new Page($count,10);
            // 分页样式
			$show = $Page->show();
            // 当前页显示数据
			$categoryList = D($this->className)->getDataList('','','',$Page->firstRow . ',' . $Page->listRows);
			$this->assign('categoryList',$categoryList);
			$this->assign('show',$show);
			$this->display();
		}
		
		/**
		 * @desc 添加页面
		 */
		public function add(){
			$this->display();
		}
		
		/**
		 * @desc 保存添加
		 */
		public function saveAdd(){
			$id = D($this->className)->addData($_POST);
			if($id){
				$this->success('添加关键字成功','Admin/KeywordsCategory/index');
			}else{
				$this->error( D($this->className)->getLastError() );
			}
		}
		
		/**
		 * @desc 编辑页
		 */
		public function edit(){
			$id = intval($_GET['id']);
			$categoryInfo = D($this->className)->getDataById($id);
			$this->assign('categoryInfo',$categoryInfo);
			$this->display();
		}
		
		/**
		 * @desc 保存编辑
		 */
		public function saveEdit(){
			$id = D($this->className)->saveDataById($_POST);
			if($id){
				$this->success('修改成功','Admin/KeywordsCategory/index');
			}else{
				$this->error( D($this->className)->getLastError() );
			}
		}
	}

