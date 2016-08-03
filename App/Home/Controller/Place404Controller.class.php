<?php
namespace Home\Controller;
use Common\ORG\Page;
/**
 * @desc 404广告位
 * @author songweiqing
 */
class Place404Controller extends CommonController{
	protected $host='';//益播域名
	protected $table1;//404广告位正式表
	protected $table2;//广告位临时表、
	
	public function __construct(){
		parent::__construct();
		if(method_exists($this, 'getDeploy') ){
			$this->getDeploy();
		}
	}
	
	/**
	 * @desc首页
	 */
	public function index(){
		$uid = $this->getUid();
		$p = $_GET['p'] ? intval($_GET['p']) : 1;
		import("Common.ORG.Page");
		//正式表和流水表联合查询，过滤临时表已经冗余的数据
		$sql404 = "select id,web_name,list_id as pid,create_time from {$this->table1} where uid = {$uid} union all select id,web_name,id =0 as list_id,create_time from {$this->table2} where uid = {$uid} and id not in(select list_id from place404 where uid =".$uid.")";
		$counts = M()->query($sql404);
		$count = count($counts);
		$Page = new Page($count,5);
		$show404 = $Page->show();
		$sql = "(select id,web_name,list_id as pid,create_time,status from {$this->table1} where uid = {$uid}) union all (select id,web_name,id =0 as list_id,create_time,status from {$this->table2} where uid = {$uid} and id not in(select list_id from place404 where uid =".$uid.")) order by create_time desc limit ".$Page->firstRow.','.$Page->listRows;
		$place404List = M()->query($sql);
		$this->assign('show404',$show404);
		$this->assign('p',$p);
		$this ->assign('place404List',$place404List);
		$this->display();

	}
	/**
	 * @desc 404添加页面
	 */
	public function add(){
		$map['status'] = 1;
		$pushPro = D('Project')->where('is_push = 1 and status = 1')->find();
        //过滤主推
        if($pushPro['start_date'] !=0 && $pushPro['start_date'] > time()){
            unset($pushPro);
        }

        if($pushPro['end_date'] !=0 && $pushPro['end_date'] < time()){
            unset($pushPro);
        }
		$projectId = $pushPro ? $pushPro['id'] :1;
		$projectList = D('Project')->getDataList($map);
        foreach($projectList as $key=>$prot){
            //开始日期不为0且开始时间大于当前时间，过滤
            if($prot['start_date'] != 0 && $prot['start_date'] > time()){
                unset($projectList[$key]);
            }
            //结束时间不为0且结束时间小于当前时间，过滤,
            if($prot['end_date'] != 0 && $prot['end_date'] < time()){
                unset($projectList[$key]);
            }
        }
		$web_name = '404公益'; 
		//根据专题Id 调取专题信息
		$projectInfo = D('Project')->getDataById($projectId);
		$projectInfo['desc'] = msubstr($projectInfo['desc'],0,20);
		//调取专题的详情信息ProjectDetail
		$projectDetailInfo = D("Memcache")->getProjectDetailInfo($projectId);
		$tpl = '';
		foreach($projectDetailInfo['info'] as $key => $val){
			if($projectDetailInfo['info'][$key] == ''){
				unset($projectDetailInfo['info'][$key]);
				continue;
			}
			if(mb_strlen($projectDetailInfo['info'][$key],'utf-8') >= 30){
				$projectDetailInfo['info'][$key] = msubstr($projectDetailInfo['info'][$key],0,30);
			}
		}
		//对各个专题配置对应尺寸的广告位(可写入配置文件)
		$size_id = D($this->className)->getAdSizeByTemp($projectInfo['template_id']); 
		$maps['size_id'] = intval($size_id);
        $maps['status']  = 2;
		//加载随机广告
		$adInfos = D('Ad')->getDataList($maps,'',"rand()",1);
		$adInfo = $adInfos[0];
		//加载域名等
		$comeUrl = $_SERVER["HTTP_REFERER"];
		$parseUrl = parse_url($comeUrl);
		$reindex  = $parseUrl['host'];
		$projectDetailInfo['url'] = base64_encode($projectDetailInfo['url']);
		$adInfo['url'] = base64_encode($adInfo['url']);
                
                //领取兑换券提示
                $uid = $_SESSION['userInfo']['id'];
                $show_notice = 0;
                $codeTotal = M("Webcodo")->where('uid=0')->count();
                $info = array();
                $info = M("Webcodo")->where("uid=".$uid)->find();

                if ($codeTotal && empty($info)){
                    $_SESSION['is_notice'] = 1;
                    $show_notice = 1;
                }
                $this->assign('codeTotal',$codeTotal);
                $this->assign('show_notice',$show_notice);
		
		//默认广告位
		$placeInfo['id'] = 0;
		$this->assign('comeUrl',base64_encode($comeUrl));
		$this->assign('reindex',$reindex);
		$this->assign('placeInfo',$placeInfo);
		
		$this->assign('adInfo',$adInfo);
		$this->assign('projectList',$projectList);
		$this->assign('projectDetailInfo',$projectDetailInfo);
		$this->assign('projectInfo',$projectInfo);
		//$this->assign('default_confs',$default_confs);
		$this->assign('web_name',$web_name);
		$this->display();
	}
	/**
	 * @desc ajax404同步编辑
	 */
	public function updateConfs(){
		$projectId = intval($_POST['projectId']);
		$projectInfo = D('Project')->getDataById($projectId);
		$projectInfo['desc'] = msubstr($projectInfo['desc'],0,20);
		$projectDetailInfo = D("Memcache")->getProjectDetailInfo($projectId);
		$projectDetailInfo['info']['详情描述']=msubstr($projectDetailInfo['info']['详情描述'],0,20);
		$tpl = '';
		foreach($projectDetailInfo['info'] as $key => $val){
			if($projectDetailInfo['info'][$key] == ''){
				unset($projectDetailInfo['info'][$key]);
				continue;
			}
			if(mb_strlen($val,'utf-8') >= 30){
				$val = msubstr($val,0,30);
			}
			$tpl .= $key.'：'.$val.'<br/>';
		}
		$projectInfo['tpl'] = $tpl;
		$projectInfo['img'] = $projectDetailInfo['img'];
		$projectInfo['url'] = $projectDetailInfo['url'];
		if($projectId == 11){
			$projectInfo['ad_url'] = "http://www.gdga.gov.cn/jmlx/qmxz/201404/t20140422_705192.html";
			$projectInfo['ad_pic'] = "/Public/images/tjf_pic.png";
		}else{
			$maps['size_id'] = 13;
			$adInfo = D('Ad')->getDataList($maps,'',"rand()",1);
			$projectInfo['ad_url'] = $adInfo[0]['url'];
			$projectInfo['ad_pic'] = $adInfo[0]['pic'];
		}
		//dump($projectInfo);
		$this->ajaxReturn($projectInfo,1,1);
	}	
	
