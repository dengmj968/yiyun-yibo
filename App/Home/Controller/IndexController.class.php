<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Common\ORG\Page;
class IndexController extends BaseController{
	public $default_pic = '/Public/images/default.jpg';
    /**
     * 首页
     */
    function index(){
    	$map['size_id'] = 10;
    	$map['status'] = 2;
    	$map['id'] = array('in',$this->deploy['HOME_AD']);
    	$adList = D(Ad)->getDataList($map,'',"rand()",4);
		$place404Sums = D('Place404')->getCount();
		$placeSums = D('Place')->getCount();
		$place404Sums = $place404Sums + $placeSums;
		$adPlace = 220000 + $placeSums;
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('index_focus', 1);
        $this->assign('banner', $banner);
		
		$activityInfo = $this->getActivityInfo();
		$activityInfo['desc'] = '<a style="color:#fff;" href="'.$activityInfo['url'].'">'.mb_substr($activityInfo['desc'],0,20,'utf-8').'...</a>'; 
    	$this->assign('activityInfo', $activityInfo);
		$this->assign('place404Sums',$place404Sums);
		$this->assign('adPlace',$adPlace);
		$this->assign('adList',$adList);
        $this->display();
    }

    public function window(){
    	$searchs['title'] = $_POST['title'] ? htmlspecialchars($_POST['title']) : '';
    	$searchs['keywordIds']	= $_POST['keywordIds'] ? $_POST['keywordIds'] : '';	
    	$keywordsCategoryList = D('KeywordsCategory')->getDataList();
    	$sizeTypeList = D('AdSizeConfig')->getDataList('','',"`width` asc");
		$size_id = 10;
		if(isset($_POST['size_id'])){
			$size_id = intval($_POST['size_id']);
		}
		//$size_id = intval($_POST['size_id']) ? intval($_POST['size_id']) : 10;
		$searchs['size_id'] = $size_id;
    	$defaultSizeInfo = D('AdSizeConfig')->getDataById($size_id);
		$this->assign('defaultSizeInfo',$defaultSizeInfo);
		$this->assign('sizeTypeList',$sizeTypeList);
		$this->assign('keywordsCategoryList',$keywordsCategoryList);
		$this->assign('searchs',$searchs);
		$this->display();
    }
    
    public function idea(){
        $this->display();
    }

    public function addIdea(){
        $data['email'] = htmlspecialchars($_POST['email']);
        $data['desc'] = htmlspecialchars($_POST['desc']);
        if (!$data['email'] || !$data['desc']) {
            $this->error('填写内容不能为空！');
        }
        $id = M('idea')->add($data);
        if ($id) {
            echo "提交成功！感觉您对益云公益的支持！";
        }
    }

    public function blog(){
		if(!$_SESSION['userInfo']['id']){
			echo "<script language='javascript' type='text/javascript'>";
			echo "window.location.href='{$this->deploy['CENTER_SERVER']}/Login'";
			echo "</script>";
			exit;
		}
		$host = $this->deploy['YIBO_YU'];
    	//生成博客广告代码
		$data['uid'] = $_SESSION['userInfo']['id'];
		$data['size_id'] = 10;
		$data['status'] = 1;
		$data['placeType'] = 2;
		$data['create_time'] = date('Y-m-d',time());
		
		//检测主表存在该条数据
		$map['uid'] = $_SESSION['userInfo']['id'];
		$map['size_id'] = 10;
		$map['placeType'] = 2;
		
		$formalres = D ( 'Place' )->getSingleData ( $map );
		if($formalres){
			$codes = D('Place')->createBlogCodes($formalres['pid'],$host);
		}else{
			$tempres = D('TempPlace')->getSingleData($map);
			if($tempres){
				$codes = D('Place')->createBlogCodes($tempres['id'],$host);
			}else{
				$insert_id = D('TempPlace')->addData($data);
				$codes = D('Place')->createBlogCodes($insert_id,$host);
			}
		
		}
		
        $webData = D("WebTitle")->getWebData(8);
        $this->assign("webData", $webData);
		$this->assign('codes',$codes);
        $this->display();
    }
    
