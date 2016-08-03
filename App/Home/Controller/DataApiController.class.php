<?php
namespace Home\Controller;
use Think\Controller;
/**
 * 益播数据交互接口
 * @author 邓明倦
* create Time 2014-12-01
 */
class DataApiController extends Controller{
	/**
	 * 全局配置变量
	 * @var array
	 */
	private $deploy = array();
    
	private $userKey;
	
	
	/**
	 * 初始化方法，加载全局配置
	 */
	protected function _initialize(){
		//加载全局配置表
		$deployList = M('deploy')->select();
		foreach($deployList as $key=>$val){
			$data[$val['key']] = $val['value'];
		}
		$this->deploy = $data;
	}
	
	/**
	 * 校验子站点的KEY
	 */
	private function checkKey(){
		if($this->userKey != $this->deploy['USER_KEY']){
			return false;
		}else{
			return true;
		}
	}
	
	
	
	/**
	 * 获取益播积分配置
	 */
	public function getScoreDeploy(){
		$this->userKey = $_POST['key'];
		if(0 && !$this->checkKey() ){
			echo 1;//秘钥错误
		}else{
            //积分配置
			$ruleList = D('ScoreDeploy')->getDataList('`status`=1', '`score`,`desc`','id');
			if($ruleList){
				echo json_encode($ruleList);
			}else{
				echo 2;//没有相应数据
			}
		}
	}
}