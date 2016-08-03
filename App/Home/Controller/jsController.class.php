<?php
namespace Home\Controller;
use Common\Controller\BaseController;
/**
 * 广告展示/数据统计的入口
 * @athor 张良
 * @vstion 1.0
 * @create_date 2013-07-10
 */
class jsController extends BaseController{
	
    /**
     * 当没有获取到数据时显示默认页面
     */
    private $errorImg = "";

    /**
     * 单一的广告入口
     * 说明：广告id直接获取数据,优先查看推送比重
     */
    public function simple()
    {
        $IP = get_client_ip(); // 获取客户端IP
        $adID = intval($_GET["ad_id"]); // 广告id
        $array = D("Memcache")->getAdById($adID);
        echo $this->getJs($array, $IP, 0);
    }

    /**
     * 得到测试人员所看的字符串
     */
    protected function getTestString($array = array(), $PlaceID)
    {
        return '';
        $str = "<br>";
        $str .= "广告投放区域ID => " . $array["area"] . "<br>";
        $str .= "广告ID => " . $array["id"] . "<br>";
        $str .= "广告尺寸 => " . D("AdConfigSize")->getFieldById($array["size_id"], "size_name") . "<br>";
        $str .= "广告关键字 => " . $array["keywords"] . "<br>";
        $str .= "<br>";
        $PlaceRows = D("UserAdPlace")->getDataById($PlaceID);
        $str .= "广告位ID => " . $PlaceID . "<br>";
        $str .= "广告位关键字 => " . $PlaceRows["keywords"] . "<br>";
        $str .= "广告位反关键字 => " . $PlaceRows["re_keywords"] . "<br>";
        return $str;
    }

//------------------------------------------------------------------
//------404接口 建超完成---------------------------------------------
//------------------------------------------------------------------
    public function api()
    {
        if ($_GET['key'] == 'e45e3099a8faae43df86cb3406956d87') {
        	$data = M('project_detail')->select();
			foreach($data as $key=>$val){
				$data[$key]['info'] = unserialize($val['info']);
				$infoList[]['name'] = $val['info'][1]; 
			}
			foreach($data as $key=>$val){
				$infoList[$key]['name'] =  $val['info'][1];
				$infoList[$key]['sex'] =   $val['info'][2];
				$infoList[$key]['height'] = $val['info'][3];
				$infoList[$key]['area'] = $val['info'][6];
				$infoList[$key]['desc'] = $val['info'][7];
				$infoList[$key]['losttime'] = $val['info'][5];
				$infoList[$key]['descurl'] = $val['info'][9];
				$infoList[$key]['barthtime'] = $val['info'][4];
				$infoList[$key]['imgsrc'] = $val['info'][10];
				$infoList[$key]['tel'] = $val['info'][8];
			}
            echo json_encode($infoList);
        }
    }

    public function yibo404(){
    	$url = base64_encode($_SERVER['HTTP_REFERER']);
        $id = intval($_GET['key']);
		$this->redirect('/Home/Distribute/ad404',array('key'=>$id,'url'=>$url));
    }
	
	//世纪天成的跳转到ID
	public function yibo_tiancity(){
		//加载配置
		$host = $this->deploy['YIBO_YU'];
		
		//默认广告位为0
		$place404Id = 0;
		
		//专题默认选1
		$projectId = 1;
		
		//获取专题信息
		$projectInfo = D('Project')->find(1);
		$projectDetailInfo = D("Memcache")->getProjectDetailInfo($projectInfo['id']);//获取专题内容
    	$projectDetailInfo['info']['详情描述']=msubstr($projectDetailInfo['info']['详情描述'],0,80);
		 foreach($projectDetailInfo['info'] as $key=>$val){
		   		//定义保留字段
		   	$needColumn = array('姓名','性别','出生日期','详情描述','tel');
		   	if(!in_array($key,$needColumn)){
		   		unset($projectDetailInfo['info'][$key]);
		   	}		
    		if(!$val){
    			unset($projectDetailInfo['info'][$key]);
    		}
    	}
    	$projectDetailInfo['url'] = base64_encode($projectDetailInfo['url']);
		//指定模板，世纪天成指定模板
		$templateTpl = 'Tpl404_13';
		
		//加载统计
		$data['project_id'] = $projectInfo['id'];
    	$data['place_id'] = $place404Id ? $place404Id : 0;
    	$data['uid'] = 0;
    	$data['come_url'] = $_SERVER['HTTP_REFERER'];
    	$data['ip'] = get_client_ip();
    	$data['area_id'] = D("Ip")->GetAreaID($data['ip']);
    	$data['create_time'] = date('Y-m-d H:i:s');
    	D('Statistics')->update404HistoryTable($data,'show');
		$this->assign("projectInfo",$projectInfo);
		$this->assign('projectDetailInfo',$projectDetailInfo);
		$comeUrl = base64_encode($data['come_url']);
    	$this->assign('reindex', $reindex);
    	$this->assign( 'comeUrl',$comeUrl);
		$this->assign('keys',$place404Id);
		$this->assign('host',$host);
		$this->display('Template:'.$templateTpl);
		
	}
}