    /**
     * 常见问题问答展示
     */
    public function faqs(){
        $faqsType = D("FaqsType")->getDataList('', '', 'sort_no');
        $list = null;
        foreach($faqsType as $val){
            $where['tid'] = $val['id'];
            $faqs = D("Faqs")->getDataList($where, '', 'id');
            if(!empty($faqs)){
                $list[$val['name']] = $faqs;
            }
        }
        $this->assign("list", $list);
    	$this->display();
    }

    /*
     * 获取要在首页展示的活动信息
     * 默认选择is_show状态为1的活动，如果没有，选择最后添加的活动
     */
    public function getActivityInfo(){
        $map['is_show'] = 1;
        $activityInfo = D("Activity")->getDataByMap($map);

        if(!$activityInfo){
            $activity = D("Activity")->getDataList('', '', 'id desc', '1');
            $activityInfo = $activity[0];
        }
        return $activityInfo;
    }
    
    public function getAdList(){
    	//$_GET['keywordIds'] = 4;
        $sizeId     = intval($_GET['size_id']);
        $page       = intval($_GET['page']);
        $title      = htmlspecialchars($_GET['title']);
        $keywordIds = intval($_GET['keywordIds']);
    	$where = "where status = 2 and (type = 1 or type = 5)";
		if(isset($sizeId) && $sizeId != ''){
			$where .= " and size_id = ".$sizeId;
		} 
		if(isset($title) && $title != ''){
			$where .= " and title like '%".$title."%'";
		}
		//查询关键字
		if(isset($keywordIds) && $keywordIds != ''){
			$keycat['category_id'] = $keywordIds;
			
			$keywords = D('Keywords')->getDataList($keycat);
			$where .= " and ( ";
			foreach($keywords as $key1 => $val1){
				$where .= " find_in_set({$val1['id']},`keyword_ids`) or ";	
			}
			$where .= " find_in_set({$keywordIds},`keyword_ids`) ";
			$where .= " ) ";
			//$where .= " and find_in_set(".$_GET['keywordIds'].",`keyword_ids`) ";
		}
    	$page = $page?$page:1;
    	$first = ($page-1)*15;
    	$where .= " limit ".$first.",15";
    	$sql = "select * from ad ".$where;
    	$adList = M()->query($sql);
   		foreach($adList as $key => $val){
			$map['ad_id'] = $val['id'];
			$count1 = D('GetStatistics')->getAdMonthStatistics($map,'show'); 
			$sums1 = $count1['sum'] ? $count1['sum'] : 0;
			//$sums2 = mt_rand(10000,50000);
			$maps['create_time'] = array('GT',date('Y-m-01'));
			$maps['ad_id'] = $val['id'];
			$count2 = D('GetStatistics')->getAdDayStatistics($maps,'show');
			$sums2 = $count2['sum'] ? $count2['sum'] : 0;
			//$sums2 = mt_rand(500,20000);
			$sums = $sums1 + $sums2;
			$adList[$key]['counts'] = $sums;
			$adList[$key]['sums1'] = $sums1;
			$adList[$key]['sums2'] = $sums2;
			$adList[$key]['sql'] = $sql;
			$sizes = M('ad_size_config')->find($val['size_id']);
   			$adList[$key]['width'] = $sizes['width'];
   			$adList[$key]['height'] = $sizes['height'];
   		}
		echo json_encode($adList);
		exit;
    }
    
