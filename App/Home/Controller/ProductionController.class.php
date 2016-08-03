<?php
namespace Home\Controller;
use Common\ORG\Page;
/**
 * @desc        作品
 * @author      liuqiuhui
 * @createdate  2014-12-29
 */
class ProductionController extends CommonController{
    
    /**
	 * @desc 用户作品列表
	 */
	public function index(){
		$userInfo = $this->getUserInfo();
		$map['user_id'] = $userInfo['id'];
        $map['type'] = array('in','2,3,4');
		import("Common.ORG.Page");
		$count =D('Ad')->getCount($map);
		$Page = new Page($count, 10);
		$show = $Page->show();
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
		$adList = D('Ad')->getDataList($map, '', '', $Page->firstRow . ',' . $Page->listRows);
		$webData = D("WebTitle")->getWebData(2);
		$this->assign("webData",$webData);
		$this->assign('show',$show);
		$this->assign('proList',$adList);
		$this->assign('p',$p);
		$this->display();
	}
    
    /**
	 * @desc   编辑作品
	 */
	public function edit(){
		$map['id'] = intval($_GET['id']);
		$userInfo = $this->getUserInfo();
        $map['user_id'] = $userInfo['id'];
        $myUrl = $userInfo['extend']['my_url'];

		$adInfo = D('Ad')->getDataByMap($map);

		if(!$adInfo){
			$this->error('您无此广告！');
		}

        !empty($adInfo['area']) && $adInfo['area'] = explode(',', $adInfo['area']);
		$size = D('AdSizeConfig')->getDataById($adInfo['size_id']);
		$adInfo['picWidth'] = $size['width'];
		$adInfo['picHeight'] = $size['height'];
		$area = D("Area")->getAreaList(0); //todo
		$categoryList = D('KeywordsCategory')->getDataList();
        $adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');

		$adInfo['start_date'] = date('Y-m-d',$adInfo['start_date']);
		$adInfo['end_date'] = date('Y-m-d',$adInfo['end_date']);

		$this->assign('area',$area);
		$this->assign('adSizeList',$adSizeList);
		$this->assign('categoryList',$categoryList);
		$this->assign('adInfo',$adInfo);
        $this->assign('myUrl',$myUrl);
		$this->display();
	}
	
	/**
	 * @desc   保存编辑方法
	 */
	public function saveEdit(){
        if($_POST['areaType'] == 'all' || ( $_POST['areaType'] == 'custom' && count($_POST['area']) == 34 ) ){
            $_POST['area'] = null;
        }else{
            $_POST['area'] = join(',', $_POST['area']);
        }
        $_POST['status'] = 1;

        $res = D('Ad')->saveDataById($_POST);

        if($res){
            //$this->success('修改成功！','/Home/Production/index');
            redirect("/Home/Production");
        }else{
            $this->error( D('Ad')->getLastError() );
        }
	}
    
    /**
	 * 作品提交页
     * @author liuqiuhui
	 */
	public function inputProduction(){
        $this->isLogin();
        $groupName = $_SESSION['userInfo']['group']['group_name'];
        $categoryList = D('KeywordsCategory')->getDataList();        
		$adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');
        $demandList   = D("Demand")->getDataList('', '', 'id DESC','0,10');
       // dump($demandList);
        foreach($demandList as $key=>$val){
        	$sql = "select count(distinct user_id) as num from ad_link_demand where demand_id={$val['id']}";
        	$num = M()->query($sql);
        	$demandList[$key]['num'] = $num[0]['num'] ? $num[0]['num'] : 0;
        }
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('production_add');
        $this->assign('banner', $banner);

        $this->assign('demandList',$demandList);
		$this->assign('adSizeList',$adSizeList);
        $this->assign('categoryList',$categoryList);
        $this->assign('isGroup',$groupName);
        $this->assign('fromUrl',base64_decode($_GET['url']));
		$this->display();
	}
    
	/**
	 * 作品获取图片大小Ajax方法
     * @ahthor liuqiuhui
	 */
	public function getPicSize(){
		$id = $_GET['id'];
		$size = D('AdSizeConfig')->getDataById($id);
		if($size['width'] > 600){
			$size['width'] = $size['width']/2;
			$size['height'] = $size['height']/2;
		}
		echo json_encode($size);
	}
    
    /**
	 * 作品提交页保存
     * @author liuqiuhui
	 */
	public function saveAddProduction(){
        $this->isLogin();
        $groupName = $_SESSION['userInfo']['group']['group_name'];
        if($groupName != '设计师' && $groupName){
            $this->error('您已认证为$groupName,只有认证为设计师才可以上传作品哟！','Stylist');
            exit;
        }elseif($groupName != '设计师'){
            $this->error('请认证为设计师',$this->deploy['CENTER_SERVER'].'/Home/Index/userOrgApply');
            exit;
        }
        $_POST['user_id']     = $_SESSION['userInfo']['id'];
        $_POST['create_time'] = date("Y-m-d H:i:s");
        $_POST['title']       = htmlspecialchars($_POST['title']);
        $_POST['desc']        = htmlspecialchars($_POST['desc']);
        $id = D("Ad")->addData($_POST);
        if($id){
            //$this->success('作品上传成功！','');
            redirect('/Home/Production');
        }else{
            $this->error(D("Production")->getLastError());
        }
	}
	
