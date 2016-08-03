<?php
namespace Home\Controller;
use Common\Controller\BaseController;
/**
 * 益播广告统一分发入口
 * @author 宋小平
 * create time 2014-06-16
 */
class DistributeController extends BaseAction{
	private $ids = array(2769,10378,2882,2889);
	
    /**
     * 普通广告获取入口
     * 说明:广告位+IP随机匹配，优先查看推送比重
     */
    public function index(){
		$placeId = intval($_GET['placeId']);
		if(!$placeId){
			return false;
		}
		$adInfo = D('Memcache')->getAdInfoBySpaceId($placeId);
		if(!$adInfo){
			return false;
		}
		if( ($adInfo['type'] == 2) || ( $adInfo['type'] == 5) ){
			$adInfo['url'] = $this->deploy['YIBO_YU']."/Stylist/detail/id/".$adInfo['id'];
		}
		
		//更新统计流水表
		$data['ad_id'] = $adInfo['id'];
		$data['uid'] = $adInfo['user_id'];
		$data['place_id'] = $placeId;
		$data['come_url'] = $_SERVER['HTTP_REFERER'];
		$data['ip'] = get_client_ip();
		$data['area_id'] = D("Ip")->GetAreaID($data['ip']);
		$data['create_time'] = date('Y-m-d H:i:s');
		D('Statistics')->updateAdHistoryTable($data);
		echo $this->getJs($adInfo,$placeId);
    }
    
    /**
     * 根据数组得到要输出的js
     * @param array 广告数据
     * @param ip 访问人ip
     * @param $adSpaceId 广告位
     * @return string  输出的html
     */
    protected function getJs($adInfo,$place_id = 0){
    	if (!is_array($adInfo)) return $this->errorImg; // 失败展示默认页面,并且不计入展示量
    	$url = base64_encode($adInfo["url"]);
    	$ip = base64_encode($ip);
    	$placeId = intval($place_id);
    	$adId = intval($adInfo["id"]);
    	$adInfo["desc"] = htmlspecialchars($adInfo["desc"]);
    	$adInfo["desc"] = str_replace("'", "", $adInfo["desc"]);
    	$adInfo["desc"] = str_replace(";", "；", $adInfo["desc"]);
    	$adInfo["desc"] = trim($adInfo["desc"]);
    	$adInfo["desc"] = preg_replace("/[\s]{2,}/","",$adInfo["desc"]);
    	$adInfo["title"] = trim($adInfo["title"]);
    
    	$left_max = $adInfo["width"] - 96;
    	$left_min = $adInfo["width"] - 22;
    	$random = rand(10000, 99999);
    	$server = $this->deploy['SERVER_HOST'];
    	
    	$input_str  = '<div style="width:'.$adInfo["width"].'px; height:'.$adInfo["height"].'px;display:block;padding:0;margin:0;z-index:1000;border:0 none;" >';
    	$input_str .= '<div style="border:0 none; width:'.$adInfo["width"].'px; height:'.$adInfo["height"].'px;">';
    	$input_str .= '<a target="_blank"  href="'.$server.'/Distribute/Jump?placeId=' . $placeId . '&url=' . $url . '&areaId=' . $adInfo["area"] . '&ip=' . $ip . '&adId=' . $adId . '" >';
    	$input_str .= '<img style="border:0;" src="'. $server . $adInfo["pic"] . '"   alt="' . $adInfo["title"] . '"   title="' . $adInfo["desc"] . '"  width="' . $adInfo["width"] . '"  height="' . $adInfo["height"] . '" /></a></div>';
    	$input_str .= '<div id="' . $random . '_div" style="margin-top:-22px;border:0 none;">';
    	$input_str .= '<a style=" display:block; position:relative; height:22px; overflow:hidden;" href="'.$server.'" title="益云致力于公益机构和社会企业的广告传播,益云通过广告助力社会企业和公益广告的传播.益播公益已有公益博客广告、404公益广告、QQ空间公益广告、爱心公益广告位等多种形式." target="_blank" >';
		$input_str .= '<img id="img1" style="right:0;top:0;position:absolute;z-index:999; border:0;" src="'.$this->deploy['SERVER_HOST'].'/Public/images/yibo_logo1.png" />';
		$input_str .= '<img id="img2" style="right:0;top:0;position:absolute;z-index:9999; border:0; display:none;" src="'.$this->deploy['SERVER_HOST'].'/Public/images/yibo_logo2.png" />';
    	$input_str .= '</a></div></div>';
    	//$url = $server . '/js/acceptDataToDb?yb_ad_id=' . $adInfo["id"] . '&area_id=' . $adInfo["area"] . '&ip=' . $ip . '&yb_place_id=' . $place_id ;
    	return 'document.write(\'' . $input_str . '\');
		window.onload = function(){
			var img1 = document.getElementById("img1");
			var img2 = document.getElementById("img2");
			img1.onmouseover = function () {
				img1.style.display = "none";
				img2.style.display = "block";
			};
			img2.onmouseout = function () {
				img2.style.display = "none";
				img1.style.display = "block";
			};

		};';
    }
    
