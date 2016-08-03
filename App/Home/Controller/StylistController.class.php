<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Common\ORG\Page;
class StylistController extends BaseController{
	public $default_pic = '/Public/images/default.jpg';

    private function getUserScore($uid){
        $data['uid'] = $uid;
        $score = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/ScoreApi/getScoreByUid",$data),true );
		return $score;
    }
    
    /**
     * 获取用户名
     * @param int|array $info  用户id|用户信息
     * @return string|null  用户信息不存在返回NULL
     */
    private function getUsername($info){
        if (!is_array($info)){
            $info = D('Memcache')->getUserInfoById($info);
        }
        if (isset($info['extend']['true_name']) && $info['extend']['true_name']){
            return $info['extend']['true_name'];
        }else if(isset($info['extend']['user_name']) && $info['extend']['user_name']){
            return $info['extend']['user_name'];
        }else if (isset($info['email'])){
            return $info['email'];
        }  else {
            return NULL;
        }
    }
    
    /**
     * 获取作品评论
     * @param int $id 作品id
     */
    private function getAdcomment($id){
        //导入分页
		import("Common.ORG.Page");
        //评论
		$userCenter = $this->deploy['CENTER_SERVER'];
		$comments['ad_id'] = $id;
		$comments['type'] = 2;
		//获取评论数量
		$commentsNums = D('AdComment')->getCount($comments); 
		$Page = new Page($commentsNums,5);
        $Page->rollPage = 5;
		$show = $Page->show();
		$comments = D('AdComment')->getDataList($comments,'','`create_time` desc',$Page->firstRow . ',' . $Page->listRows);
		foreach($comments as $key => $val){
			if(!$val['author_id']){
				$comments[$key]['username'] = '游客';
				$comments[$key]['pic_small'] = $this->default_pic;	
				continue;
			}
			$commentsInfo = D('Memcache')->getUserInfoById($val['author_id']);
			$comments[$key]['username'] = $commentsInfo['extend']['user_name'] ?  $commentsInfo['extend']['user_name'] :  ($commentsInfo['extend']['true_name'] ? $commentsInfo['extend']['true_name'] : $commentsInfo['email']);
			$comments[$key]['pic_small'] = $commentsInfo['extend']['pic_small'] ?  $userCenter.$commentsInfo['extend']['pic_small'] :  $this->default_pic;
		}
        $this->assign('comments',$comments);
		$this->assign('commentsNums',$commentsNums);
		$this->assign('show',$show);
    }

    /**
     * 根据广告尺寸信息，获取相应展示模板
     * @param array $sizeInfo 作品尺寸信息
     * @return string  模板名
     */
    private function getTplNameBySize($sizeInfo){
        $width = $sizeInfo['width'];
        $height = $sizeInfo['height'];
        if ($sizeInfo['type']==1){ //404广告位
            $tplName = 'tpl404';
            $this->assign('is404','1');
        }
        else if($height<=280){ //上下结构，显示宽度不能大于640
            $tplName = 'tpl_1';
            if ($width>640){
                $height = intval((640/$width)*$height);
                $width = 640;
            }
        }
        else{      //左右结构，显示高度不能大于350
            $tplName = 'tpl_2';
            if ($height>350){
                $width = intval((350/$height)*$width);
                $height = 350;
            }
            $width2 = 600 - $width;
            $this->assign('width2',$width2);
        }
        
        $this->assign('width', $width);
        $this->assign('height', $height);
        return $tplName;
    }
    
    /**
	 * 设计师主页
	 */
	public function index(){
        $map = array();
        if ($this->deploy['STYLIST_AD']){
            $map['id'] = array('in',$this->deploy['STYLIST_AD']);
        }
		$styleInfo = D('Ad')->getDataList($map,'',"create_time desc",1);
		//判定宽高展示样式
		if(in_array($styleInfo[0]['size_id'],array(25,17,18,24,2))){
			$li_length = 1;
			$getNum = 7;
		}elseif(in_array($styleInfo[0]['size_id'],array(1,19,10,6))){
			$li_length = 2;
			$getNum = 5;
		}elseif(in_array($styleInfo[0]['size_id'],array(7,14,20,21))){
			$li_length = 4;
			$getNum = 4;
		}elseif(in_array($styleInfo[0]['size_id'],array(8,9,36,23))){
			$li_length = 3;
			$getNum = 3;
		}elseif(in_array($styleInfo[0]['size_id'],array(13,16,3))){
			$li_length = 5;
			$getNum = 4;
		}elseif(in_array($styleInfo[0]['size_id'],array(4,5))){
			$li_length = 6;
			$getNum = 2;
		}
		$styList = D('Ad')->getDataList($map,'',"create_time desc",$getNum);
		$lastAd = count($styList);
 		foreach($styList as $key=>$val){
			$sizes = D('AdSizeConfig')->getDataById($val['size_id']);
			$styList[$key]['width'] = $sizes['width'];
			$styList[$key]['height'] = $sizes['height'];
		}
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('stylist');
        $this->assign('banner', $banner);
        
		$this->assign('styList',$styList);
		$this->assign('lastAd',$lastAd);
		$this->assign('li_length',$li_length);
		$this->display();
	}
    