	/**
	 *  @desc 404编辑页面
	 */
	public function edit(){
		$obj = $this->getObject($_GET['pid']);
		$place404Info = $obj->getDataById($_GET['id']);
		$place404Info['mid'] = explode(",",$place404Info['mid']);
		$resIndex = array_rand($place404Info['mid']);
		$projectId = $place404Info['mid'][$resIndex] ? $place404Info['mid'][$resIndex] : 1; 
		$map['status'] = 1;
		$project = D('Project')->getDataList($map);
        foreach($project as $key=>$prot){
            //开始日期不为0且开始时间大于当前时间，过滤
            if($prot['start_date'] != 0 && $prot['start_date'] > time()){
                unset($project[$key]);
            }
            //结束时间不为0且结束时间小于当前时间，过滤,
            if($prot['end_date'] != 0 && $prot['end_date'] < time()){
                unset($project[$key]);
            }
        }
		$projectInfo = D('Project')->getDataById($projectId);
		$this->assign("projectId",$projectId);
		$this->assign('project',$project);
		$this->assign('projectInfo',$projectInfo);
		$this->assign('place404Info',$place404Info);
		$this->assign("default_code",D($this->className)->getDefaultCode($place404Info,$this->host) );

        //404新加入的爱心站记录
        $pmap['web_name'] = array('neq','');
        $pmap['web_host'] = array('neq','');
        $pmap['status']   = 2;
        $placeList = D('Place404')->where($pmap)->field('web_name,web_host')->order('id desc')->limit(6)->select();
        $this->assign('hostList',$placeList);
		$this->display();
	}
		