    public function top5(){
		$map['create_time'] = date('Y-m-d',mktime('0','0','0',date('m'),'0',date('Y')));
    	//$map['create_time'] = date('Y').'-'.(date('m')-1).'-00';
		//dump($map);
		
		//$before['create_time'] =  date('Y').'-'.(date('m')-2).'-00';
		$before['create_time'] = date('Y-m-d',mktime('0','0','0',(date('m')-1),'0',date('Y')));
		//dump($before);
		//exit;
		$beforeList = explode("-",$before['create_time']);
    	$dateList = explode('-',$map['create_time']);
    	$showList = D('GetStatistics')->getAdMonthStatisticsList($map,'show','`ad_id`,`sum`',"`sum` desc",'5');
		$beforeShowList = D('GetStatistics')->getAdMonthStatisticsList($before,'show','`ad_id`,`sum`',"`sum` desc",'5');
	
    	foreach($showList as $key=>$val){
			if(!$val['ad_id']){
				unset($showList[$key]);
				continue;
			}
    		$adInfo = D('Ad')->getDataById($val['ad_id'],'`title`');
			if($showList[$key]['sum'] > $beforeShowList[$key]['sum']){
				$showList[$key]['flags'] = 'up';
				$showList[$key]['ratio'] = $showList[$key]['sum'] - $beforeShowList[$key]['sum'];
			}else{
				$showList[$key]['flags'] = 'below';
				$showList[$key]['ratio'] = $beforeShowList[$key]['sum'] - $showList[$key]['sum'];
			}
			
			$showList[$key]['title'] = $adInfo['title'] ? $adInfo['title'] : '益播公益广告';
    	}
		
		
    	$clickList = D('GetStatistics')->getAdMonthStatisticsList($map,'click','`ad_id`,`sum`',"`sum` desc",'6');
		$beforeClickList = D('GetStatistics')->getAdMonthStatisticsList($before,'click','`ad_id`,`sum`',"`sum` desc",'6');
		unset($adInfo);

    	foreach($clickList as $key=>$val){
			if(!$val['ad_id']){
				unset($clickList[$key]);
				continue;
			}
    		$adInfo = D('Ad')->getDataById($val['ad_id'],'`title`');
			if($clickList[$key]['sum'] > $beforeClickList[$key]['sum']){
				$clickList[$key]['flags'] = 'up';
				$clickList[$key]['ratio'] = $clickList[$key]['sum'] - $beforeClickList[$key]['sum'];
			}else{
				$clickList[$key]['flags'] = 'below';
				$clickList[$key]['ratio'] =  $beforeClickList[$key]['sum'] - $clickList[$key]['sum'];
			}
			
    		$clickList[$key]['title'] = $adInfo['title'] ? $adInfo['title'] : '益播公益广告';
    	}

    	$this->assign('dateList',$dateList);
    	$this->assign('showList',$showList);
    	$this->assign('clickList',$clickList);
    	$this->display();
    }
	
	/**
	 *	@desc 非注册用户的404页面
	 */
	public function web404_old(){
		$map['status'] = 1;
		//查询主推
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
                
                //领取兑换券提示
                $uid = $_SESSION['userInfo']['id'];
                $show_notice = 0;
                $info = array();
                if ($uid){
                    $info = M("Webcodo")->where("uid=".$uid)->find();
                    $is_login = 1;
                }else{
                    $is_login = 0;
                }
                $codeTotal = M("Webcodo")->where('uid=0')->count();
                
                if ($codeTotal && empty($info)){
                    //if (!$uid || !$_SESSION['is_notice']){
                        $show_notice = 1;
                        $_SESSION['is_notice'] = 1;
                    //}
                }
                
                $this->assign('codeTotal',$codeTotal);
                $this->assign('is_login',$is_login);
                $this->assign('show_notice',$show_notice);
                
		//查询专题信息
		$projectInfo = D('Project')->getDataById($projectId);
		$web_name = '404公益'; 
		$projectInfo['desc'] = msubstr($projectInfo['desc'],0,20);
		$this->assign('projectList',$projectList);
		$this->assign('projectInfo',$projectInfo);
		$this->assign('default_confs',$default_confs);
		$this->assign('web_name',$web_name);
		$this->display();
	}
	
