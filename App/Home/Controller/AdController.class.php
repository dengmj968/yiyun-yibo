<?php
namespace Home\Controller;
use Common\ORG\Page;
/**
 * @desc        前台广告管理业务逻辑
 * @author      lixiaoli,liuqiuhui
 * @createdate  2013-7-18
 */
class AdController extends CommonController{
	
	/**
	 * @desc 用户广告列表
	 */
	public function index(){
		$userInfo = $this->getUserInfo();        
		$map['user_id'] = $userInfo['id'];
        $map['type'] = array('in','1,5');
		import("Common.ORG.Page");
		$count =D($this->className)->getCount($map);
		$Page = new Page($count, 10);
		$show = $Page->show();
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
        
		$adList = D($this->className)->getDataList($map, '', '', $Page->firstRow . ',' . $Page->listRows);
		$webData = D("WebTitle")->getWebData(2);
		$this->assign("webData",$webData);
		$this->assign('show',$show);
		$this->assign('adList',$adList);
		$this->assign('p',$p);
		$this->display();
	}
	
	/**
	 * @desc 用户添加广告
	 */
	public function add(){
        $area = D("Area")->getAreaList(0);
		$categoryList = D('KeywordsCategory')->getDataList();
		$adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');
        $userInfo = $this->getUserInfo();
        $myUrl = $userInfo['extend']['my_url'];

		$this->assign('categoryList',$categoryList);
		$this->assign('area',$area);
		$this->assign('adSizeList',$adSizeList);
        $this->assign('myUrl',$myUrl);
		$this->display();
	}
	
	/**
	 * @desc   根据广告尺寸获取此广告尺寸的信息
	 * @param  $id
	 */
	public function getSizeInfoById($id){
		$sizeInfo = D("AdSizeConfig")->getDataById($id);
		exit(json_encode($sizeInfo));
	}
	
	/**
	 * @desc   根据广告尺寸获取此广告尺寸的信息 ajax
	 * @param  $id
	 */
	public function getSizeId(){
		$id = intval($_GET['id']);
		$sizeInfo = D("AdSizeConfig")->getDataById($id);
		exit(json_encode($sizeInfo));
	}
	
	/**
	 * @desc   获取所有的广告尺寸信息
	 */
	public function getSizeData(){
		$data = D("AdSizeConfig")->getDataList();
		exit(json_encode($data));
	}
	
	/**
	 * @desc   保存新增广告
	 */
	public function saveAdd(){
		$userInfo = $this->getUserInfo();
		if(!$userInfo['id'] || $userInfo['id'] == 0){
			$this->error('请重新登陆添加广告');
			exit;
		
		}
		$_POST['user_id'] = $userInfo['id'];

        if($_POST['areaType'] == 'all') unset($_POST['area']);
        if( $_POST['areaType'] == 'custom' && (count($_POST['area']) == 34 || $_POST['area'] == '') ) unset($_POST['area']);

        $id = null;

        if($_POST['uploadType'] == 'single'){
            /*$picInfo = getimagesize($_POST['pic']);
            $sizeInfo = D('AdSizeConfig')->getDataById($_POST['size_id']);

            if(($sizeInfo['width'] != $picInfo[0]) || $sizeInfo['height'] != $picInfo[1]){
                $this->error('您上传的图片长或宽不正确！');
            }*/

            $id = D($this->className)->addData($_POST);
        }

        if($_POST['uploadType'] == 'multi'){
            foreach($_POST['picArr'] as $key => $val){
                $data[$key] = $_POST;
                $data[$key]['pic'] = $val;
                $picInfo = getimagesize("./" . $val);
                $map['width'] = $picInfo[0];
                $map['height'] = $picInfo[1];

                $data[$key]['size_id'] = D('AdSizeConfig')->where($map)->getField('id');

                $id = D($this->className)->addData($data[$key]);
            }
        }

        if($id){
        	//添加广告积分,获取展示时间，获取展示区域
        	$diff_time_start = strtotime($_POST['start_date']);
        	$diff_time_end = strtotime($_POST['end_date']);
			$diff_days = floor(($diff_time_end -$diff_time_start)/86400) +1;
			$area_ratio = ($_POST['areaType'] == 'all') ? 5 : 1;
			$uid = $userInfo['id'];
			//$method = 'ad_saveadd';
			//$points = -intval($diff_days) * $area_ratio;
			//$desc = '这么多'.$points.'分数';
			//D('ScoreHistory')->setScoreByRule($method,'',$uid,'');
            $this->userAddScore();
            //$this->success('添加成功！','/Home/Ad/index');
            redirect('/Home/Ad/index');
        }else{
            $this->error( D($this->className)->getLastError() );
        }
	}
	