	/**
	 *  @desc删除404广告位
	 *  @param $pid int 0-临时表  1-正式表
	 *  @param $id int 广告位Id
	 */
	public function del(){
		$pid = intval($_POST['pid']);
		$id = intval($_POST['id']);
		if($pid == 0){
			$del_id = D('TempPlace404')->delById($id);
		}else{
			$del_id = D('Place404')->delById($id);
		}
		if($del_id){
			$this->ajaxReturn($del_id,1,1);
		}
	}
	
	/**
	 * @desc获取uid
	 */
	public function getUid(){
		$userInfo = $this->getUserInfo();
		return $userInfo['id'];
		//return 1;
	}	
	
	/**
	 * @desc 设置配置
	 */
	protected function getDeploy(){
		$this->host = D('Deploy')->getGlobal('YIBO_YU');
		$this->table1 = D($this->className)->trueTableName;
		$this->table2 = D('TempPlace404')->trueTableName;
	}
	
	/**
	 * @desc 调取对象
	 * @param int $id 0-临时表
	 * @return object
	 */
	public function getObject($id = 0){
		$id = intval($id);
		if($id == 0){
			return D('TempPlace404');
		}else{
			return D($this->className);
		}
	}
	
	/**
	 * @desc获取域名
	 */
	public function getWebHost(){
		$urls = parse_url($_SERVER['HTTP_REFERER']);
		return $urls['host'];
	}
	
	/**
	 * @desc获取代码
	 */
	public function getCodes(){
		$uid = $this->getUid();
		$id = intval($_POST['id']);
		$pid = intval($_POST['pid']);
		$configs = $this->getObject($pid)->getDataById($id);
		$codes = D($this->className)->getDefaultCode($configs,$this->host);
		$this->ajaxReturn($codes,1,1);
	} 
	
	/**
	 * @desc获取一条404广告
	 */
	public function getWebInfo(){
		$data = S('ad_404');
		$single = array();
		if(empty($data) ){
			$data = D('Web404')->getDataList();
			S('ad_404',$data,24*3600);
		}
		$randKey = array_rand($data,1);
		$single = $data[$randKey];
		$single["desc"] = mb_substr($single["desc"],0,80,"utf-8");
		return $single;
	
	}
	
	/**
	 * @desc 创建404广告位
	 */
	public function insertAdd(){
		$uid = $this->userInfo['id'];
		$_POST['uid'] = $uid;
		$insert_id = D('TempPlace404')->addData($_POST);
		$configs = D('TempPlace404')->getDataById($insert_id);
		if($configs){
			//$this->userAddScore();
			$res['codes'] = D($this->className)->getDefaultCode($configs,$this->host);
		}else{
			$default_confs = D($this->className)->getDefaultConfigs($uid);
			$res['codes'] = D($this->className)->getDefaultCode($default_confs,$this->host);
		}
		
        //获取分享码
        $res['codenum'] = 0;
        $uid = $_SESSION['userInfo']['id'];
        if ($uid){
            $info = M("Webcodo")->where("uid=".$uid)->find();
            if (!$info){
                $row = M("Webcodo")->where("uid=0")->find();
                if ($row){
                    $datas['uid'] = $uid;
                    $datas['update_time'] = date("Y-m-d H:i:s");
                    M("Webcodo")->where("id=".$row['id'])->save($datas);
                    $res['codenum'] = $row['code'];
                    $count = D("place404")->where('status=2')->count();
                    $res['webcount'] = $count+1;
                }
            }
        }
		$this->ajaxReturn($res,1,1);

	}
	
	/**
	 * @desc编辑404广告位
	 */
	function editConfs(){
		$pid = intval($_POST['pid']);
		$projectId = $_POST['mid'] ? $_POST['mid']:1;
		$projectInfo = D('Project')->getDataById($projectId);
		$obj = $this->getObject($pid);
		$flag = $obj ->saveDataById($_POST);
		$sql = $obj->getLastsql();
		$codes =  D($this->className)->getDefaultCode( $_POST,$this->host);
		$this->ajaxReturn($sql,1,1);
	}
}