<?php
namespace Admin\Controller;
/**
 * @desc 合作伙伴分类Action
 **/
class CooperationConfigController extends CommonController
{
    /**
     * @desc 类名设置
     **/
	public function setClassName()
	{
		$this->className = 'CooperationConfig';
	}
    
    /**
     * @desc 合作伙伴分类列表页面加载
     **/
	public function index()
	{
		$data = D($this->className)->getDataList();
		$this->assign('data', $data);
		$this->display();
	}
    
    /**
     * @desc 添加合作伙伴分类页面加载
     **/
	public function add()
	{
		$this->display();
	}
    
    /**
     * @desc 添加合作伙伴分类
     **/
	public function saveAdd()
	{
		$id = D( $this->className )->addData($_POST);
		if($id){
			$this->success("添加成功","/Admin/CooperationConfig/index");
		}else{
			$this->error(D( $this->className )->getLastError());
		}
	}

    /**
     * @desc 修改合作伙伴分类页面加载
     **/
	public function edit()
	{
		$id = intval($_GET['id']);
		$data = D( $this->className )->getDataById($id);
		$this->assign('data',$data);
		$this->display();
	}
    
    /**
     * @desc 修改合作伙伴分类
     **/
	public function saveEdit()
	{
		$res = D( $this->className )->saveDataById($_POST);
		if($res){
			$this->success("修改成功","/Admin/CooperationConfig/index");
		}else{
			$this->error(D( $this->className )->getLastError());
		}
	}

}