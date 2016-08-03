<?php
namespace Common\Model;
/**
 * memcacheModel
 * mysql和memcache数据交互缓冲区
 * @author 宋小平
 */

class MemcacheModel{
	/**
	 * 全站配置
	 * @var array
	 */
	protected $deploy;
	//请求的IP
	protected $ip;
	//请求的地区ID
	protected $areaId;
	//请求的URL
	protected $fromUrl;
	
	/**
	 * 初始化信息
	 * @see Model::_initialize()
	 */
	protected function _initialize(){
		//获取加载全站配置表
		$deployList = M('deploy')->select();
		foreach($deployList as $key=>$val){
			$data[$val['key']] = $val['value'];
		}
		$this->deploy = $data;
		
		if( method_exists($this,'getAreaId') ){
			$this->getAreaId();
		}
		
		if( method_exists($this,'getFromUrl') ){
			$this->getFromUrl();
		}
	}
	
	/**
	 * 数据过滤方法，在这里无用(non-PHPdoc)
	 * @see BaseModel::parseData()
	 */
	public  function parseData(array $data){}
	
	/**
	 * 获取访问进来的链接地址，以便回跳
	 */
	protected function getFromUrl(){
		$this->fromUrl = $_SERVER['HTTP_REFERER'];
	}
	
	/**
	 * 获取请求来源的地区ID
	 */
	protected function getAreaId(){
		$this->ip = get_client_ip();
		$this->areaId = D("Ip")->GetAreaID( $this->ip );
	}
	
	/**
	 * 更新广告在memcache中的K,val数据
	 * 一次性获取所有广告数据，循环补全尺寸数据，然后缓存
	 * 过滤掉过期广告，并修改其状态变为3
	 */
	public function updateAllAdCache(){
		$map['status'] = array('in','2,3');
		$map['type'] = array('in','1,2,5');
		$adList = D('Ad')->getDataList($map,'id,pic,title,desc,url,keywords,size_id,area,size_id,start_date,end_date,user_id');
        foreach($adList as $key=>$val){
        	if( ($val['start_date'] > time()) || ($val['end_date'] < time()) ){
				$data['status'] = 3;
				D('Ad')->where("id = {$val['id']}")->save($data);
			}
        	$adSizeInfo = D('AdSizeConfig')->getDataById($val['size_id']);
        	$val['width'] = $adSizeInfo['width'];
        	$val['height'] = $adSizeInfo['height'];
			S($this->getCacheKey('ad',$val['id']),$val);
		}
	}
	
	/**
	 * 获取缓存的key;
	 * @param string $type
	 * @param int $id
	 * @return string;
	 */
	private function getCacheKey(string $type, $id){
		$id = intval($id);
		if(!$id){
			return false;
		}
		return 'yibo_'.$type.'_'.$id;
	}
	
	/**
	 * 根据ID获取一条广告（有缓存）
	 * @param int $id
	 * @return array
	 */
	public function getAdInfoById($id){
		$id = intval($id);
		$key = $this->getCacheKey('ad',$id);
		if( S($key) ){
			return S($key);
		}
		$adInfo = D('Ad')->getDataById($id,'id,pic,title,desc,url,keywords,area,size_id,start_date,end_date,user_id,type');
		if(!adInfo){
			$this->error = "广告不存在！";
			return false;
		}
		$adSizeInfo = D('AdSizeConfig')->getDataById($adInfo['size_id']);
		$adInfo['width'] = $adSizeInfo['width'];
		$adInfo['height'] = $adSizeInfo['height'];
		S($key,$adInfo);
		return $adInfo;
	}
	
	/**
	 * 删除一条广告缓存
	 * @param int $id
	 */
	public function delAdCacheById($id){
		$id = intval($id);
		$key = $this->getCacheKey('ad',$id);
		S($key,null);
	}
	
	/**
	 * 获取广告位信息并添加地区信息
	 * @param int $placeId 广告位ID
	 * @return array $map 广告位信息
	 */
	public function getPlaceMapById( $placeId ){
		$placeInfo = D('Place')->getPlaceById( $placeId );
		if(!$placeInfo || $placeInfo['status'] == 3){
			$this->error = "广告位已经停用或者不存在！";
			return false;
		}
		$placeInfo['ip'] = $this->ip;
		$placeInfo['areaId'] = $this->areaId;
		$map = $this->parsePlaceMap($placeInfo);
		return $map;
	}
	
