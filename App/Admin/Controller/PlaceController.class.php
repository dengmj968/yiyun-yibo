<?php
namespace Admin\Controller; 
use Common\ORG\Page;
	/**
	 * @desc 普通广告位
	 * @author Songweiqing
	 */
	class PlaceController extends CommonController{
		const NUMS=10;
		public function setClassName(){
			$this -> className = 'Place';
		}
		
		/**
		 * @desc 首页
		 */
		public function index(){
			if($_REQUEST['id']){
				$map['id'] = intval($_REQUEST['id']);
				$maps['id'] = intval($_REQUEST['id']);
			}
			if($_REQUEST['name']){
				$name = filter_str($_REQUEST['name']);
				$map['name'] = array('like','%'.$name.'%');
				$maps['name'] = $_REQUEST['name'];
			}
			if($_REQUEST['size_id']){
				$map['size_id'] = intval($_REQUEST['size_id']);	
				$maps['size_id'] = $_REQUEST['size_id'];
			}
			if($_REQUEST['keywords']){
				$keywords = filter_str($_REQUEST['keywords']);
				$map['keywords'] = array('like','%'.$keywords.'%');
				$maps['keywords'] = $_REQUEST['keywords'];
			}
			if($_REQUEST['status']){
				$map['status'] = intval($_REQUEST['status']);
				$maps['status'] = $_REQUEST['status'];
			}
			$p = $_GET['p']?$_GET['p']:1;
			$sizes = $this->getSizes();
			$count = D($this->className)->getCount($map);
			import("Common.ORG.Page");
			$Page = new Page($count,self::NUMS);
			foreach($maps as $key=>$val) {
				$Page->parameter   .=   "$key=".urlencode($val).'&';
			}
			$show = $Page->show();
			$placeList = D($this->className)->getDataList($map,'','',$Page->firstRow . ',' . $Page->listRows);
			$this->assign('sizes',$sizes);
			$this->assign('p',$p);
			$this->assign('placeList',$placeList);
			$this->assign("show",$show);
			$this->assign('maps',$maps);
			$this->display();
		}
		
		/**
		 * @desc 编辑页面
		 */
		public function edit(){
			$id = intval($_GET['id']);
			$placeInfo = D($this->className)->getDataById($id);
			$sizes = $this->getSizes();
			$categoryList = $this->getCats();
			$pics = D('AdSizeConfig')->getDataById($placeInfo['size_id']);
			$placeInfo['pic'] = $pics['pic'];
			//dump($placeInfo);
			$this->assign('placeInfo',$placeInfo);
			$this->assign('sizes',$sizes);
			$this->assign('categoryList',$categoryList);
			$this->display();
		}
		
		/**
		 * @desc 保存
		 */
		public function saveEdit(){
			$id = D($this->className)->saveDataById($_POST);
			if($id){
				$this->success('添加成功！','/Admin/Place/index');
			}else{
				$this->error( D($this->className)->getLastError() );
			}
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
		 * @desc 获取尺寸
		 */
		public function getSizes(){
			$sizes = S("sizes");
			if(empty($sizes)){
				$sizes = D('AdSizeConfig')->getDataList('','','width asc','');
				S("sizes",$sizes,24*3600);
			}
			return $sizes;
		}
		
		/**
		 * 关键字
		 */
		public function getCats(){
			$categoryList = S("categoryList");
			if(empty($categoryList)){
				$categoryList = D('KeywordsCategory')->getDataList();
				S("categoryList",$categoryList,24*3600);
			}
			return $categoryList;
		}
		
		/**
		 * 获取图片
		 */
		public function getPic(){
			$size_id = $_GET['size_id']?intval($_GET['size_id']):1;
			$sizeInfo = D('AdSizeConfig')->getDataById($size_id);
			$this->ajaxReturn($sizeInfo,1,1);
		}
		
		/**
		 * @desc 修改状态
		 */
		public function changeStatus(){
			$status = intval($_POST['status']);
			$map['id'] = intval($_POST['id']);
			
			switch($status){
				case 1:
					$map['status'] = 2;
					$map['info'] = "使用中";
					break;
				case 2:
					$map['status'] = 3;
					$map['info'] = "停用中";
					break;
				default:
					$map['status'] = 2;
					$map['info'] = "使用中";
					break;
			}
			
			$id = D($this->className)->saveDataByID($map);
			if($id){
				$this->ajaxReturn($map,1,1);
			}
		}
		
	}
