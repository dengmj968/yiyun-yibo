<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * @desc    后台广告逻辑业务层
 * @author      liuqiuhui
 * @createdate  2014-11-19
 */
class AdController extends CommonController
{
	/**
	 * @desc 后台广告首页
	 **/
	public function index(){

        // 获取搜索参数
		$p = $_GET['p'] ? $_GET['p'] : 1;
        if(!is_numeric($_REQUEST["is_recommend"]) && empty($_REQUEST["is_recommend"])) $_REQUEST["is_recommend"] = 2;
        $id                   = intval($_REQUEST["id"]);
        $title                = filter_str($_REQUEST["title"]);
        $size_id              = $_REQUEST["size_id"];
        $keywords             = filter_str($_REQUEST["keywords"]);
        $keywords             = trim($keywords,',');
        $keywords             = trim($keywords,'，');
        $status               = intval($_REQUEST["status"]);
        $is_recommend         = intval($_REQUEST["is_recommend"]);
        $is_type              = intval($_REQUEST["type"]);
        // 搜索条件组合
        if ($id > 0) $map['id'] = $id;
        if (!empty($title)) $map['title'] = array('like', "%{$title}%");
        if (!empty($size_id)) $map['size_id'] = array('in', $size_id);
        if (!empty($keywords)) $map['keywords'] = array('like', "%{$keywords}%");
        if (!empty($status)) $map['status'] = $status;
        if ($is_recommend != 2) $map['is_recommend'] = $is_recommend;
        $map['type'] = 1;
        // 获取广告尺寸列表
        $adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
        // 引入分页
        import("Common.ORG.Page");
        // 取总页数
		$count = D($this->className)->getCount($map);
        // 设置每页显示条数
		$Page = new Page($count, 15);
        // 分页样式
		$show = $Page->show();
        // 分页数据
		$data =D($this->className)->getDataList($map, '' , '', $Page->firstRow . ',' . $Page->listRows);
		// 以url形式组合所有搜索条件
        foreach($map as $key=>$val) {
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }
		$this->assign('show',$show);
		$this->assign('data',$data);
        $this->assign('adSizeList',$adSizeList);
        $this->assign('map',$map);
        $this->assign('p',$p);
        $this->assign('size_id',$size_id);
		$this->display();
	}
	
	/**
	 * @desc 加载编辑审核页面
	 **/
	public function edit(){
	    // 以获取的广告id判断广告是否存在，如不存在则不能修改 
        $p  = intval($_GET['p']) ? intval($_GET['p']) : 1;       
        $adInfo = D($this->className)->getDataById($_GET['id']);
        if(!$adInfo){
            $this->error('您无此广告！');
        }
        // 如果前面判断为真则把字符串分隔为数据
        !empty($adInfo['area']) && $adInfo['area'] = explode(',', $adInfo['area']);
        // 按尺寸id获取尺寸
        $size = D('AdSizeConfig')->getDataById($adInfo['size_id']);
        $adInfo['picWidth'] = $size['width'];
        $adInfo['picHeight'] = $size['height'];
        // 获取区域列表
        $area = D("Area")->getAreaList(0);
        // 获取关键字列表 
        $categoryList = D('KeywordsCategory')->getDataList();
        // 获取尺寸列表
        $adSizeList   = D('AdSizeConfig')->getDataList('', '', 'width ASC,height ASC');
        // 获取开始及结束时间
        $adInfo['start_date'] = date('Y-m-d',$adInfo['start_date']);
        $adInfo['end_date'] = date('Y-m-d',$adInfo['end_date']);
        // 获取当前广告关键字循环判断关键字类型
        $keywords = explode(',', $adInfo['keywords']);
        foreach($keywords as $key => $val){
            $res = D("Keywords")->getDataByMap("value = '{$val}'");
            if(!$res){
                $this->assign('keyword_type', 'custom');
            }else{
                $this->assign('keyword_type', 'system');
            }
        }
        $this->assign('p',$p);
        $this->assign('area',$area);
        $this->assign('adSizeList',$adSizeList);
        $this->assign('categoryList',$categoryList);
        $this->assign('adInfo',$adInfo);
        $this->assign('keywords',$keywords);
        $this->display();
	}
	
	/**
	 * @desc Ajax获取图片大小
	 **/
	public function getPicSize(){
		$id = $_GET['id'];
        // 按id获取当前尺寸信息
		$size = D('AdSizeConfig')->getDataById($id);
		if($size['width'] > 600){
			$size['width'] = $size['width']/1.5;
			$size['height'] = $size['height']/1.5;
		}
		echo json_encode($size);
	}
	
