<?php
/**
 * 系统基础Action
 * 用于加载全局数据
 * （请勿修改，如需修改请告之创建者）
 * @author 宋小平
 * create time 2014-04-11
 */
namespace Common\Controller;
use Think\Controller;
abstract class BaseController extends Controller{
	
	/**
	 * class名
	 * @var string
	 */
	protected $className;
	
	/**
	 * 方法名
	 * @var string
	 */
	protected $actionName;
	
	/**
	 * 全站配置
	 * @var array
	 */
	protected $deploy = array();

	/**
	 * 验证码函数
	 */
	Public function verify(){
		import('ORG.Util.Image');
		Image::buildImageVerify();
	}
	
	public function __construct(){
		parent::__construct();

        //加载全站配置
		$deployList = D('Deploy')->getDataList();
		foreach($deployList as $key=>$val){
			$deploy[$val['key']] = $val['value'];
		}
		$this->deploy = $deploy;
		$this->assign('deploy',$this->deploy);
		//加载class名和方法名
		$this->className = CONTROLLER_NAME;
		$this->actionName = ACTION_NAME;
		
		
		//加载用户数据和判断是否登录
		if($_SESSION['userInfo']['id']){
			$this->assign('userInfo',$_SESSION['userInfo']);
		}
		if( method_exists($this,'init') ){
			$this->isLogin();
		}
	}
	
	function isLogin(){
		if(!$_SESSION['userInfo']['id']){
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			die;
		}else{
		$this->userInfo = $_SESSION['userInfo'];
		$this->assign('userInfo',$_SESSION['userInfo']);
		}
	}
	
	/**
	 * 得到当前展示页面的域名
	 */
	public function get_web_host(){
		$parseurl = parse_url($_SERVER['HTTP_REFERER']);
        return $parseurl['host'];
	}
	
	/**
	 * 单条数据删除的AJAX方法
	 */
	public function delByIdAjax(){
		$id = intval($_GET['id']);
		//if( !D( $this->className )->isHaveJurisdiction( $id,$_SESSION['userInfo']['id'],'user_id') ){
		//	echo 2;
		//	return;
		//};
		$result = D( $this->className )->delById($id);
		if($result){
			echo 1;
		}else{
			echo 0;
		}
	}
    
    /**
     * @desc 请求用户中心添加积分接口
     */ 
    public function userAddScore($key='',$data = array(),$uid=''){
        if($key){
	        $isTrue = D('ScoreHistoryKey')->isHave($key);
            if(!$isTrue) return false;
        }        
        if(empty($data)){
            $method = strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME);
            $data['method'] = $method;
            $list = D('ScoreDeploy')->getDataList($data);
            $param['uid']    = $uid?$uid:$_SESSION['userInfo']['id'];;
            $param['points'] = $list[0]['score'];
            $param['desc']   = $list[0]['desc'];
        }else{
            $param = $data;
        }
        
        $param['from'] = 2;
        $userUrl = $this->deploy['CENTER_SERVER'];
        $res = curl_post($userUrl.'/Api/ScoreApi/getScore',$param);
        /*if($url){
            header("Location:".$userUrl.'/Login/Index/distributeUserInfo/url/'.base64_encode($url));
        }else{
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            header("Location:".$userUrl.'/Login/Index/distributeUserInfo/url/'.base64_encode($url));
        }*/        
    }
    
    /**
     * @desc 请求用户中心发送站内信接口
     */
    public function userSendMessage($data = array(),$uid=''){
        if(empty($data)){
            $method = strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME);
            //$method = "home_index_addlikes";
            $data['method'] = $method;
            $list = D('MessageConfig')->getDataList($data);
            $param['uid']     = $uid?$uid:$_SESSION['userInfo']['id'];
            $param['title']   = $list[0]['title'];
            $param['content'] = $list[0]['content'];
        }else{
            $param = $data;
        }
        $param['key']     = $this->deploy['USER_KEY'];
        $param['from']    = 2;
        $userUrl = $this->deploy['CENTER_SERVER'];
        $result = curl_post($userUrl.'/Api/DataApi/sendMessage',$param);
        if($result == 3){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 文件下载
     */
     public function downloadFile($file){
        $file = ".".$file;
		if(file_exists($file)){
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
		}else{
			$this->error("文件不存在");		
		}
	}

    
}