	/**
	 * @desc   编辑广告
	 */
	public function edit(){
		$map['id'] = intval($_GET['id']);
		$userInfo = $this->getUserInfo();
        $map['user_id'] = $userInfo['id'];
        $myUrl = $userInfo['extend']['my_url'];

		$adInfo = D($this->className)->getDataByMap($map);

		if(!$adInfo){
			$this->error('您无此广告！','Ad');
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

        $res = D($this->className)->saveDataById($_POST);

        if($res){
            //$this->success('修改成功！','/Home/Ad/index');
            redirect('/Home/Ad/index');
        }else{
            $this->error( D($this->className)->getLastError() );
        }
	}
	
	/**
	 * @desc   获取广告js代码接口
	 */
	public function getJsByAdId(){
		$id = intval($_GET['id']);
		$data = D('Memcache')->getAdInfoById($id);
		$url = C("SERVER_HOST");
		$rand = rand(1000,9999);
		//echo 11;
		$jsString = '<script type="text/javascript">var ad_id = '.$id.';</script><script type="text/javascript" src="'.D('Deploy')->getGlobal('SERVER_HOST').'/yibo.js?ad_id='.$id.'&random='.$rand.'"></script>';
		$this->assign('adData',$data);
		$this->assign('jsString',$jsString);
		$this->display();
	}
	
	/**
	 * @desc   获取图片大小Ajax方法
	 */
	public function getPicSize(){
		$id = $_GET['id'];
		$size = D('AdSizeConfig')->getDataById($id);
		if($size['width'] > 600){
			$size['width'] = $size['width']/1.5;
			$size['height'] = $size['height']/1.5;
		}
		echo json_encode($size);
	}

	/**
	 * @desc   获取关键字Ajax
	 */
	public function getKeywordsAjax(){
        $keywordsArr = explode(',', $_GET['keywords']);
		$map['category_id'] = intval($_GET['category_id']);
		$keywordList = D('Keywords')->getDataList($map);
		$keywordString = '';
		foreach($keywordList as $key=>$val){
            if(in_array($val['value'], $keywordsArr)){
                $keywordString .= "<label class='checkbox' style='font-size: 12px; width: 200px; display: inline-block; margin-bottom: 10px; margin-top: 10px;
                 color: red'>";
                $keywordString .= "<input type='checkbox' name='keywordSystem[]' value='{$val['value']}' checked='checked'>{$val['value']}";
            }else{
                $keywordString .= "<label class='checkbox' style='font-size: 12px; width: 200px; margin-bottom: 10px; margin-top: 10px; display: inline-block;'>";
                $keywordString .= "<input type='checkbox' name='keywordSystem[]' value='{$val['value']}'>{$val['value']}";
            }

            $keywordString .= "</label>";
		}

		echo $keywordString;
	}

    /**
     * @desc    广告状态修改ajax方法
     */
    public function changeStatus(){
        $status = intval($_GET['status']);
        $id = intval($_GET['id']);

        if($status == 2){
            $data['status'] = 3;
        }elseif($status == 3){
            $data['status'] = 2;
        }

        $id = D($this->className)->where("id = {$id}")->save($data);

        if($id){
            echo $data['status'];
        }else{
            echo false;
        }

    }

    public function delPicFile(){
        $file = $_GET['file'];
        $info = parse_url($file);
        $path = $info['path'];



        $file = '../..'.$path;

        echo $file;
        if(unlink($file)){
            echo 1;
        }else{
            echo 2;
        }
    }
	
	public function loadAd(){
		$ad_id=intval($_POST['ad_id']);
		$ads=D('Ad')->getDataById($ad_id);
		$url = C("SERVER_HOST");
		$rand = rand(1000,9999);
		if($ads){
			$codes = '<script type="text/javascript">var ad_id = '.$ad_id.';</script><script type="text/javascript" src="'.D('Deploy')->getGlobal('SERVER_HOST').'/yibo.js?ad_id='.$ad_id.'&random='.$rand.'"></script>';
			$size = D('AdSizeConfig')->getDataById($ads['size_id']);
			$ads['width'] = $size['width'];
			$ads['hosts'] = $host;
			$ads['codes'] = $codes;
			$this->ajaxReturn($ads,'我成功了',1);
		}

	}
    
    /**
     * 广告展示详情
     */ 
    public function adShowList(){
        $uid = $_SESSION['userInfo']['id'];
        
        if($_REQUEST['date']){
            $upMonth = $_REQUEST['date'];
        }else{
            $upMonth = 1;
        }
        $date = date("Ym",strtotime("-$upMonth month"));
        $name = date("Y",strtotime("-$upMonth month")).'年'.date("m",strtotime("-$upMonth month")).'月广告展示记录';
        $className = "st_ad_history_show_".$date;
        $map[$className.'.uid'] = $uid;
        $map['ad.title'] = array ('neq',"");
        if($_REQUEST['title']){
            $title = htmlspecialchars($_POST['title']);
            $map['ad.title'] = array ('like',"%$title%");
        }
        if($_REQUEST['size_id']){
            $map['ad.size_id'] = intval($_REQUEST['size_id']);
        }
        
        import("Common.ORG.Page"); 
		$count =M($className)->join("LEFT JOIN ad ON ad.id = $className.ad_id")->where($map)->Count($className.'.id');
		$Page = new Page($count, 7);
        // 以url形式组合所有搜索条件
        foreach($_POST as $key=>$val) {
            if($val){
                $Page->parameter   .=   "$key=".urlencode($val).'&';
            }            
        }
		$show = $Page->show();
		$p = isset($_GET['p']) ? $_GET['p'] : 1;
        $adList =  M($className)->field("$className.come_url,$className.create_time,ad.title,ad.size_id")->join("LEFT JOIN ad ON ad.id = $className.ad_id")->where($map)->limit( $Page->firstRow . ',' . $Page->listRows)->select();
         
        $adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');       
        $dateArr = array();
        for($i=1;$i<7;$i++){
            $dateArr[$i] = date("Ym",strtotime("-$i month"));
        }

        $this->assign('searchs',$_REQUEST);
        $this->assign('adSizeList',$adSizeList);
		$this->assign('show',$show);
		$this->assign('adList',$adList);
		$this->assign('p',$p);
        $this->assign('name',$name);
        $this->assign('dateArr',$dateArr);
        $this->display();
    }

    /**
     * @desc 显示图片
     */
    public function getPicById(){
        $id = intval($_GET['id'])?intval($_GET['id']):25;
        //调取默认广告
        $picInfo = D('AdSizeConfig')->field('pic,width,height,size_name')->find($id);
        //调取列表广告
        $map['size_id'] = $id;
        $map['status']  = 3;
        $map['type']    = 4;
        //根据尺寸定义图片的数量和类的属性
        if(in_array($id,array(25,18,2,19,6,17,1,10))){
            $arrInfo['type'] = 1;
            if($picInfo['height'] > 300){
                $arrInfo['sty'] = 300;
            }else{
                if($id == 19 ){
                    $arrInfo['sty'] = 222;
                }elseif($id == 6){
                    $arrInfo['sty'] = 160;
                }else{
                    $arrInfo['sty'] = $picInfo['height'];
                }
            }
        }elseif(in_array($id,array(24,20,14,21,23,8,9,35,13,54,16,3,4,5,7))){
            $arrInfo['type'] = 2;
            if($picInfo['width'] > 320){
                $arrInfo['sty'] = 320;
            }else{
                $arrInfo['sty'] = $picInfo['width'];
            }
        }
        $list = D('Ad')->getDataList($map,'',"create_time desc",10);
        $picInfo['adList'] = $list;
        $picInfo['type']   = $arrInfo['type'];
        $picInfo['sty']    = $arrInfo['sty'];
        echo json_encode($picInfo);
    }
}