<?php
namespace Admin\Controller;
use Common\ORG\Page;
	/**
	 * @author songweiqing
	 * @desc 评论管理
	 */
	class CommentController extends CommonController{
		/**
		 * @desc 首页
		 */
		public function index(){
			if($_REQUEST['id'] && $_REQUEST['id'] != ''){
				$map['id'] = intval($_REQUEST['id']);
				$maps['id'] = intval($_REQUEST['id']);
			}
			if($_REQUEST['ad_id'] && $_REQUEST['ad_id'] != ''){
				$map['ad_id'] = intval($_REQUEST['ad_id']);
				$maps['ad_id'] = intval($_REQUEST['ad_id']);
			}
			if($_REQUEST['content'] && $_REQUEST['content'] != ''){
				$map['content'] = array('like','%'.$_REQUEST['content'].'%');
				$maps['content'] = htmlspecialchars($_REQUEST['content']);
			}
			import("Common.ORG.Page");
			$count = D('AdComment')->getCount($map);
			$Page = new Page($count,15);
			$show = $Page->show();
			foreach($maps as $key=>$val) {
				$Page->parameter   .=   "$key=".urlencode($val).'&';
			}
			$commentList = D('AdComment')->getDataList($map,'','',$Page->firstRow . ',' . $Page->listRows);
			$this->assign('commentList',$commentList);
			$this->assign('show',$show);
			$this->assign('maps',$maps);
			$this->display();
		}
		
		public function del(){
			$id = intval($_GET['id']);
			$del_id = D('AdComment')->delById($id);
			if($del_id){
				echo $del_id;
				exit;
			}
		}
	}
	