	/**
	 * 需求作品页面
     * @author liuqiuhui
	 */
	public function inputDemandProduction(){
        $this->isLogin();
        import("Common.ORG.Page");
        $groupName = $_SESSION['userInfo']['group']['group_name'];

        if($groupName != '设计师' && $groupName){
            $isFalse = 1;
        }elseif($groupName != '设计师'){
            $isFalse = 1;
        }
        //需求信息
        $id = intval($_GET['id']);
        if(!$id){ $this->error('您访问的需求不存在',"/Home/Stylist/demandList"); }
        $demandInfo = D('Demand')->getDataById($id); 
        $demandInfo['username'] = D('Demand')->getUsername($demandInfo['uid']);        
        $demandInfo['file']     = base64_encode($demandInfo['result']); 
        if(!empty($demandInfo['size_id'])){
            $sizeList = D('AdSizeConfig')->getDataList(" id IN ($demandInfo[size_id])");
        }
        
        $map['demand_id'] = $id;
        $map['accept']    = 1;
        //判断是否已有采纳作品
        $isHave = D('AdLinkDemand')->getCount($map);
        unset($map['accept']);
        $count  = count(D("AdLinkDemand")->getAdByDemandList(array("ald.demand_id"=>$id), '', '', '' , " ald.user_id "));
        //$count = D('AdLinkDemand')->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D("AdLinkDemand")->getAdByDemandList(array("ald.demand_id"=>$id), '', '', $Page->firstRow.','.$Page->listRows , " ald.user_id ");
        foreach($data as $k=>$v){
            
            $data[$k]['username'] = D("Demand")->getUsername($v['uid']);
            $data[$k]['create_time'] = date("Y-m-d H:i:s",$v['create_time']);
            $info = D('Memcache')->getUserInfoById($v['uid']);
            if ($info['extend']['pic_small']){
                $data[$k]['userPic'] = $this->deploy['CENTER_SERVER'].$info['extend']['pic_small'];
            }else{
                $data[$k]['userPic'] = '/Public/images/default.jpg';
            }            
        }
        $demandList   = D("Demand")->getDataList('', '', 'id DESC','0,10');       
        foreach($demandList as $key=>$val){
        	$sql = "select count(distinct user_id) as num from ad_link_demand where demand_id={$val['id']}";
        	$num = M()->query($sql);
        	$demandList[$key]['num'] = $num[0]['num'] ? $num[0]['num'] : 0;
        }
        $this->assign('sizeList',$sizeList);        
        $this->assign('page',$show);
        $this->assign('data',$data);
        $this->assign('demandInfo', $demandInfo);
        $this->assign('demandList',$demandList);
        $this->assign('isFalse',$isFalse);
        $this->assign('isHave',$isHave);
        $this->assign('count',$count);
        $this->display();
        
	}
    
    /**
     * 保存需求作品
     * @author liuqiuhui
     */
    public function saveDemandProduction(){
        $this->isLogin();
        $_POST['title'] = $_SESSION['userInfo']['extend']['user_name'] ? $_SESSION['userInfo']['extend']['user_name'] : $_SESSION['userInfo']['extend']['true_name'];
        $_POST['title'] .= "的作品";
        //Array ([size_id]=>25 [demand_id]=>5 [desc]=>[img] => Array ([0]=>/Public/upload/production/194627851.jpg ))
        $_POST['user_id']      = $_SESSION['userInfo']['id'];
        $_POST['user_name']    = D("Demand")->getUsername($_SESSION['userInfo']['id']);
        $_POST['desc']         = htmlspecialchars($_POST['desc']);
        $_POST['type']         = 3;
        $_POST['type_id']      = 1;
        $_POST['status']       = 1;
        $_POST['create_time']  = time();
        $imgArr   = $_POST['img'];
        unset($_POST['img']);
        if(empty($imgArr)){
             $this->error('没上传对应尺寸的作品！','');
        }
        $sizeArrD = explode(",",$_POST['size_id']);
        foreach($imgArr as $key => $val){
            $data[$key] = $_POST;
            $data[$key]['pic'] = $val;
            $picInfo       = getimagesize("." . $val);
            $map['width']  = $picInfo[0];
            $map['height'] = $picInfo[1];
            $data[$key]['size_id'] = D('AdSizeConfig')->where($map)->getField('id');
            $sizeArr[] = $data[$key]['size_id'];          
        }
        $sizeArr = array_unique($sizeArr);
        sort($sizeArrD);
        sort($sizeArr);
        foreach($sizeArrD as $k => $val){
            if($val != $sizeArr[$k]){
                $this->error('上传作品图片尺寸错误！','');
            }
        }
        $aldData = array();
        $aldData['demand_id']   = $_POST['demand_id'];        
        $aldData['user_id']     = $_SESSION['userInfo']['id'];
        $aldData['create_time'] = date("Y-m-d H:i:s");
        foreach($data as $val){
            $id = D("Ad")->add($val);            
            $aldData['ad_id']       = $id;
            $res = D('AdLinkDemand')->add($aldData);
        }
        if($id){
            //$this->success('作品上传成功','');
            redirect('/Home/Production');
        }
    }
    
