<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * @desc 合作伙伴Action_后台
 **/
class CooperationController extends CommonController
{
    /**
     * @desc 类名设置
     **/
	public function setClassName() {
		$this->className = 'Cooperation';
	}
    
    /**
     * @desc 合作伙伴列表页面加载
     **/
	public function index() {
	    // 引入分类类
		import("Common.ORG.Page");
        // 获取数据总条数
		$count = D($this->className)->getCount();
        // 设置每页显示条数
		$Page  = new Page($count, 10);
        // 分页样式
		$show  = $Page->show();
        // 每页显示数据
		$data  = D($this->className)->getDataList('', '', '', $Page->firstRow.','.$Page->listRows);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}

    /**
     * @desc 添加合作伙伴页面加载
     **/
	public function add() {
	    // 合作伙伴分类数据
		$list = D('CooperationConfig')->getDataList();
		$this->assign('list', $list);
		$this->display();
	}
    
    /**
     * @desc 添加合作伙伴
     **/
	public function saveAdd() {
		$_POST['create_time'] = time();
		$res                  = D('Cooperation')->addData($_POST);
		if ($res) {
			$this->success("添加成功", "/Admin/Cooperation/index");
		} else {
			$this->error(D($this->className)->getLastError());
		}
	}
    
    /**
     * @desc 修改合作伙伴页面加载
     **/
	public function edit() {
		$list = D('CooperationConfig')->getDataList();
		$this->assign('list', $list);
		$id   = intval($_GET['id']);
		$data = D($this->className)->getDataById($id);
		$this->assign('data', $data);
		$this->display();
	}
    
    /**
     * @desc 修改合作伙伴
     **/
	public function saveEdit() {
		$res = D($this->className)->saveDataById($_POST);
		if ($res) {
			$this->success("修改成功", "/Admin/Cooperation/index");
		} else {
			$this->error(D($this->className)->getLastError());
		}
	}
    
    /**
     * @desc Ajax请求修改合作伙伴状态
     **/
	public function changeStatus() {
		$data['id'] = intval($_GET['id']);
		$status     = intval($_GET['status']);
		if ($status == 1) {
			$data['status'] = 2;  //设置为 已停用
		} else {
			$data['status'] = 1;  //设置为 已使用
		}
		$id = D('Cooperation')->saveDataById($data);
		if ($id) {
			echo $data['status'];
		} else {
			echo false;  //状态设置失败
		}
	}

    /**
     * @desc 设置排序字段
     **/
	public function setOrder() {
		$data['id']    = intval($_POST['id']);
		$data['order'] = intval($_POST['order']);
		D('Cooperation')->saveDataById($data);
	}

}