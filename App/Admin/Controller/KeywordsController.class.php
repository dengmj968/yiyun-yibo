<?php
namespace Admin\Controller; 
use Common\ORG\Page;
    /**
     * @desc 关键字后台_Action
     * @athor liuqiuhui
     * @create_date 2014-11-19
     **/ 
	class KeywordsController extends CommonController{
	   
		/**
		 * @desc 关键字首页
		 */
		public function index(){
		    // 关键字类别列表
			$categoryList = $this->getCategoryList();
            // 搜索条件组合
			if($_POST['value'] && !empty($_POST['value'])){
				$map['value'] = array('like','%'.$_POST['value'].'%');
			}
			if($_POST['category_id'] && !empty($_POST['category_id'])){
				$map['category_id'] = $_POST['category_id'];
			}
            // 引入分页类
			import("Common.ORG.Page");
            // 获取数据总条数
			$count = D($this->className)->getCount($map);
            // 设置每页显示条数
			$Page = new Page($count,10);
            // 分页样式
			$show = $Page->show();
            // 获取当前页数据列表
			$keywordsList = D($this->className)->getDataList($map,'','',$Page->firstRow . ',' . $Page->listRows);
            // 按类别id获取关键字类别名称
			foreach($keywordsList as $key => $val){
				$categoryInfo = D('KeywordsCategory')->getDataById($val['category_id']);
				$keywordsList[$key]['categoryName'] = $categoryInfo['category_name'];
			}
			$this->assign('show',$show);
			$this->assign('categoryList',$categoryList);
			$this->assign('keywordsList',$keywordsList);
			$this->assign('category_id',$_POST['category_id']);
			$this->assign('value',$_POST['value']);
			$this->display();
		}
		
		/**
		 * @desc 添加
		 */
		public function add(){
			$categoryList = $this->getCategoryList();
			$this->assign('categoryList',$categoryList);
			$this->display();
		}
		
		/**
		 * @desc 保存添加
		 */
		public function saveAdd(){
			$id = D($this->className)->addData($_POST);
			if($id){
				$this->success('添加成功','__URL__/index');
			}else{
				$this->error(D($this->className)->getLastError());
			}
		}
		
		/**
		 * @desc 编辑
		 */
		public function edit(){
			$id = intval($_GET['id']);
			$keywordsInfo = D($this->className)->getDataById($id);
			$categoryList = $this->getCategoryList();
			$this->assign('categoryList',$categoryList);
			$this->assign('keywordsInfo',$keywordsInfo);
			$this->display();
		}
		
		/**
		 * @desc 保存编辑
		 */
		public function saveEdit(){
			$id = D($this->className)->saveDataById($_POST);
			if($id){
				$this->success('修改成功','index');
			}else{
				$this->error(D($this->className)->getLastError());
			}
		}
		
		/**
		 * @desc 删除
		 */
		public function delByIdAjax(){
			$id = intval($_GET['id']);
			$returnId = D($this->className)->delById($id);
			if($returnId){
				$this->ajaxReturn($returnId,1,1);
			}
		}
		
		/**
		 * @desc 获取分类列表
		 */
		public function getCategoryList(){
			return D('KeywordsCategory')->getDataList();
		}
	}