    /**
     * 图片上传获取图片尺寸
     * @author liuqiuhui
     */
    public function demandPicSize(){
        $demandId   = intval($_GET['demandId']);
        $demandInfo = D('Demand')->getDataById($demandId);
        if(!empty($demandInfo['size_id'])){
            $sizeList = D('AdSizeConfig')->getDataList(" id IN ($demandInfo[size_id])");
        }
        exit(json_encode($sizeList));       
    }  
    
    /**
     * 需求素材下载
     * @author liuqiuhui
     */ 
	public function downDemand(){
        $this->isLogin();
        $groupName = $_SESSION['userInfo']['group']['group_name'];
        if($groupName != '设计师' && $groupName){
            $this->error('抱歉，认证非设计师不能上传作品！',$this->deploy['CENTER_SERVER']);
            exit;
        }elseif($groupName != '设计师'){
            $this->error('请认证为设计师',$this->deploy['CENTER_SERVER'].'/Home/Index/userOrgApply/');
            exit;
        }
        $file = base64_decode($_GET['file']);
        $this->downloadFile($file);
	}
    
    /**
     * 采纳
     * @author liuqiuhui
     */ 
     
     /* $buyInfo['ad_id']          广告id
        $buyInfo['buyer_id']       购买者id
        $buyInfo['buyer_consume']  消费积分值
        $buyInfo['seller_id']      作者id
        $buyInfo['seller_harvest'] 作者积分提成：0
        $buyInfo['show_num']       购买次数：1
        $buyInfo['show_time']      购买时间：99999
        $buyInfo['status']         1
        $buyInfo['type']           3*/
    public function byAccept(){
        $map['user_id']   = intval($_GET['uid']);
        $map['demand_id'] = intval($_GET['demandId']);
        $uid              = $_SESSION['userInfo']['id'];
        if(!$uid){
            $res['status'] = '1';
            $res['msg']    = '请登录！';
        }else{            
            $maps['demand_id']   = $map['demand_id'];
            $maps['accept']      = 1;
            //判断是否已有采纳作品
            $isHave = D('AdLinkDemand')->getCount($maps);
            if(!$isHave){
                $save['accept']      = '1';
                $id      = D('AdLinkDemand')->where($map)->save($save);
                $acIdarr = D('AdLinkDemand')->where("demand_id = {$map[demand_id]} AND user_id = {$map[user_id]} AND accept = 1")->getField('ad_id',true);
                $acIdStr = implode(',',$acIdarr);
                $ids     = D('Ad')->where("id IN ({$acIdStr})")->save(array('user_id'=>$uid,'type'=>5));
                $adIdArr = D('AdLinkDemand')->where("demand_id = {$map[demand_id]} AND user_id != {$map[user_id]} AND accept != 1")->getField('ad_id',true);
                $adIdStr = implode(',',$adIdArr);
                $uids    = D('Ad')->where("id IN ({$adIdStr})")->save(array('type'=>4));
                foreach($adIdArr as $val){
                    $sizeId = D('Ad')->where("id = {$val}")->getField("size_id");
                    $price  = D('AdSizeConfig')->where("id = {$sizeId}")->getField('price');
                    D('Ad')->where("id = {$val}")->save(array('price'=>$price));
                }
                D('Demand')->where("id={$map[demand_id]}")->save(array('status'=>1));
                if($id){
                    $res['status']  = '3';
                    $res['msg']     = '采纳成功';
                    $this->userSendMessage(array(),$_GET['uid']);                
                    $data['uid']    = intval($_GET['uid']);
                    $data['points'] = D('Demand')->where(" id = {$map[demand_id]}")->getField('score');
                    $data['desc']   = '需求作品被采纳获得'.$data['score'].'积分';
                    $this->userAddScore('',$data);                
                }else{
                    $res['status'] = '2';
                    $res['msg']    = '采纳失败';
                }
            }else{
                $res['status'] = '4';
                $res['msg']    = '已有作品被采纳';
            }
        }
        exit(json_encode($res));
    }

}