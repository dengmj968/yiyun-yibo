<?php
namespace Admin\Controller;
/**
 * @desc    后台全局配置
 * @author  宋小平
 *
 */
class DeployController extends CommonController{
    
    /**
     * @desc 类名
     */
	public function setClassName(){
		$this->className = "Deploy";
	}
	
	/**
	 * @desc   获取全局配置列表
	 */
	public function index(){
		$deployList = M('deploy')->select();
		$this->assign('deployList',$deployList);
		$this->display();
	}
	
	/**
	 * @desc   保存配置修改
	 */
	public function saveDeploy(){
		$data = $_POST;
		foreach($data as $key => $val){
			$da['value'] = $val;
			M('deploy')->where("`key` = '{$key}'")->data($da)->save();
		}
		$this->success('已保存！');
	}
	
}