	/**
	 * @desc 保存编辑审核
	 */
	public function saveEdit(){
        $id = intval($_POST['id']);
        $p  = intval($_POST['p']) ? intval($_POST['p']) : 1;
        // 判断广告区域是否为全国 如果为全国area为null 否则区域用，逗号隔开存入area
        if($_POST['areaType'] == 'all' || ( $_POST['areaType'] == 'custom' && count($_POST['area']) == 34 ) ){
            $_POST['area'] = null;
        }else{
            $_POST['area'] = join(',', $_POST['area']);
        }
        // 提交审核关键字
        $keyword_ids='';
        if($_POST['keywords']){
            $keywordList = explode( ',', $_POST['keywords'] );
            foreach($keywordList as $key => $val){
                //判断关键字是否存在于表里，如果不存在且不为空则入库
                $res = D("Keywords")->getIdByValue($val);
                if($res){
                    $keyword_ids .= $res . ',';
                }else{
                    //判断关键字是否为空
                    if($_POST[$val]){ //不为空入库
                        $keyData['category_id'] = $_POST[$val];
                        $keyData['value'] = $val;
                        $keyword_ids .= D('Keywords')->addData($keyData) . ',';
                    }else{ 
                        $this->error('请审核新增关键字！');
                        unset($keywordList[$key]);
                    }
                }
            }
        }
        // 当前广告关键字用逗号隔开的字符串
        $_POST['keywords'] = implode(',', $keywordList);
        // 当前广告所有关键字id用逗号连接的字符串
        $_POST['keyword_ids'] = trim($keyword_ids, ',');
        // 保存数据
		$res = D($this->className)->saveDataById($_POST);
        if($res){
            if(($_POST['type'] == 2 || $_POST['type'] == 4) && $_POST['status'] == 2 ){
                $data['uid']    = $_POST['user_id'];
                $data['points'] = D('AdSizeConfig')->where("id = $_POST[size_id]")->getField('price');
                $data['desc']   = '作品审核通过奖励'.$data['points'].'积分';
                $scoreKey = getScoreHistoryKey($data['uid'],strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id);
                $this->userAddScore($scoreKey,$data);
                $this->userSendMessage(array(),$_POST['user_id']);
            }
            $this->success('修改成功！','/Admin/Ad/index/p/'.$p.'/');
        }else{
            $this->error( D($this->className)->getLastError() );
        }
	}
	
	/**
	 * @desc 修改广告状态的AJAX方法 1-审核中 2-使用中 3-已停用
	 */
	public function changeStatus(){
		$status = intval($_GET['status']);
		$id = intval($_GET['id']);
		if($status == 2){         //点击 使用中 状态改为 已停用
			$data['status'] = 3;
		}elseif($status == 3){    //点击 已停用 状态改为 使用中
			$data['status'] = 2;
		}elseif($status == 1){    //点击 审核中 状态改为 使用中
			$data['status'] = 2;
		}
		$id = D($this->className)->where("id = {$id}")->save($data);
		if($id){
			echo $data['status'];
		}else{
			echo false;
		}		
	}
	
	/**
	 * @desc 修改广告是都推荐的AJAX方法 0-不推荐 1-推荐
	 */
	public function changeRecommend(){
		$recommend = intval($_GET['recommend']);
		$adId = intval($_GET['id']);
		if($recommend == 0){  // 点击 否 状态改为 是
			$data['is_recommend'] = 1;
		}elseif($recommend == 1){  // 点击 是 状态改为 否
			$data['is_recommend'] = 0;
		}
        //print_r($data);
		$id = D($this->className)->where("id = $adId")->save($data);
		if($id){
			echo $data['is_recommend'];
		}else{
			echo false;
		}
	}
	
	/**
	 * @desc ajax_关键字校验方法 
     * 对广告关键字时行校验，将不在库里的关键字以，(逗号)连接的字符串方式返
	 */
	public function addKeywords(){
		$keyword     = $_POST['keyword'];
		$keywordList = explode(',', $keyword);
		$data        = '';
		foreach($keywordList as $key=>$val){
            // 判断关键字是否存在于库里
			$id = D("Keywords")->getIdByValue($val);
			if(!$id){
				$data .= $val . ',';
			}
		}
		if($data){
			echo trim($data,',');
		}else{
			echo false;
		}
	}
	
	/**
	 * @desc ajax方法_获取关键字类别列表的
	 */
	public function getKeywordType(){
		$keywordTypeList = D('KeywordsCategory')->getDataList();
		echo json_encode($keywordTypeList);
	}
	
	
	/**
	 * @desc 加载广告缓存页面
	 */
	public function cache(){
		$this->display();
	}
	
	/**
	 * @desc 更新广告缓存的ajax方法
	 */
	public function updateAdCache(){
		D('Memcache')->updataAllAdCache();
		echo 1;
	}
	
	/**
	 * @desc 更新广告位缓存ajax方法
	 */
	public function updateSpaceCache(){
		D('Memcache')->updataAllUserAdPlaceCache();
		echo 1;
	}
	
	public function sync(){
		D('Memcache')->updateSync();
		echo 1;
	}

    /**
     * @desc 获取关键字Ajax
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
}
