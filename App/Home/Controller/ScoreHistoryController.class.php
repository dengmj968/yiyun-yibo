<?php
namespace Home\Controller;
use Common\ORG\Page;
/**
	 * @desc 积分流水记录
     * @author  liuqiuhui
     * @creat_time 2014-11-19
	 */
	class ScoreHistoryController extends CommonController{

		/**
		 * @desc 首页
		 */
		public function index(){
			$uid = intval($this->userInfo['id']);
			import("Common.ORG.Page");
			$map['uid'] = $uid;
			$count = D($this->className)->getCount($map);
			$Page = new Page($count,10);
			$show = $Page->show();
			$scoreList = D($this->className)->getDataList($map,'','',$Page->firstRow . ',' . $Page->listRows);
			$this->assign('scoreList',$scoreList);
			$this->assign('show',$show);
			$this->display();
		}
		
		/**
		 * @desc 积分规则
		 */
		public function scoreRule(){
		
			$this->display();
		}

	}