    /**
     * 广告跳转，含点击数据统计
     */
    public function jump(){
    	$url = base64_decode($_GET['url']);
    	$data['place_id'] = intval($_GET['placeId']);
    	$data['ip'] = get_client_ip();
    	$data['area_id'] = D("Ip")->GetAreaID($data['area_id']);
    	$data['create_time'] = date('Y-m-d H:i:s');
    	
    	if($_GET['type'] == 404){
    		$data['project_id'] = intval($_GET['projectId']);
    		$data['come_url'] = base64_decode($_GET['comeUrl']);
    		D('Statistics')->update404HistoryTable($data,'click');
    	}else{
	    	$data['ad_id'] = intval($_GET[adId]);
	    	$data['come_url'] = $_SERVER['HTTP_REFERER'];
	    	$adInfo = D('Memcache')->getAdInfoById($data['ad_id']);
	    	$data['uid'] = $adInfo['user_id'];
	    	D('Statistics')->updateAdHistoryTable($data,'click');
    	}
    	header("Location:" . $url);
    }
    
    /**
     * 404页面上普通广告跳转，含点击数据统计
     */
    public function jump404ad(){
    	$url = base64_decode($_GET['url']);
    	$data['place_id'] = intval($_GET['placeId']);
    	$data['ip'] = get_client_ip();
    	$data['area_id'] = D("Ip")->GetAreaID($data['area_id']);
    	$data['create_time'] = date('Y-m-d H:i:s');
    	 

    		$data['ad_id'] = intval($_GET[adId]);
    		$data['come_url'] = base64_decode($_GET['comeUrl']);
    		$adInfo = D('Memcache')->getAdInfoById($data['ad_id']);
    		$data['uid'] = $adInfo['user_id'];
    		D('Statistics')->update404adHistoryTable($data,'click');
    	header("Location:" . $url);
    }
    
