<?php
namespace Admin\Controller;
/**
 * 地区配置
 * @author 宋小平
 *
 */
class AreaController extends CommonController
{
    public function setClassName(){
        $this->className = 'Area';
    }/**
	 * 地区列表
	 */
	public function index(){
		$_GET['pid'] = intval($_GET['pid']);
        $map['pid'] = $_GET['pid'];
		$area = D('Area')->getAreaList($_GET['pid']);
		$this->assign('area', $area);
		 
		if ( $_GET['pid'] == 0 ) {
			$this->assign('back_id', '-1');
		}else {
			$back_id = D('Area')->where("area_id={$_GET['pid']}")->getField('pid');
			$this->assign('back_id', $back_id);
		}
		 
		$this->assign('pid', $_GET['pid']);
		$this->display();
	}
	
	/**
	 * 修改地区
	 */
	public function editArea() {
		$area_id = intval($_GET['area_id']);
		$area = D('Area')->getAreaById($area_id);
		$this->assign('area', $area);
		$this->display();
	}
	
	/**
	 * 保存修改地区
	 */
	public function doEditArea() {
		$data['title'] = $_POST['title'];
		$map['area_id']	= intval($_POST['area_id']);
		if (empty($data['title'])) {
			echo 0;
			return ;
		}
		$id = D('Area')->saveArea($data,$map);
		echo $id ? '1' : '0';
		//$_LOG['uid'] = $this->mid;
		//$_LOG['type'] = '3';
		//$data[] = '扩展 - 工具 - 地区管理';
		//$data[] =  M('area')->where( array( 'area_id'=>intval($_POST['area_id']) ) )->find();
		//$data[] = $_POST;
		//$data['2']['pid'] = $data['1']['pid'];
		//$_LOG['data'] = serialize($data);
	//	$_LOG['ctime'] = time();
		//M('AdminLog')->add($_LOG);
	
		//F('Cache_PostArea',null);
	
		//echo M('area')->where('`area_id`='.$_POST['area_id'])->setField('title', $_POST['title']) ? '1' : '0';
	}
	
	/**
	 * 添加地区
	 */
	public function addArea() {
		$this->assign('pid', intval($_GET['pid']));
		$this->display('editArea');
	}
	
	/**
	 * 保存新添加地区
	 */
	public function doAddArea() {
		$data['title'] = $_POST['title'];
		$data['pid'] = intval($_POST['pid']);
		if (empty($_POST['title'])) {
			echo 0;
			return ;
		}
		$id = D('Area')->saveAddArea($data);
/* 		$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '1';
		$data[] = '扩展 - 工具 - 地区管理';
		$data[] = $_POST;
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);
	
		F('Cache_PostArea',null); */
		echo $id ? $id : '0';
	}
	
	/**
	 * 删除地区
	 */
	public function doDeleteArea() {
		$ids = explode(',',trim($_POST['ids'],','));
		if (empty($ids)) {
			echo 0;
			return ;
		}
		$map['area_id']	= array('in',$ids);
		$id = D('Area')->delAreas($map);
		//$_LOG['uid'] = $this->mid;
		//$_LOG['type'] = '2';
		//$data[] = '扩展 - 工具 - 地区管理';
		//$data[] =  M('area')->where( $map )->findall();
		//$_LOG['data'] = serialize($data);
		//$_LOG['ctime'] = time();
		//M('AdminLog')->add($_LOG);
	
		//F('Cache_PostArea',null);
	
		echo $id ? '1' : '0';
	}
	
	/**
	 * 获取下级地区ajax方法
	 */
	public function getChildArea(){
		$pId = $_POST['pId'];
		$childAreaList = D('Area')->getAreaList($pId);
		$childAreaList = json_encode($childAreaList);
		echo $childAreaList;
	}
}