    public function detail(){
        $id = intval($_GET['id']); //作品id
        $proInfo = D('Ad')->getDataById($id);
        
        switch ($proInfo['type'].$proInfo['status']){
            case 22: //已经赞助
                $this->supportInfo($proInfo);
                break;
            
            case 23: //待赞助
                if (empty($_SESSION['userInfo'])){
                    $this->error('请先登录！',$this->deploy['CENTER_SERVER'].'/Login');
                    exit;
                }
                $this->support($proInfo);
                break;
            
            case 42: // 可以购买
            case 43: // 可以购买
                if (empty($_SESSION['userInfo'])){
                    $this->error('请先登录！',$this->deploy['CENTER_SERVER'].'/Login');
                    exit;
                }
                $this->buyAd($proInfo);
                break;
            
            case 52://已售
            case 53://已售
                $this->buyInfo($proInfo);
                break;
            
            default : 
                $this->error('你访问的页面不存在！','');
                exit;
        }
    }

    /**
	 * 已售出展示页
	 */
    private function buyInfo(&$proInfo){
        $userCenter = $this->deploy['CENTER_SERVER'];
        
        //广告尺寸信息，根据广告尺寸显示相应的模板
        $sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        $proInfo['size_name'] = $sizeInfo['size_name'];
        $tplName = $this->getTplNameBySize($sizeInfo);
        
        //作者信息(默认为拥有者)，作品已出售，作者id应为 ad_buy.seller_id
        $proInfo['username'] = $this->getUsername($proInfo['user_id']);
        $proInfo['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$proInfo['user_id'];
        
        $buy = array();//购买者信息
        $userInfo = D('Memcache')->getUserInfoById($proInfo['user_id']);
        $buy['user'] = $this->getUsername($userInfo);
        if ($userInfo['extend']['pic']){
            $buy['photo'] = $userCenter.$userInfo['extend']['pic'];
        }else{
            $buy['photo'] = $userCenter.'/Public/image/default.jpg';
        }
        $buy['user_desc'] = msubstr($userInfo['extend']['desc'], 0, 26);
        $buy['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$proInfo['user_id'];
        $buy['my_url'] = trim($userInfo['extend']['my_url']);
        $buy['my_url2'] = substr(trim($userInfo['extend']['my_url']),7,15);
        $map['ad_id'] = $proInfo['id'];
        $map['type'] = array('in', '2,3'); //2：购买，3：认购
        $info = D('AdBuy')->where($map)->order('id DESC')->find(); //获取作品的购买信息
        if (!empty($info)){
            //作者信息
            $proInfo['username'] = $this->getUsername($info['seller_id']);
            $proInfo['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$info['seller_id'];
        }
        $this->assign('buy', $buy);
        
        //作品列表
        $map2['type'] = array('in','2,4');
        $map2['status'] = array('in',array(2,3));
        $proList = D('Ad')->getDataList($map2,'id,title,likes,type,status','create_time desc','10');

        foreach ($proList as &$v){
            $v['tit'] = msubstr($v['title'],0,15);
            $v['url'] = '/Home/Stylist/detail/id/'.$v['id'];
        }
        $this->assign('proList', $proList);

        //作品评论
		$this->getAdcomment($proInfo['id']);

        $this->assign('showType', 4);
        $this->assign('data', $proInfo);
        layout('Stylist/buy'); //布局模板
		$this->display($tplName);
	}
    
    /**
	 * 已赞助展示页面
	 */
	private function supportInfo(&$proInfo){
        $userCenter = $this->deploy['CENTER_SERVER'];
        
        //广告尺寸信息，根据广告尺寸显示相应的模板
        $sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        $proInfo['size_name'] = $sizeInfo['size_name'];
        $tplName = $this->getTplNameBySize($sizeInfo);
        
        //作者信息
        $proInfo['username'] = $this->getUsername($proInfo['user_id']);
        $proInfo['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$proInfo['user_id'];
        
        $map['ad_id'] = $proInfo['id'];
        $map['type'] = 1;
        $map['status'] = 1;
        $info = D('AdBuy')->where($map)->order('id DESC')->find(); //获取作品当时的赞助信息
        $userInfo = D('Memcache')->getUserInfoById($info['buyer_id']);
        $buy = array();//赞助方信息
        $buy['user'] = $this->getUsername($userInfo);
        if ($userInfo['extend']['pic']){
            $buy['photo'] = $userCenter.$userInfo['extend']['pic'];
        }else{
            $buy['photo'] = $userCenter.'/Public/image/default.jpg';
        }
        $url = trim($info['url']);
        if (strpos($url, 'http://') ===false){
            $buy['my_url'] = 'http://'.$url;
            if (strlen($url)>14){
                $buy['my_url2'] = substr($url,0,13);
                $buy['my_url2'] .= '…';
            }else{
                $buy['my_url2'] = $url;
            }
        }else{
            $buy['my_url'] = $url;
            if (strlen($url)>21){
                $buy['my_url2'] = substr($url,7,13);
                $buy['my_url2'] .= '…';
            }else{
                $buy['my_url2'] = substr($url,7);
            }
        }
        $buy['user_desc'] = msubstr($userInfo['extend']['desc'], 0, 26);
        $buy['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$info['buyer_id'];
        $buy['start_date'] = date('Y-m-d H:i', $info['start_time']);
        $buy['end_date'] = date('Y-m-d H:i', $info['end_time']);
        $this->assign('buy', $buy);
        
        //作品列表
        $map2['type'] = array('in','2,4');
        $map2['status'] = array('in',array(2,3));
        $proList = D('Ad')->getDataList($map2,'id,title,likes,type','create_time desc','10');

        foreach ($proList as &$v){
            $v['tit'] = msubstr($v['title'],0,15);
            $v['url'] = '/Home/Stylist/detail/id/'.$v['id'];
        }
        $this->assign('proList', $proList);
        
        //作品评论
		$this->getAdcomment($proInfo['id']);

        $this->assign('showType', 1);
        $this->assign('scoreRuleUrl', $userCenter.'/ScoreHistory/scoreRule');
        $this->assign('data', $proInfo);
        layout('Stylist/buy'); //布局模板
		$this->display($tplName);
	}
    
    /**
	 * 赞助页
	 */
	private function support(&$proInfo){
        $userCenter = $this->deploy['CENTER_SERVER'];
        
        //广告尺寸信息，根据广告尺寸显示相应的模板
        $sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        $proInfo['size_name'] = $sizeInfo['size_name'];
        $tplName = $this->getTplNameBySize($sizeInfo);
        
        //作者信息(默认为拥有者)，作品已出售，作者id应为 ad_buy.seller_id
        $proInfo['username'] = $this->getUsername($proInfo['user_id']);
        $proInfo['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$proInfo['user_id'];
        
        //赞助人信息
        $uInfo = D('Memcache')->getUserInfoById($_SESSION['userInfo']['id']);
        if ($uInfo['extend']['my_url'] !='' && $uInfo['extend']['my_url'] != 'http://'){
            $this->assign('goUrl', trim($uInfo['extend']['my_url']));
        }
        
        //赞助人总积分
        if (isset($_SESSION['userInfo']['score']['score'])){
            $scoreTotal = $_SESSION['userInfo']['score']['score'];
        }else{
            $row = $this->getUserScore($_SESSION['userInfo']['id']);
            $scoreTotal = isset($row['score']) ? $row['score'] : 0; //用户总积分
            $_SESSION['userInfo']['score']['score'] = $scoreTotal;
        }
        $scoreOneDay = $sizeInfo['score']; //展示一天所需积分
        $days = floor($scoreTotal/$scoreOneDay); //可展示的天数
        $this->assign('scoreTotal', $scoreTotal);
        $this->assign('scoreOneDay', $scoreOneDay);
        $this->assign('days', $days);
        
        //作品评论
		$this->getAdcomment($proInfo['id']);

        //防止重复提交
        $buycode = mt_rand(0,1000000);
        $this->assign('buycode', $buycode);
        $this->assign('scoreTotal', $scoreTotal);
        $this->assign('showType', 2);
        $this->assign('scoreRuleUrl', $userCenter.'/Login/Public/scoreRule');
        $this->assign('data', $proInfo);
        layout('Stylist/buy'); //布局模板
		$this->display($tplName);
	}
    
    /**
	 * 购买页
	 */
	private function buyAd(&$proInfo){
        $userCenter = $this->deploy['CENTER_SERVER'];
        
        //广告尺寸信息，根据广告尺寸显示相应的模板
        $sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        $proInfo['size_name'] = $sizeInfo['size_name'];
        $tplName = $this->getTplNameBySize($sizeInfo);
        
        //作者信息(默认为拥有者)，作品已出售，作者id应为 ad_buy.seller_id
        $proInfo['username'] = $this->getUsername($proInfo['user_id']);
        $proInfo['homePage'] = $userCenter.'/Home/HomePage/index/uid/'.$proInfo['user_id'];
        
        //购买人总积分
        if (isset($_SESSION['userInfo']['score']['score'])){
            $scoreTotal = $_SESSION['userInfo']['score']['score'];
        }else{
            $row = $this->getUserScore($_SESSION['userInfo']['id']);
            $scoreTotal = isset($row['score']) ? $row['score'] : 0; //用户总积分
            $_SESSION['userInfo']['score']['score'] = $scoreTotal;
        }
        
        //作品评论
		$this->getAdcomment($proInfo['id']);

        //防止重复提交
        $buycode = mt_rand(0,1000000);
        $this->assign('buycode', $buycode);
        $this->assign('scoreTotal', $scoreTotal);
        $this->assign('showType', 3);
        $this->assign('scoreRuleUrl', $userCenter.'/Login/Public/scoreRule');
        $this->assign('data', $proInfo);
        layout('Stylist/buy'); //布局模板
		$this->display($tplName);
	}

    /**
     * 赞助提交
     * @author 邓明倦
     */
    public function saveBuy(){
        $id = intval($_POST['id']); //作品id
        $proInfo = D('Ad')->getDataById($id);
        
        //只能赞助类别为作品的广告且状态为停用的广告
        if (empty($proInfo) || $proInfo['type']!=2 || $proInfo['status']!=3){
            $this->error('你访问的页面不存在！','');
            exit;
        }
        
        if (empty($_SESSION['userInfo'])){
            $this->error('请先登录！',$this->deploy['CENTER_SERVER'].'/Login');
            exit;
        }
        
        if(isset($_POST['buycode'])) {
            if($_POST['buycode'] == $_SESSION['buycode']){
                $this->error('请不要重复提交！','/Stylist/detail/id/'.$id);
                exit;
            }
        }
        
        $_SESSION['buycode'] = $_POST['buycode'];
        
        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $scoreTotal = isset($row['score']) ? $row['score'] : 0; //用户总积分
                
        $sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        
        $scoreOneDay = $sizeInfo['score']; //展示一天所需积分
        $dayNum = intval($_POST['day_num']); //展示天数
        $score = $dayNum*$scoreOneDay; //需消耗的积分
        if ($score > $scoreTotal){
            $this->error('您的积分不够！','Stylist/detail/id/'.$id);
            exit;
        }
        
        
        $scoreData = array();
                
        $scoreToSeller = ceil($score*$this->deploy['RATIO_SELLER']); //设计师获得积分
        //echo $scoreToSeller;exit;
        
        //赞助广告扣除积分
        $scoreData[] = array('uid'=>$_SESSION['userInfo']['id'],'score'=>'-'.$score,'from'=>'2','desc'=>"赞助广告消耗{$score}积分");
        //广告作者获得积分
        $scoreData[] = array('uid'=>$proInfo['user_id'],'score'=>$scoreToSeller,'from'=>'2','desc'=>"广告被他人赞助获得{$scoreToSeller}积分");

        
        $data['ad_id'] = $proInfo['id'];
        $data['buyer_id'] = $_SESSION['userInfo']['id'];
        $data['buyer_consume'] = $score;
        $data['seller_id'] = $proInfo['user_id'];
        $data['seller_harvest'] = $scoreToSeller;
        $data['show_time'] = $dayNum; //购买天数
        $data['url'] = $_POST['url'];
        $data['logo'] = $_POST['logo'];
        $data['type'] = 1; //交易类别为赞助
        $data['create_time'] = date('Y-m-d H:i:s');
        
        //根据购买时间，和展示天数计算展示时间。从第二天0点开始展示
        $data['start_time'] = strtotime(date('Y-m-d',time()+86400));//展示开始时间，第二天0点
        $data['end_time'] = $data['start_time'] + $data['show_time']*86400-1;//展示结束时间
        
        $ids = D('AdBuy')->addData($data);
        //echo D('AdBuy')->getLastSql();exit;
        if ($ids){
            $data2 = array();
            if(strpos($data['url'], 'http://') === false){
                $data2['url'] = 'http://'.$data['url'];
            }else{
                $data2['url'] = $data['url'];
            }
            
            $data2['start_date'] = $data['start_time'];
            $data2['end_date'] = $data['end_time'];
            $data2['status'] = 2; //作品已经赞助，变成使用中
            
            //赞助交易信息插入成功，则更新积分数据
            $res = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/ScoreApi/setAdScore",$scoreData),true );
            if (empty($res) || $res['status']!=4){ //积分数据更新失败
                $datas['status'] = 0; //交易失败
                //D('AdBuy')->where('id='.$ids)->save($datas); //
                D('AdBuy')->where('id='.$ids)->delete();
                $msg = '赞助失败';
                $errorMsg = array(2=>'无法更新积分。',3=>'积分余额不足。',5=>'积分支付失败。',6=>'积分更新失败。');
                if (isset($res['status']) && isset($errorMsg[$res['status']])){
                    $msg = '赞助失败!'.$errorMsg[$res['status']];
                }
                
                $this->error($msg);
            }  else {
                D('Ad')->where('id='.$proInfo['id'])->save($data2);
                $_SESSION['userInfo']['score']['score'] = $scoreTotal - $score;
                $this->userSendMessage(array(),$proInfo['user_id']); //给作者发送消息
                $this->success('赞助成功','Stylist/productionWall');
            }
        }else{
            $this->error('赞助失败，'.D('AdBuy')->getLastError());
        }
        
    }
    
    /*
     * 购买提交
     * @author 邓明倦
     */
    public function saveBuyProduction(){
        $id = intval($_GET['id']); //作品id
        $proInfo = D('Ad')->getDataById($id);
        
        $userCenter = $this->deploy['CENTER_SERVER'];
        
        //检查作品类别、状态检查
        if (empty($proInfo) || $proInfo['type']!=4 || !in_array($proInfo['status'],array(2,3))){
            $this->error('你访问的页面不存在！','');
            exit;
        }
        
        if (empty($_SESSION['userInfo'])){
            $this->error('请先登录！',$userCenter.'/Login');
            exit;
        }
        
        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $scoreTotal = isset($row['score']) ? $row['score'] : 0; //用户总积分
        $price = $proInfo['price'];//作品价格
        //$sizeInfo = D('AdSizeConfig')->getDataById($proInfo['size_id']);
        
        if ($price > $scoreTotal){
            $this->error('您的积分不够！','Stylist/detail/id/'.$id);
            exit;
        }
        
        $scoreData = array();
        
        $scoreToSeller = $price; //设计师获得积分
        
        //购买广告扣除积分
        $scoreData[] = array('uid'=>$_SESSION['userInfo']['id'],'score'=>'-'.$scoreToSeller,'from'=>'2','desc'=>"购买广告消耗{$scoreToSeller}积分");
        //广告作者获得积分
        $scoreData[] = array('uid'=>$proInfo['user_id'],'score'=>$scoreToSeller,'from'=>'2','desc'=>"广告出售获得{$scoreToSeller}积分");
        
        
        $data['ad_id'] = $proInfo['id'];
        $data['buyer_id'] = $_SESSION['userInfo']['id'];
        $data['buyer_consume'] = $scoreToSeller;
        $data['seller_id'] = $proInfo['user_id'];
        $data['seller_harvest'] = $scoreToSeller;
        $data['show_time'] = 99999; //购买不需要指定天数。因为空值无法插入，故使用不可能的天数
        $data['status'] = 1; 
        $data['type'] = 2; //交易类别为购买
        $data['create_time'] = date('Y-m-d H:i:s');
        
        $ids = D('AdBuy')->addData($data);
        
        if ($ids){
            $data2 = array();
            
            $data2['type'] = 5; //修改广告类型
            $data2['user_id'] = $_SESSION['userInfo']['id']; //变更拥有者
            //$data2['status'] = 2; //作品已经赞助，变成使用中
            
            //交易数据插入成功，则更新积分数据
            $res = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/ScoreApi/setAdScore",$scoreData),true );
            if (empty($res) || $res['status']!=4){ //积分数据更新失败
                $datas['status'] = 0; //交易失败
                //D('AdBuy')->where('id='.$ids)->save($datas); //
                D('AdBuy')->where('id='.$ids)->delete();
                $msg = '购买失败';
                $errorMsg = array(2=>'无法支付积分。',3=>'积分余额不足。',5=>'积分支付失败。',6=>'积分更新失败。');
                if (isset($res['status']) && isset($errorMsg[$res['status']])){
                    $msg = '购买失败!'.$errorMsg[$res['status']];
                }
                $this->error($msg);
            }  else {
                D('Ad')->where('id='.$proInfo['id'])->save($data2);
                $_SESSION['userInfo']['score']['score'] = $scoreTotal - $scoreToSeller;
                $this->userSendMessage(array(),$proInfo['user_id']); //给作者发送消息
                $this->success('购买成功，请完善广告信息','Stylist/addDemandProduction/adId/'.$proInfo['id']);
            }
        }else{
            $this->error('购买失败，'.D('AdBuy')->getLastError());
        }
        
    }

    /**
	 * 作品墙
	 */
	public function productionWall(){
		$searchs['title'] = $_POST['title'] ? htmlspecialchars($_POST['title']) : '';
		$keywordsCategoryList = D('KeywordsCategory')->getDataList();
    	$sizeTypeList = D('AdSizeConfig')->getDataList('','',"`width` asc");
		$size_id = 10;
		if(isset($_POST['size_id'])){
			$size_id = intval($_POST['size_id']);
		}
		$searchs['size_id'] = $size_id;
    	$defaultSizeInfo = D('AdSizeConfig')->getDataById($size_id);
		$this->assign('defaultSizeInfo',$defaultSizeInfo);
		$this->assign('sizeTypeList',$sizeTypeList);
		$this->assign('keywordsCategoryList',$keywordsCategoryList);
		$this->assign('searchs',$searchs);
		$this->display();
	}
	
	/**
     * 瀑布流加载广告
     */ 
	public function getAdList(){
		$sizeId = intval($_GET['size_id']);
		$page   = intval($_GET['page']);
		//$title  = $this->lib_replace_end_tag($_GET['title']);
		$title  = htmlspecialchars($_GET['title']);
		$title  = str_replace( "'", "", $title);
    	$where  = "where (status = 3 or status = 2) and (type in (2,4,5)) ";
		if(isset($sizeId) && $sizeId != ''){
			$where .= " and size_id = ".intval($sizeId);
		}
 		if(isset($title) && $title != ''){
			$where .= " and title like '%".$title."%'";
		}
		$adList = null;
    	$page = $page?$page:1;
    	$first = ($page-1)*15;
    	$where .= " limit ".$first.",15";
    	$sql = "select * from ad ".$where;
		$adList = M()->query($sql);
   		foreach($adList as $key => $val){
			$sizes = D('AdSizeConfig')->getDataById($val['size_id']);
			$adList[$key]['sql'] = $sql;
			$adList[$key]['yiboUrl'] = $this->deploy['YIBO_YU'];
   			$adList[$key]['width'] = $sizes['width'];
   			$adList[$key]['height'] = $sizes['height'];
   		}
		if(!$sizeId){
			shuffle($adList);
		}
		echo json_encode($adList);
		exit;
    }
    
   /**
	* 弹窗
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
		$id = intval($_GET['id']);
		$size_id = intval($_GET['size_id']);
		$adInfo = D('Production')->getDataById($id);
		$size = D('AdSizeConfig')->getDataById($adInfo['size_id']);
		 if($size['width'] > 930){
			$nw = 930;
			$nh = floor(($nw*$size['height'])/$size['width']);
			$adInfo['widths'] = $nw;
			$adInfo['heights'] = $nh;
		}else{
			$adInfo['widths']  = $size['width'];
			$adInfo['heights'] = $size['height'];
		}
		$adInfo['size_name'] = $size['size_name'];
		$aduserInfo = D('Memcache')->getUserInfoById($adInfo['uid']);
		$adInfo['username'] = $aduserInfo['extend']['user_name'] ? $aduserInfo['extend']['user_name'] : $aduserInfo['email'];
		$adInfo['pic_small'] = $aduserInfo['extend']['pic_small'] ? $userCenter.$aduserInfo['extend']['pic_small'] : $this->default_pic;
		$uid = $_SESSION['userInfo']['id'] ? $_SESSION['userInfo']['id'] : 0;		
		//获取评论信息,type-1 普通广告评论，type-2设计师广告评论
		$comments['ad_id'] = $id;
		$comments['type'] = 2;
		$comments = D('AdComment')->getDataList($comments,'','`create_time` desc');
		foreach($comments as $key => $val){
			if(!$val['author_id']){
				$comments[$key]['username'] = '游客';
				$comments[$key]['pic_small'] = $this->default_pic;	
				continue;
			}
			$commentsInfo = D('Memcache')->getUserInfoById($val['author_id']);
			$comments[$key]['username'] = $commentsInfo['extend']['user_name'] ?  $commentsInfo['extend']['user_name'] :  $commentsInfo['email'];
			$comments[$key]['pic_small'] = $commentsInfo['extend']['pic_small'] ?  $userCenter.$commentsInfo['extend']['pic_small'] :  $this->default_pic;
		}

		//获取评论数量
		$commentsNums = M('ad_comment')->where('ad_id = '.$id)->count(); 
		$this->assign('comments',$comments);
		$this->assign('commentsNums',$commentsNums);
		$this->assign('adInfo',$adInfo);
		$this->assign('show',$show);
		$this->assign('yiboCenter',$yiboCenter);
		$this->assign('userCenter',$userCenter);
		$this->display();
	}
    
    /**
     * 作品版权说明
     */ 
    public function copyright(){
        $this->display();
    }
    
    /**
     * 注册用户协议
     */ 
    public function protocol(){
        $this->display();
    }

    /**
     * 需求列表
     */
	public function demandList(){
        import("Common.ORG.Page");
        
        // 设置搜索条件
        $title = isset($_GET['title']) ? filter_str($_GET['title']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $map = array();
        if (!empty($title)) $map['title'] = array('like', "{$title}%");
        if ($status != -1){
            $map['status'] = $status;
        }
        
        //如果用户登录，优先显示他发布的需求
        if(isset($_SESSION['userInfo']['id'])){
            $uid = $_SESSION['userInfo']['id'];
            $order = "FIND_IN_SET(uid,{$uid}) DESC,id DESC";
        }else{
            $uid = 0;
            $order = 'id DESC';
        }
        
		$count = D("Demand")->getCount($map);
		$Page  = new Page($count, 10);
        $Page->rollPage = 5; //分页栏每页显示的页数
		$show  = $Page->show();
		$data  = D("Demand")->getDataList($map, '', $order, $Page->firstRow.','.$Page->listRows);
        foreach($data as &$v){
            $v['username'] = D("Demand")->getUsername($v['uid']);
            $v['time'] = substr($v['create_time'], 0, -3);
            $v['tit'] = msubstr($v['title'], 0, 24);
            $info = D('Memcache')->getUserInfoById($v['uid']);
            if ($info['extend']['pic_small']){
                $v['userPic'] = $this->deploy['CENTER_SERVER'].$info['extend']['pic_small'];
            }else{
                $v['userPic'] = '/Public/images/default.jpg';
            }
            $v['userUrl'] = $this->deploy['CENTER_SERVER'].'/Home/HomePage/index/uid/'.$v['uid'];
            $sql = "SELECT COUNT(DISTINCT user_id) as total FROM `ad_link_demand` WHERE demand_id=".$v['id'];
            $res = D('AdLinkDemand')->query($sql);
            $v['reply'] = $res[0]['total'];
            
            //如果需求发布人为当前登录的用户，并且该需求还没上传作品就可编辑
            $v['self'] = 0;
            $v['allowEdit'] = 0; //不允许修改
            if ($v['uid'] == $uid){
                $v['self'] = 1; //允许修改
                if (!$v['status']){
                    $v['allowEdit'] = 1; //允许修改
                }
            }
            
        }
        
        //作品列表
        $map2['type'] = array('in','2,4');
        $map2['status'] = array('in',array(2,3));
        $proList = D('Ad')->getDataList($map2,'id,title,likes,type,status','create_time desc','10');
        
        foreach ($proList as &$val){
            $val['tit'] = msubstr($val['title'],0,15);
            $val['url'] = "/Stylist/detail/id/".$val['id'];
        }
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('demand_list');
        $this->assign('banner', $banner);
        
        $this->assign('count',$count);
        $this->assign('title',$title);
        $this->assign('status',$status);
        $this->assign('page',$show);
        $this->assign('data',$data);
        $this->assign('proList',$proList);
		$this->display();
	}
        
    public function productiondemand(){
        $sizeList = D('AdSizeConfig')->getDataList('','id,`price`,`size_name`,`score`');
        //$total = count($sizeList);
//        if ($total%2 !=0 ){
//            $sizeList[$total] = array();
//        }
        $this->assign('sizeList', $sizeList);
        $this->display();
    }

    /**
	 *  点赞
	 */
	public function addLikes(){
		$uid = $_SESSION['userInfo']['id'] ? $_SESSION['userInfo']['id'] : 0;
		if(!$_COOKIE['click_zan'.$_POST['id']]){
			setcookie('click_zan'.$_POST['id'],time(),time()+180);
		}else{
			$this->ajaxReturn(1,'该广告你已经点过赞了',0);
		} 
		$key = 
		$id = intval($_POST['id']);
		$adInfo = D('Ad')->getDataById($id);
		$likes = $adInfo['likes']+1;
		$maps['likes'] = $likes;
		$maps['id'] = $id;
		$maps['status'] = $adInfo['status'];
		$up_id = D('Ad')->saveDataById($maps);
		if($up_id){
			if($uid){
				$userKey = getScoreHistoryKey($uid,strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id,date('Y-m-d'),time());
				$this->userAddScore($userKey);
			}
			$this->ajaxReturn($maps,'点赞成功',1);
		}	
	}
	/**
	 * 发表评论
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
		$uid = $_SESSION['userInfo']['id'] ? $_SESSION['userInfo']['id'] : 0 ;
		$pic_small = $_SESSION['userInfo']['extend']['pic_small'] ? $userCenter.$_SESSION['userInfo']['extend']['pic_small'] : $this->default_pic;
		$username = $_SESSION['userInfo']['extend']['user_name'] ? $_SESSION['userInfo']['extend']['user_name'] :($_SESSION['userInfo']['extend']['true_name'] ? $_SESSION['userInfo']['extend']['true_name'] : $_SESSION['userInfo']['email']);
		$_POST['author_id'] = $uid;
		$insert_id = D('AdComment')->addData($_POST);
		$times = date('Y-m-d H:i:s',time());
		if($insert_id){
			if($uid){
				$userKey = getScoreHistoryKey($uid,strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id,date('Y-m-d'),time());
				 $this->userAddScore($userKey); //添加积分
			}
			$tpl .= '<div class="stylist_discuss_list clearfix">';
			$tpl .= '<div class="discuss_part clearfix">';
			$tpl .= '<div class="user_photo">';
			$tpl .= '<a href="javascript:;"><img src="'.$pic_small.'" width="48" height="48" /></a></div>';
			$tpl .= '<div class="discuss_con">';
			$tpl .= '<h3>'.$username.'<span>'.$times.'</span></h3>';
			$tpl .= '<p>'.htmlspecialchars($_POST['content']).'</p>';
			$tpl .= '</div></div>';
			$this->ajaxReturn($tpl,1,1);
		}
	}
    
    public function ajaxGetUserScore(){
        D('Memcache')->updateThisSession();
        echo intval($_SESSION['userInfo']['score']['score']);
    }
    
        
    /**
	 * @desc 需求广告具体信息填写
     * @author liuqiuhui
     */
    public function addDemandProduction(){
        
        //广告
        $adId = intval($_GET['adId']);
        $demandId = intval($_GET['demandId']);
        $area = D("Area")->getAreaList(0);
		$categoryList = D('KeywordsCategory')->getDataList();
		$adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');
        $myUrl = $userInfo['extend']['my_url'];
        $leftDemandList   = D("Demand")->getDataList('', '', 'id DESC','0,10');
        
        $this->assign('demandList',$leftDemandList);
        $this->assign('categoryList',$categoryList);
		$this->assign('area',$area);
		$this->assign('adSizeList',$adSizeList);
        $this->assign('myUrl',$myUrl);
        $this->assign('demandId',$demandId);
        $this->assign('adId',$adId);
    	$this->display();
    }
    
    /**
	 * @desc 需求广告或购买的广告具体信息填写保存
     * @author liuqiuhui
	 */
	public function saveDemandAd(){
        $ad_id = intval($_POST['ad_id']); //广告id
        $demand_id = intval($_POST['demand_id']); //需求id
        $acIdStr = '';
        
        //购买完善广告
        if ($ad_id)
        {
            $adInfo = D('Ad')->getDataById($ad_id);
            if ($adInfo){
                if($adInfo['user_id'] != $_SESSION['userInfo']['id']){
                    $this->error('该广告不是您的，您无法修改！');
                }
                $acIdStr = $ad_id;
            }
        }
        
        //认购完善广告
        if ($demand_id){
            $acIdarr = D('AdLinkDemand')->where("demand_id = {$demand_id} AND accept = 1")->getField('ad_id',true);
            if ($acIdarr){
                $acIdStr = implode(',',$acIdarr);
                $userid  = D('Ad')->where("id IN ({$acIdStr})")->getField('user_id');
                if($userid != $_SESSION['userInfo']['id']){
                    $this->error('该广告不是您的，您无法修改');
                }
            }
        }

        if ($acIdStr == ''){
            $this->error('您要修改的广告信息不存在！');
        }
        
        if(count($_POST['area']) == 34){
            $_POST['area'] = null;
        }else{
            $_POST['area'] = join(',', $_POST['area']);
        }       
        $_POST['start_date'] = strtotime($_POST['start_date']);
        $_POST['end_date']   = strtotime($_POST['end_date']);
        $_POST['status']     = 2;
        $_POST['title']      = htmlspecialchars($_POST['title']);
        $_POST['desc']       = htmlspecialchars($_POST['desc']);
        $_POST['keywords']   = htmlspecialchars($_POST['keywords']);
        $_POST['url']        = htmlspecialchars($_POST['url']);
        $ids     = D('Ad')->where("id IN ({$acIdStr})")->save($_POST);
        if($ids){
            $this->success('详细信息已补充完整！','/Home/Ad/index');
        }else{
            $this->error( D('Ad')->getLastError() );
        }
	}
	
}