	/**
	 * @desc 404同步编辑
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
	 * @desc 添加404数据
	 */
	public function insertAdd(){
		$uid = $this->userInfo['id']?$this->userInfo['id']:0;
		$_POST['uid'] = $uid;
		$insert_id = D('TempPlace404')->addData($_POST); 
		$host = $this->deploy['YIBO_YU'];
		$configs = D('TempPlace404')->getDataById($insert_id);
		if($configs){
			$res['codes'] = D('Place404')->getDefaultCode($configs,$host);
		}else{
			$default_confs = D('Place404')->getDefaultConfigs($uid);
			$res['codes'] = D('Place404')->getDefaultCode($default_confs,$host);
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
        //print_r($res);exit;
		$this->ajaxReturn($res,1,1);

	
	}
	/**
	 * 活动
	 */
	public function activity(){
		$id = intval($_GET['id']);
		$activityInfo = D('Activity')->getDataById($id);
		
		$this->assign('activityInfo',$activityInfo);
		
		$this->display();
	}
	
	/**
	 * @desc top5加载图片
	 */
	public function loadAd(){
		$ad_id=intval($_POST['ad_id']);
		$ads=D('Ad')->getDataById($ad_id);
		if($ads){
			$size = D('AdSizeConfig')->getDataById($ads['size_id']);
			$ads['width']=$size['width'];
			$ads['hosts']=$host;
			$this->ajaxReturn($ads,'我成功了',1);
		}

	}
	
	/**
	 * @desc 爱心站长名录
	 */
	public function place404show(){
 		if($_REQUEST['web_host']){
			$map['web_host'] = array(array('neq',''),array('neq','localhost'),array('neq','127.0.0.1'),array('like','%'.$_REQUEST['web_host'].'%'),'AND');
			$maps['web_host'] = $_REQUEST['web_host'];
		}else{
			$map['web_host'] = array('neq','');
		}
		//$map['web_name'] = array('neq','');
/* 		if($_POST['web_name']){
			$map['web_name'] = array(array('neq',''),array('like','%'.$_POST['web_name'].'%'),'AND');
		}else{
			$map['web_name'] = array('neq','');
		} */
		//$map['web_host'] = array(array('neq',''),array('neq','localhost'));
		$map['is_come'] = 1;
		import("Common.ORG.Page");
		$count = D('Place404')->getCount($map);
		$Page = new Page($count,30);
		foreach($maps as $key=>$val) {
			$Page->parameter   .=   "$key=".urlencode($val).'&';
		}
		$show = $Page->show();
		$place404List = D('Place404')->getDataList($map,'', 'id desc', $Page->firstRow . ',' . $Page->listRows);
		$this->assign('place404List',$place404List);
		$this->assign('show',$show);
		$this->assign('web_host',htmlspecialchars($maps['web_host']));
		$this->display();
	}
	
	public function cooperation(){
		$cooperationList = D('Cooperation')->getDataList('','','`order` asc');
		$this->assign('list', $cooperationList);
		$this->display();
	}
	
	public function contact(){
		$this->display();
	}
	
	/**
	 * @desc 广告墙加载广告位
	 */
 	public function getPlaceCodes($uid,$size_id,$size_name){
		$host = $this->deploy['YIBO_YU'];
		$sizeName = $size_name;
		$map['size_id'] = $size_id;
		$map['placeType'] = 1;
		if($uid){
			$map['uid'] = $uid;
			$placeInfo = D('Place')->getSingleData($map);
			if($placeInfo){
				//return  D('Place')->getLastsql();
				$codes = D('Place')->createCodes($placeInfo['pid'],$host);
				return $codes;	
			}else{
				//查询临时表
				$tempPlaceInfo = D('TempPlace')->getSingleData($map);
				//有获取，没有插入
				if($tempPlaceInfo){
					$codes = D('Place')->createCodes($tempPlaceInfo['id'],$host);
					return $codes;
				}else{
					$data['status'] = 1;
					$data['name'] = $sizeName;
					$data['size_id'] = $size_id;
					$data['uid'] = $uid;
					$data['placeType'] = 1;
					$data['type'] = 1;
					$data['create_time'] = date('Y-m-d',time());
					$insert_id = D('TempPlace')->addData($data);
					//return D('TempPlace')->getLastsql();
					$codes = D('Place')->createCodes($insert_id,$host);
					return $codes;
				}

			}
			
		}else{
			$map['uid'] = 0;
			$placeInfo = D('Place')->getSingleData($map);
			//return  D('Place')->getLastsql();
			$codes = D('Place')->createCodes($placeInfo['pid'],$host);
			return $codes;
		}

	} 
	
	/**
	 * @desc 广告墙弹窗
	 */
	
	public function adInfos(){
		$yiboCenter = $this->deploy['YIBO_YU'];
		$userCenter = $this->deploy['CENTER_SERVER'];
		
		if($_SESSION['userInfo']){
			$show['pic_small'] = $_SESSION['userInfo']['extend']['pic_small'] ? $userCenter.$_SESSION['userInfo']['extend']['pic_small'] : $this->default_pic;
		}else{
			$show['pic_small'] = $this->default_pic;
		}
		//获取广告信息
		$ad_id = intval($_GET['ad_id']);
		$size_id = intval($_GET['size_id']);
		$adInfo = D('Ad')->getDataById($ad_id);
		$size = D('AdSizeConfig')->getDataById($adInfo['size_id']);
		//根据尺寸按比例缩放
		if($size['width'] > 930){
			$nw = 930;
			$nh = floor(($nw*$size['height'])/$size['width']);
			$adInfo['widths'] = $nw;
			$adInfo['heights'] = $nh;
		}else{
			$adInfo['widths'] = $size['width'];
			$adInfo['heights'] = $size['height'];
		}
		$adInfo['size_name'] = $size['size_name'];
		$aduserInfo = D('Memcache')->getUserInfoById($adInfo['user_id']);
		$adInfo['username'] = $aduserInfo['extend']['user_name'] ? $aduserInfo['extend']['user_name'] : $aduserInfo['email'];
		$adInfo['pic_samll'] = $aduserInfo['extend']['pic_small'] ? $userCenter.$aduserInfo['extend']['pic_samll'] : $this->default_pic;
		//dump($adInfo);
		$link = parse_url($adInfo['url']);
		$adInfo['links'] = $link['host'];
		//月统计
		$map['ad_id'] = $adInfo['id'];
		$adClick_1 = D('GetStatistics')->getAdMonthStatistics($map,'click');
		$clicks_1 = $adClick_1['sum'] ? $adClick_1['sum'] : 0;
		$adShow_1 = D('GetStatistics')->getAdMonthStatistics($map,'show');
		$shows_1 = $adShow_1['sum'] ? $adShow_1['sum'] : 0;
		//日统计
		$maps['ad_id'] = $adInfo['id'];
		$maps['create_time'] = array('GT',date('Y-m-01'));
		$adShow_2 = D('GetStatistics')->getAdDayStatistics($maps,'show');
		$adClick_2 = D('GetStatistics')->getAdDayStatistics($maps,'click');
		$clicks_2 = $adClick_2['sum'] ? $adClick_2['sum'] : 0;
		$shows_2 = $adShow_2['sum'] ? $adShow_2['sum'] : 0;
		$adInfo['shows'] = $shows_1 + $shows_2;
		$adInfo['clicks'] = $clicks_1 + $clicks_2;
		
		$uid = $_SESSION['userInfo']['id'] ? $_SESSION['userInfo']['id'] : 0;
		$codes = $this->getPlaceCodes($uid,$size_id,$size['size_name']);
		
		//获取评论信息
		$comments['ad_id'] = $ad_id;
		$comments = D('AdComment')->getDataList($comments,'','`create_time` desc');
		foreach($comments as $key => $val){
			if(!$val['author_id']){
				$comments[$key]['username'] = '匆匆过路人';
				$comments[$key]['pic_small'] = $this->default_pic;	
				continue;
			}
			$commentsInfo = D('Memcache')->getUserInfoById($val['author_id']);
			$comments[$key]['username'] = $commentsInfo['extend']['user_name'] ?  $commentsInfo['extend']['user_name'] :  $commentsInfo['email'];
			$comments[$key]['pic_small'] = $commentsInfo['extend']['pic_small'] ?  $userCenter.$commentsInfo['extend']['pic_small'] :  $this->default_pic;
		}

		//获取评论数量
		$commentsNums = M('ad_comment')->where('ad_id = '.$ad_id)->count(); 
		$this->assign('comments',$comments);
		$this->assign('commentsNums',$commentsNums);
		$this->assign('adInfo',$adInfo);
		$this->assign('codes',$codes);
		$this->assign('show',$show);
		$this->assign('yiboCenter',$yiboCenter);
		$this->assign('userCenter',$userCenter);
		$this->display();
	}
	
	/**
	 * @desc 点赞
	 */
	public function addLikes(){
		$uid = $this->userInfo['id']?$this->userInfo['id']:0;
		//存COOKIE避免刷点击，间隔时间是3分钟
		if(!$_COOKIE['click_zan'.$_POST['ad_id']]){
			setcookie('click_zan'.$_POST['ad_id'],time(),time()+180);
		}else{
			$this->ajaxReturn(1,'该广告你已经点过赞了',0);
		} 
		$id = intval($_POST['ad_id']);
		$adInfo = D('Ad')->getDataById($id);
		$likes = $adInfo['likes']+1;
		$maps['likes'] = $likes;
		$maps['id'] = $id;
		$maps['status'] = $adInfo['status'];
		$up_id = D('Ad')->saveDataById($maps);
		$sql = D('Ad')->getLastsql();
		if($up_id){
			//登陆用户才会触发积分，未登录用户不触发
			if($uid){
				$userKey = getScoreHistoryKey($uid,strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id,date('Y-m-d'),time());
				$this->userAddScore($userKey);
			}
			$this->ajaxReturn($maps,'点赞成功',1);
		}	
	}
	
	/**
	 * @desc 发表评论
	 */
	public function addComments(){
		$userCenter = $this->deploy['CENTER_SERVER'];
		if(!$_COOKIE['comments_add'.$_POST['ad_id']]){
			setcookie('comments_add'.$_POST['ad_id'],time(),time()+180);
		}else{
			$this->ajaxReturn(1,'该广告你已经评论过了',0);
			exit;
		} 
		$id = intval($_POST['ad_id']);//仅为生存添加积分的KEY而设置
		$uid = $_SESSION['userInfo']['id'] ? $_SESSION['userInfo']['id'] :0;
		$pic_small = $_SESSION['userInfo']['extend']['pic_small'] ? $userCenter.$_SESSION['userInfo']['extend']['pic_small'] : $this->default_pic;
		$username = $_SESSION['userInfo']['extend']['user_name'] ? $_SESSION['userInfo']['extend']['user_name'] :($_SESSION['userInfo']['extend']['email'] ? $_SESSION['userInfo']['extend']['email'] : '匆匆过路人');
		$_POST['author_id'] = $uid;
		$insert_id =D('AdComment')->addData($_POST);
		$times = date('Y-m-d H:i:s',time());
		
		if($insert_id){
			if($uid){
				$userKey = getScoreHistoryKey($uid,strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id,date('Y-m-d'),time());
				 $this->userAddScore($userKey); //添加积分
			}
			$tpl .= '<div class="discuss_part clearfix">';
			$tpl .= '<div class="user_photo">';
			$tpl .= '<a href="javascript:;"><img src="'.$pic_small.'" width="60" height="60" /></a></div>';
			$tpl .= '<div class="discuss_con">';
			$tpl .= '<h3>'.$username.'<span>'.$times.'</span></h3>';
			$tpl .= '<p>'.htmlspecialchars($_POST['content']).'</p>';
			$tpl .= '</div></div>';
			$this->ajaxReturn($tpl,1,1);
		}
	}
	
	/**
	 * 统计数据的定时任务
	 * 普通广告的月统计
	 */
	public function syncStatistics(){
		D('Statistics')->statisticsAdDayToMonth();
		D('Statistics')->statisticsAdDayToMonth('','click');
	}
	
	/**
	 * 统计404流水表进日表，展示部分
	 * 默认是服务器定时任务，也可以手动更新
	 */
	public function ad404StatisticsHistotyToDayShow(){
		D('Statistics')->statistics404HistotyToDay();
	}
	
	/**
	 * 统计404点击流水进日表
	 * 默认服务器定时任务，或者手动更新
	 */
	public function ad404StatisticsHistotyToDayClick(){
		D('Statistics')->statistics404HistotyToDay('','click');
	}
	
	public function ad404StatisticsDayToMonth(){
		D('Statistics')->statistics404DayToMonth('2014-12-04','show');
	}
	
	/**
	 * @desc 动态获取404模板
	 */
	public function getTemplate(){
		$project_id = intval($_POST['project_id']);
		$color = $_POST['color'] ? intval($_POST['color']) : 2;
		if(!$project_id){
			exit;
		}
		//根据专题信息
		$projectInfo = D('Project')->getDataById($project_id);
		//判断是普通专题和设计师专题
		if($projectInfo['type'] == 2){
			$this->getTemplateByAd($project_id);
			exit;
		}
		
		$projectInfo['url_base64'] = base64_encode($projectInfo['url']);
		if($projectInfo['branch_url']){
			$projectInfo['branch_url_base64'] = base64_encode($projectInfo['branch_url']);
		}
		
			$projectInfo['desc'] = msubstr($projectInfo['desc'],0,20);
			//加载具体详情信息
			$projectDetailInfo = D("Memcache")->getProjectDetailInfo($project_id);
			foreach($projectDetailInfo['info'] as $key => $val){
				if($projectDetailInfo['info'][$key] == ''){
					unset($projectDetailInfo['info'][$key]);
					continue;
				}
				if(mb_strlen($projectDetailInfo['info'][$key],'utf-8') >= 30){
					$projectDetailInfo['info'][$key] = msubstr($projectDetailInfo['info'][$key],0,30);
				}
			}
			
		//加载模板
		$templateId = $projectInfo['template_id'];
		
		$templateTpl = 'Tpl404_'.$templateId;
		//加载广告信息
		$size_id = D('Place404')->getAdSizeByTemp($projectInfo['template_id']);
		if($size_id){
			$maps['size_id'] = intval($size_id);
			$adInfos= D('Ad')->getDataList($maps,'','rand()',1);
			$adInfo = $adInfos[0];
			$adInfo['url'] = base64_encode($adInfo['url']);
			$this->assign('adInfo',$adInfo);
		}
		
		//查询认领信息,暂无接口
		
		//加载域名
		$comeUrl = $_SERVER["HTTP_REFERER"];
		$parseUrl = parse_url($comeUrl);
		$reindex  = $parseUrl['host'];
		$projectDetailInfo['url'] = base64_encode($projectDetailInfo['url']);
		
		//默认广告位
		$placeInfo['id'] = 0;
		$this->assign('projectInfo',$projectInfo);
		$this->assign('projectDetailInfo',$projectDetailInfo);
		$this->assign('color',$color);
		$this->assign('comeUrl',base64_encode($comeUrl));
		$this->assign('reindex',$reindex);
		$this->assign('placeInfo',$placeInfo);		
		$this->display("Template:".$templateTpl);
	}
	
	
	//必应二次跳转
	public function place(){
		$this->redirect("Index/web404");
	}
	
	//邮件节点跳转
	public function popEmail(){
		$this->assign('toUrls',$this->deploy['CENTER_SERVER'].'/Index/addEmail');
		$this->display();
	}
	
	//触发清空本地的SESSION
	public function cleanCache(){
		if($_SESSION['userInfo']['id'] && $_POST['clientsendtoserver']){
			unset($_SESSION['userInfo']);
		}
		$this->ajaxReturn(1,1,1);
	}
	//TOP5新版
	public function top6(){
		$userCenter = $this->deploy['CENTER_SERVER'];
		$default_month = date('n',strtotime('-1 month'));
		//获取上月的日期
		$month = $_POST['dateTimes'] ? intval($_POST['dateTimes']) : date('n',strtotime('-1 month'));
		$map['create_time'] = $_POST['dateTimes'] ? date('Y').'-'.$_POST['dateTimes'].'-00' : date('Y').'-'.(date('m')-1).'-00';
		//获取展示和点击量
		$showList = D('GetStatistics')->getAdMonthStatisticsList($map,'show','`ad_id`,`sum`',"`sum` desc",'100');
		$clickList = D('GetStatistics')->getAdMonthStatisticsList($map,'click','`ad_id`,`sum`',"`sum` desc",'101');
		//加载广告的详情信息
		foreach($showList as $key=>$val){
			$adInfo = D('Ad')->getDataById($val['ad_id']);
			$showList[$key]['adInfo'] = $adInfo;
			$userInfo = D('Memcache')->getUserInfoById($adInfo['user_id']);
			$showList[$key]['username'] = $userInfo['extend']['user_name'] ? $userInfo['extend']['user_name'] : ($userInfo['extend']['true_name'] ? $userInfo['extend']['true_name'] : ($userInfo['email'] ? $userInfo['email'] : $userInfo['email']));
		}
		$adInfo = null;
		$userInfo = null;
		foreach($clickList as $key=>$val){
			if(!$val['ad_id']){
				unset($clickList[$key]);
				continue;
			}
			$adInfo = D('Ad')->getDataById($val['ad_id']);
			$clickList[$key]['adInfo'] = $adInfo;
			$userInfo = D('Memcache')->getUserInfoById($adInfo['user_id']);
			$clickList[$key]['username'] = $userInfo['extend']['user_name'] ?  $userInfo['extend']['user_name'] :  $userInfo['email'];	
		}
		$this->assign('userCenter',$userCenter);
		$this->assign('showList',$showList);
		$this->assign('clickList',$clickList);
		$this->assign('month',$month);
		$this->assign('default_month',$default_month);
		$this->display();
	
	}
	
	/**
	 *
	 * @desc 调取404设计广告
	 */
	public function getTemplateByAd($project_id){
		$project_id = $project_id;
		$userCenter = $this->deploy['CENTER_SERVER'];
		//获取模板
		$projectInfo = D('Project')->find($project_id);
		$templateId = $projectInfo['template_id'];
		$templateTpl = 'Tpl404_'.$templateId;
		
		//获取设计专题对应的消息
		$map['project_id'] = $project_id;
		$map['status'] = 1;
		$projectDetailInfo = M('project_ad')->field('ad_id')->where($map)->select();

		foreach($projectDetailInfo as $val){
			$arrs[] = $val['ad_id'];
		}
		$ad_ids = join(',',$arrs);

		$map = null;
		//获取一组404广告,需要加上赞助、购买等状态,如果没有作品需要考虑一组默认的作品
		$map['type'] = 2;
		$map['status'] = 2;
		$map['id'] = array('in',$ad_ids);
		$adList = D('Ad')->getDataList($map);
		if(!$adList){
			$adInfo = D('Place404')->getDefaultInfo();
		}else{ 
			//$adKey = array_rand($adList);
			$adInfo = $adList[array_rand($adList)];
			//查询设计作品的广告记录
			$map = null;
			$map['type'] = 1;//赞助
			$map['status'] = 1;//状态有效
			$map['ad_id'] = $adInfo['id'];
			$buyInfo = D('AdBuy')->where($map)->order('`id` desc')->find();
			//获取设计师的信息
			$degInfo = D('Memcache')->getUserInfoById($buyInfo['seller_id']);
			$degInfo['extend']['pic_small'] = $degInfo['extend']['pic_small'] ? $userCenter.$degInfo['extend']['pic_small'] : $this->default_pic;
			//得到赞助者的信息
			$buyerInfo  = D('Memcache')->getUserInfoById($buyInfo['buyer_id']);
			$buyerInfo['logos'] = $buyInfo['logo'];
			$buyerInfo['logo_url'] = $buyInfo['url'];
			$adInfo['deger'] = $degInfo;
			$adInfo['buyer'] = $buyerInfo;
		}
		$this->assign('userCenter',$userCenter);
		$this->assign('projectInfo',$projectInfo);
		$this->assign('adInfo',$adInfo);
		$this->display("Template:".$templateTpl);

	}

    /**
     * web404新版页面
     */
    public function web404(){
        $map['status'] = 1;
        //查询主推
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

        //领取兑换券提示
        $uid = $_SESSION['userInfo']['id'];
        $show_notice = 0;
        $info = array();
        if ($uid){
            $info = M("Webcodo")->where("uid=".$uid)->find();
            $is_login = 1;
        }else{
            $is_login = 0;
        }
        $codeTotal = M("Webcodo")->where('uid=0')->count();

        if ($codeTotal && empty($info)){
            //if (!$uid || !$_SESSION['is_notice']){
            $show_notice = 1;
            $_SESSION['is_notice'] = 1;
            //}
        }

        //404新加入的爱心站记录
        $pmap['web_name'] = array('neq','');
        $pmap['web_host'] = array('neq','');
        $pmap['status']   = 2;
        $placeList = D('Place404')->where($pmap)->field('web_name,web_host')->order('id desc')->limit(6)->select();
        $this->assign('hostList',$placeList);

        $this->assign('codeTotal',$codeTotal);
        $this->assign('is_login',$is_login);
        $this->assign('show_notice',$show_notice);

        //查询专题信息
        $projectInfo = D('Project')->getDataById($projectId);
        $web_name = '404公益';
        $projectInfo['desc'] = msubstr($projectInfo['desc'],0,20);
        $this->assign('projectList',$projectList);
        $this->assign('projectInfo',$projectInfo);
        $this->assign('default_confs',$default_confs);
        $this->assign('web_name',$web_name);
        $this->display();
    }

    /**
     * 发送邮件
     */
    public function sendEmails(){
        $message = htmlspecialchars($_POST['content']);
        $email   = $_POST['email'];
        $title   = htmlspecialchars($_POST['name']);
        $result  = sendMail($email,$title,$message);
        if($result){
            $this->ajaxReturn('ok','发送成功！',1);
        }else{
            $this->ajaxReturn('fail','发送失败！',0);
        }
    }

}