	/**
	 * 广告位过滤信息，除去非匹配条件字段
	 * @param array $placeInfo 广告位信息
	 * @return array
	 */
	public function parsePlaceMap( $placeInfo ){
		foreach( $placeInfo as $key=>$val ){
			if($key == 'size_id' && $val){
				$map['size_id'] = $val;
			}
			if($key == 'keyword_ids' && $val){
				$map['keyword_ids'] = $val;
			}
			if($key == 'areaId' && $val){
				$map['areaId'] = $val;
			}
		}
		return $map;
	}
	
	/**
	 * 获取同一尺寸的所有合法广告
	 * @param int $sizeId 广告ID
	 */
	public function getAdListBySizeId($sizeId){
		$key = $this->getCacheKey('sizeAdList',$sizeId);
		if( S($key) ){
			return S($key);
		}
		$map['size_id'] = $sizeId;
		$map['status'] = 2;
		$map['type'] = array('in','1,2,5');
		$adList = D('Ad')->getDataList($map,'`id`,`size_id`,`area`,`keyword_ids`,`is_recommend`,`start_date`,`end_date`,`type`');
		foreach($adList as $k=>$v){
			foreach($v as $m=>$n){
				if(!$n)
					unset($adList[$k][$m]);
			}
		}
		if($adList){
			S($key,$adList);
			return $adList;
		}else{
			$this->error = "没有符合尺寸的广告！";
			return false;
		}
	}
	
	/**
	 * 获取同一尺寸的所有合法广告
	 * @param int $sizeId 广告ID
	 */
	public function getAdRecommendListBySizeId($sizeId){
		$key = $this->getCacheKey('sizeAdRecommendList',$sizeId);
		if( S($key) ){
			return S($key);
		}
		$map['size_id'] = $sizeId;
		$map['status'] = 2;
		$map['is_recommend'] = 1;
		$map['type'] = array('in','1,2,5');
		$adList = D('Ad')->getDataList($map,'`id`,`size_id`,`area`,`keyword_ids`,`is_recommend`,`status`,`start_date`,`end_date`,`type`');

		foreach($adList as $k=>$v){
			foreach($v as $m=>$n){
				if(!$n)
					unset($adList[$k][$m]);
			}
		}
		if($adList){
			S($key,$adList);
			return $adList;
		}else{
			$this->error = "没有符合尺寸的广告！";
			return false;
		}
	}
	
	/**
	 * 根据广告位map表递归获取广告IDs
	 * @param array $option
	 * return array 匹配的广告id数组
	 */
	public function getAdInfoBySpaceId( $placeId ){
		$map = $this->getPlaceMapById( $placeId );//获取广告位数据
		$num = rand(1,9);
		if($this->deploy['RECOMMEND'] && ( $this->deploy['RECOMMEND_RATIO'] > $num ) ){
			$adInfo = $this->getRecommendAdIds($map);
			if($adInfo){
				return $adInfo;
			}
		}
		$adList = $this->getAdListByMap($map);

		if( count($adList) < $this->deploy['AD_NUM'] ){
			if($map['keyword_ids']){
				unset($map['keyword_ids']);
				$adList = $this->getAdListByMap($map);
			}
		}

		$array_key = array_rand($adList,1);
		$adId = $adList[$array_key]['id'];
		return $this->getAdInfoById($adId);
	}
	
	/**
	 * 根据广告位提供的条件匹配广告
	 * @param array $map
	 * @return boolean|Ambigous <boolean, unknown, mixed, object, NULL>
	 */
	public function getAdListByMap( $map ){
		$BaseAdList = $this->getAdListBySizeId( $map['size_id'] );
		if(!BaseAdList){
			return false;
		}
		foreach($BaseAdList as $key=>$val){
			if( ($val['start_date'] > time()) || ($val['end_date'] < time()) ){
				$data['status'] = 3;
				D('Ad')->where("id = {$val['id']}")->save($data);
			}
			if( $val['area'] && $map['areaId'] && !in_array($map['areaId'],$val['area']) ){
				unset($BaseAdList[$key]);
			}
			if($map['keyword_ids'] && $val['keyword_ids']){
				$keywordIdList1 = explode(',',$map['keyword_ids']);
				$keywordIdList2 = explode(',',$val['keyword_ids']);
				if( !array_intersect($keywordIdList1,$keywordIdList2) ){
					unset($BaseAdList[$key]);
				}
			}
		}
		return $BaseAdList;
	}
	