    /**
     * 404广告展示页面，包含展示统计信息
     */
    public function ad404(){
    	// 设置手机端广告位ID，用来存储手机端404广告位ID的数组，可在配置文件配置，可在deploy中配置
    	$phoneKey = array(7702);
    	$oldUrl = $_GET['url'] ? $_GET['url'] : '';
    	$url = $oldUrl ? base64_decode($oldUrl) : '';
		$host = $this->deploy['YIBO_YU'];
		$userCenter = $this->deploy['CENTER_SERVER'];
    	$place404Id = intval($_GET['key']);
    	//定义是否来自手机端的检测
    	if(in_array($place404Id,$phoneKey)){
    		$isPhone = true;
    	}else{
    		$isPhone = false;
    	}
    	$placeInfo = D("Place404")->getPlace404ById($place404Id,$oldUrl);//获取广告位信息
    	//获取所有用户选中专题，并在其中随机一个，如果没有选中就返回ID是1的专题
    	$map['status'] = 1;
    	//手机专用调取,key == ?
    	if(!$isPhone){
	    	$map['id'] = $placeInfo['mid'] ? array('in',"{$placeInfo['mid']}") : 1;
			$projectList = D('Project')->getDataList($map);
			$key = array_rand($projectList);
			//用户支持主推
			if($placeInfo['is_push']){
				$projectInfo = D('Project')->where('is_push = 1 and status = 1')->find();
				if(!$projectInfo){
					$projectInfo = $projectList[$key];//随机到的专题
				}
			}else{
				$projectInfo = $projectList[$key];
			}
    	}else{
    		$projectInfo = D('Project')->find(1);
    	} 
		//如果专题的类型是type = 2 设计师专题

		if($projectInfo['type'] == 2){
			$map = null;
			$map['project_id'] = $projectInfo['id'];
			$map['status'] = 1;
			$projectDetailInfo = M('project_ad')->field('ad_id')->where($map)->select();
		
			foreach($projectDetailInfo as $val){
				$arrs[] = $val['ad_id'];
			}
			$ad_ids = join(',',$arrs);
			$map = null;
			//获取一组404广告,需要加上赞助、购买等状态,如果没有作品需要考虑一组默认的作品
			$map['id'] = array('in',$ad_ids);
			$map['type'] = 2;
			$map['status'] = 2;
			$adList = D('Ad')->getDataList($map);
			if(!$adList){
				$adInfo = D('Place404')->getDefaultInfo();
			}else{ 
				$adKey = array_rand($adList);
				$adInfo = $adList[$adKey];
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
		}else{
			$projectInfo['url_base64'] = base64_encode($projectInfo['url']);
			if($projectInfo['branch_url']){
				$projectInfo['branch_url_base64'] = base64_encode($projectInfo['branch_url']);
			}
			$projectDetailInfo = D("Memcache")->getProjectDetailInfo($projectInfo['id']);//获取专题内容
			$projectDetailInfo['info']['详情描述']=msubstr($projectDetailInfo['info']['详情描述'],0,80);
			//如果手机调用需要过滤字段 
			foreach($projectDetailInfo['info'] as $key=>$val){
				
				if($isPhone){
					//定义保留字段
					$needColumn = array('姓名','性别','出生日期','详情描述','联系方式');
					if(!in_array($key,$needColumn)){
						unset($projectDetailInfo['info'][$key]);
					}		
				}

                if($place404Id == 17381){
                    $needColumn = array('姓名','性别','联系方式','出生日期','详情描述');
                    $projectDetailInfo['info']['详情描述']=msubstr($projectDetailInfo['info']['详情描述'],0,22);
                    if(!in_array($key,$needColumn)){
                        unset($projectDetailInfo['info'][$key]);
                    }
                }
				
				if($place404Id == 17812){
                    $needColumn = array('姓名','性别','联系方式','出生日期','详情描述');
                    //$projectDetailInfo['info']['详情描述']=msubstr($projectDetailInfo['info']['详情描述'],0,18);
                    if(!in_array($key,$needColumn)){
                        unset($projectDetailInfo['info'][$key]);
                    }
                }

				if(!$val){
					unset($projectDetailInfo['info'][$key]);
				}
			}
			$projectDetailInfo['url'] = base64_encode($projectDetailInfo['url']);
			//添加一条随机广告
			$maps['size_id'] = 13;
			$maps['status'] = 2;
			$adInfoList = D('Ad')->getDataList($maps,'',"rand()",1);
			$adInfo = $adInfoList[0];
			$adInfo['url'] = base64_encode($adInfo['url']);
		}
		//添加颜色
		$color = $placeInfo['colors'] ? intval($placeInfo['colors']) : 1 ;
    	$this->assign('placeInfo',$placeInfo);
    	$this->assign('projectInfo',$projectInfo);
    	$this->assign('adInfo',$adInfo);
    	$this->assign('projectDetailInfo',$projectDetailInfo);
		$this->assign('host',$host);
		$this->assign('color',$color);
    	
    	//$template = 'ad404_'.$projectInfo['template_id'];
		$templateTpl = 'Tpl404_'.$projectInfo['template_id'];
    	//更新展示的流水表

    	$data['project_id'] = $projectInfo['id'];
    	$data['place_id'] = $place404Id ? $place404Id : 0;
    	$data['uid'] = $placeInfo['uid'];
    	$data['come_url'] = $url ? $url : $_SERVER['HTTP_REFERER'];
    	$data['ip'] = get_client_ip();
    	//$data['area_id'] = D("Ip")->GetAreaID($data['ip']);
    	$data['area_id'] = 1;
    	$data['create_time'] = date('Y-m-d H:i:s');
    	D('Statistics')->update404HistoryTable($data,'show');
    	
    	$parseurl = parse_url($data['come_url']);
    	$reindex = $parseurl['host'];
    	$comeUrl = base64_encode($data['come_url']);
    	$this->assign('reindex', $reindex);
    	$this->assign( 'comeUrl',$comeUrl);
    	$this->assign('keys',$place404Id);
		$this->assign('userCenter',$userCenter);
		//定向手机和PC模板
		if($place404Id == 17381){
			$this->display('Template:sougou');
		}elseif($place404Id == 17812){
			$this->display('Template:sougou-pc');
		}else{
			if(!$isPhone){
				$this->display('Template:'.$templateTpl);
			}else{
				$this->display("Template:Tpl404_12");
			}
		}

    }
    
    /**
     * 博客广告位广告获取
     */
    public function blog(){
		$placeId = intval($_GET['placeId']);
		$adInfo = D('Memcache')->getAdInfoBySpaceId($placeId);
		
		$data['ad_id'] = $adInfo['id'];
		$data['uid'] = $adInfo['user_id'];
		$data['place_id'] = $placeId;
		$data['come_url'] = $_SERVER['HTTP_REFERER'];
		$data['ip'] = get_client_ip();
		$data['area_id'] = D("Ip")->GetAreaID($data['ip']);
		$data['create_time'] = date('Y-m-d H:i:s');
		D('Statistics')->updateAdHistoryTable($data);
    	//流水表添加数据
    	$this->wget_imgcl($adInfo['pic']);
    }
    /**
     * 图片输出处理
     */
    
    public function wget_imgcl($img)
    {
    	$echoimg = $this->_ecimg("http://".$_SERVER["HTTP_HOST"].$img);
    	die($echoimg);
    
    
    }
    public function _ecimg($imgurl)
    {
    	header("Content-Type:image/jpg");
    	return file_get_contents($imgurl);
    
    }
	
	//单广告调取
	public function simple(){
		$ad_id = intval($_GET['ad_id']);
		$adInfo = D('Memcache')->getAdInfoById($ad_id);
		return  '<img src="'.$adInfo['pic'].'" title="'.$adInfo['title'].'"/>';
	}

    //根据广告位id随机获取一条广告
    public function mapAdBySizeId(){
        $sizeId = intval($_GET['size_id']);
        $adInfoList = D('Memcache')->getAdListBySizeId($sizeId);
        $rand_keys = array_rand($adInfoList, 1);
        $adInfo = $adInfoList[$rand_keys];
        $adInfo = D('Memcache')->getAdInfoById($adInfo['id']);
        echo $this->getJs($adInfo);
    }
	
}