	/**
	 * 获取推荐广告
	 * @param int $spaceId
	 */
	public function getRecommendAdIds($option){
		$recommendAdList = $this->getAdRecommendListBySizeId($option['size_id']);
		if(!$recommendAdList){
			return false;
		}
		$array_key = array_rand($recommendAdList,1);
		$adId = $recommendAdList[$array_key]['id'];
		return $this->getAdInfoById($adId);
	}
		
	
	/**
	 * 获取广告分发器
	 * @param int $spaceId
	 */
	public function getkeys($spaceId){		
		//判断推荐是否开启，没有开启就直接调取全部广告
		if( !$this->deploy['RECOMMEND'] ){
			return $this->getAdIdListBySpaceId($spaceId);
		}

		$num = rand(1,9);
		if( $this->deploy['RECOMMEND_RATIO'] > $num ){
			$keys = $this->getRecommendAdIds($spaceId);
			if($keys){
				return $keys;
			}else{
				return $this->getAdIdListBySpaceId($spaceId);
			}
		}else{
			return $this->getAdIdListBySpaceId($spaceId);
		}
	}
	
	/**
	 * 删除一条广告位缓存
	 * @param unknown_type $adId
	 */
	public function delPlaceCacheById($id){
		$id = intval($id);
		$key = $this->getCacheKey('space',$id);
		S($key,null);
	}
	
	/**
	 * 更新推荐广告缓存
	 */
	public function updateRecommendCache(){
		$sizeList = D('AdSizeConfig')->getDataList();
		foreach($sizeList as $key=>$val){
			$map['size_id'] = $val['id'];
			$map['is_recommend'] = 1;
			$adList = D('Ad')->getAdIdList($map,'id,pic,title,desc,url,keywords,size_id,user_id');
			if($adList){
				S($this->getCacheKey('recommendSpace',$val['id']),$adList);
			}
		}		
	}
	
	/**
	 * 根据专题ID随机获取一条专题数据
	 * @param int $projectId
	 */
	public function getProjectDetailInfo($projectId){
		$key = $this->getCacheKey('project',$projectId);
		if( S($key) ){
			$projectDetailList = S($key);
		}else{
			$projectDetailList = D("ProjectDetail")->getInfoByProjectId($projectId);
			S($key,$projectDetailList);
		}
		$detailKey = array_rand($projectDetailList);
		return $projectDetailList[$detailKey];
	}
	
	/**
	 * 获取单个用户信息
	 * @param int $uid
	 */
	public function getUserInfoById( $uid ){
		$key = $this->getCacheKey('user',$uid);
		if( S($key) ){
			return S($key);
		}
		$map['key'] = $this->deploy['USER_KEY'];
		$map['id'] = $uid;
		$data = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/DataApi/getUserInfoById",$map),true );
		S($key,$data[0]);
		return $data[0];
	}
	
	/**
	 * 更新本站当前登陆用户的session
	 */
	public function updateThisSession(){
		$uid = $_SESSION['userInfo']['id'];
		if(!$uid){
			return false;
		}
		$key = $this->getCacheKey('user',$uid);
		$map['key'] = $this->deploy['USER_KEY'];
		$map['id'] = $uid;
		$data = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/DataApi/getUserInfoById",$map),true );
		S($key,$data[0]);
		$_SESSION['userInfo'] = $data[0];
	}
	
	
	/**
	 * 更新统计数据缓存
	 */
	public function updateSync(){
		import("@.Entity.SpaceEntity");
		import("@.Entity.AdEntity");
		$date = date('Y-m-d');
		$spaceList = M('space_statistics')->where("`create_time`='{$date}'")->select();
		foreach($spaceList as $ke => $val){
			$spaceKey = $date.'-spaceStatistics-'.$val['space_id'];
			if(S($spaceKey)){
				$dataList = S($spaceKey);
				$data['show'] = $dataList->show;
				$data['click'] = $dataList->click;
				M('space_statistics')->where("space_id={$val['space_id']} AND create_time='{$date}'")->data($data)->save();
			}
			$adList = $this->getAdidsBySpaceId($val['space_id']);
			foreach($adList as $k=>$v){
				$adKey = $date.'-adStatistics-'.$v['id'];
				if(S($adKey)){
					$adData =  S($adKey);
					$data['show'] = $adData->show;
					$data['click'] = $adData->click;
					M('ad_statistics')->where("ad_id={$v['id']} AND create_time='{$date}'")->data($data)->save();
				}
			}
			
		}
	}
}