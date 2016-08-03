<?php
namespace Admin\Controller;
/**
 * 益播统计数据展示类
 * @author 宋小平
 * @version 1.0
 * create Time 2014-10-14
 */
class StatisticsController extends CommonController{
	/**
	 * 广告基本信息统计
	 */
	public function ad(){
		$ad['sum'] = D('Ad')->getCount();
		$ad['usable'] = D('Ad')->getCount(array('status'=>2));
		$ad['noCheck'] = D('Ad')->getCount(array('status'=>1));
		$ad['stop'] = D('Ad')->getCount(array('status'=>3));
		$adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
		foreach($adSizeList as $key=>$val){
			$adSizeNameList[] = $val['size_name'];
			$adSizeCountList[] = D('Ad')->getCount(array('size_id'=>$val['id']));
		}
		$adSizeName = implode(',',$adSizeNameList);
		$adSizeCount = implode(',',$adSizeCountList);

		$this->assign('ad',$ad);
		$this->assign('adSizeName',$adSizeName);
		$this->assign('adSizeCount',$adSizeCount);
		$this->display();
	}
	
	/**
	 * 广告年数据统计
	 * @param int adId get 广告ID
	 * @param int uid get 广告所有者ID
	 * @param string date get 想要获取的日期，默认本年
	 */
	public function adStatisticsMonth(){
		if($_GET['adId']){
			$map['ad_id'] = intval($_GET['adId']);
			$adInfo = D('Memcache')->getAdInfoById($map['ad_id']);
			$this->assign('adInfo',$adInfo);
		}
		if($_GET['uid']){
			$map['uid'] = intval($_GET['uid']);
		}
		$map['date'] = $_GET['date'] ? intval($_GET['date']) : date('Y');

 		$adMonthShowList = $this->getAdMonthList($map,'show');
 		foreach($adMonthShowList as $key=>$val){
 			$adShowSum[] = $val['sum'] ? $val['sum'] : 0;
 			$ad['adShowCount']['sum'] += $val['sum'];
 			foreach($val['area_id_map'] as $k=>$v){
 				$ad['adShowCount']['area_id_map'][$k] +=$v;
 			}
 		}
 		
 		//地区排序
 		unset($ad['adShowCount']['area_id_map'][0]);
 		arsort($ad['adShowCount']['area_id_map']);
 		$ad['maxAreaShow'] = current($ad['adShowCount']['area_id_map']) ? current($ad['adShowCount']['area_id_map']) :
 		$ad['adShowCount']['sum'];
 		$ad['thisYearShow'] = implode(',',$adShowSum);
		$adMonthClickList = $this->getAdMonthList($map,'click');
		foreach($adMonthClickList as $key=>$val){
			$adClickSum[] = $val['sum'] ? $val['sum'] : 0;
			$ad['adClickCount']['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$ad['adClickCount']['area_id_map'][$k] +=$v;
			}
		}
		//地区排序
		unset($ad['adClickCount']['area_id_map'][0]);
		arsort($ad['adClickCount']['area_id_map']);
		$ad['maxAreaClick'] = current($ad['adClickCount']['area_id_map']);
		$ad['thisYearClick'] = implode(',',$adClickSum);
		$this->assign('map',$map);
		$this->assign('ad',$ad);
		$this->display();
	}
	
	/**
	 * 获取普通广告的日展示数据
	 * 按照每天的日期，循环取出日表里面的点击和展示量
	 * @param int adId get 广告ID
	 * @param int uid get 广告所有者ID
	 * @param string date get 想要获取的日期，默认本年
	 */
	public function adStatisticsDay(){
		if($_GET['adId']){
			$map['ad_id'] = intval($_GET['adId']);
			$adInfo = D('Memcache')->getAdInfoById($map['ad_id']);
			$this->assign('adInfo',$adInfo);
		}
		if($_GET['uid']){
			$map['uid'] = intval($_GET['uid']);
		}
		
		$map['date'] = $_GET['date'] ? $_GET['date'] : date('Y-m');
		$lastDate = date('t',strtotime($map['date']));//该月最后一天日期
		for($i=1;$i<=$lastDate;$i++){
			if($i<$lastDate){
				$dateString .="'".$i."',";
			}else{
				$dateString .="'".$i."'";
			}
			
			if($map['ad_id']){
				$maps['ad_id'] = $map['ad_id'];
			}
			if($map['uid']){
				$maps['uid'] = $map['uid'];
			}
			$maps['create_time'] = $map['date'].'-'.$i;
			
			//循环获取单天展示数据
			$data = D('GetStatistics')->getAdDayStatistics($maps,'show');
			//echo D('GetStatistics')->getlastsql();
			//echo "<br />";
			$ad['adShowCount']['sum'] += $data['sum'];
			foreach($data['area_id_map'] as $key=>$val){			
				$ad['adShowCount']['area_id_map'][$key] +=$val;
			}
			$adShowDaySum[] = $data['sum'] ? $data['sum'] : 0;
			
			//循环获取单天点击数据
			$data = D('GetStatistics')->getAdDayStatistics($maps,'click');
			$ad['adClickCount']['sum'] += $data['sum'];
			foreach($data['area_id_map'] as $key=>$val){
				$ad['adClickCount']['area_id_map'][$key] +=$val;
			}
			$adClickDaySum[] = $data['sum'] ? $data['sum'] : 0;
		}
		arsort($ad['adShowCount']['area_id_map']);
		$ad['maxAreaShow'] = current($ad['adShowCount']['area_id_map']) ? current($ad['adShowCount']['area_id_map']) :
		$ad['adShowCount']['sum'];
		
		$ad['adShowDaySum'] = implode(',',$adShowDaySum);
		$ad['adClickDaySum'] = implode(',',$adClickDaySum);
		$this->assign('dateString',$dateString);
		$this->assign('ad',$ad);
		$this->assign('map',$map);
		$this->display();
	}
	
	/**
	 * 站长广告位统计数据展示
	 */
	public function place(){
		$map['status'] = 2;
		$place['sum'] = D('Place')->getCount($map);
		
		$map['uid'] = array('GT',0);
		$place['uid'] = D('Place')->getCount($map);
		$map['uid'] = 0;
		$place['noUid'] = D('Place')->getCount($map);
		
		$adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
		foreach($adSizeList as $key=>$val){
			$adSizeCountList[] = D('Place')->getCount(array('size_id'=>$val['id']));
		}
		//dump($adSizeCountList);

		//$adSizeName = implode(',',$adSizeNameList);
		$adSizeCount = implode(',',$adSizeCountList);
		
		//$this->assign('adSizeName',$adSizeName);
		$this->assign('adSizeCount',$adSizeCount);
		$this->assign('place',$place);
		$this->display();
	}
	
	/**
	 * 404广告位单月的每一天的数据展示
	 * 默认展示当前月
	 * @param int uid get 广告位所有者ID
	 * @param int placeId get 广告位ID
	 * @param int projectId get 专题ID  
	 */
	public function ad404StatisticsDay(){
		if($_GET['uid']){
			$map['uid'] = intval($_GET['uid']);
		}
		if($_GET['placeId']){
			$map['place_id'] = intval($_GET['placeId']);
		}
		$projectId = $_GET['projectId'] ? $_GET['projectId'] : 0; 
		$date = $_GET['date'] ? $_GET['date'] : date('Y-m');
		$lastDate = date('t',strtotime($map['date']));
		for($i=1;$i<=$lastDate;$i++){
			
			//拼装提起字符串，用于柱状图的X轴数据
			if($i<$lastDate){
				$dateString .="'".$i."',";
			}else{
				$dateString .="'".$i."'";
			}
				
			$map['create_time'] = $date.'-'.$i;
			$data = D('GetStatistics')->get404DayStatistics($map,'show');
			//dump($data);
			$ad['adShowCount']['sum'] += $data['sum'];
			foreach($data['area_id_map'] as $key=>$val){
				$ad['adShowCount']['area_id_map'][$key] +=$val;
			}
			if($projectId){
				$adShowDaySum[] = $data['project_id_map'][$projectId] ? $data['project_id_map'][$projectId] : 0;
			}else{
				$adShowDaySum[] = $data['sum'] ? $data['sum'] : 0;
			}
			$data = D('GetStatistics')->get404DayStatistics($map,'click');
			$ad['adClickCount']['sum'] += $data['sum'];
			foreach($data['area_id_map'] as $key=>$val){
				$ad['adClickCount']['area_id_map'][$key] +=$val;
			}
			
			if($projectId){
				$adClickDaySum[] = $data['project_id_map'][$projectId] ? $data['project_id_map'][$projectId] : 0;
			}else{
				$adClickDaySum[] = $data['sum'] ? $data['sum'] : 0;
			}
		}
		//dump($adShowDaySum);
		arsort($ad['adShowCount']['area_id_map']);
		$ad['maxAreaShow'] = current($ad['adShowCount']['area_id_map']) ? current($ad['adShowCount']['area_id_map']) :
		$ad['adShowCount']['sum'];
		$ad['adShowCountSum'] = array_sum($adShowDaySum);
		$ad['adClickCountSum'] = array_sum($adClickDaySum);
		$ad['adShowDaySum'] = implode(',',$adShowDaySum);
		$ad['adClickDaySum'] = implode(',',$adClickDaySum);
		$this->assign('dateString',$dateString);
		$this->assign('ad',$ad);
		$this->assign('map',$map);
		$this->assign('date',$date);
		$this->display();
	}
	
	/**
	 * 404广告位月数据展示
	 * @param int place_id get 广告位ID
	 * @param int uid get 所有者ID
	 * @param string date 日期，只有年份,默认本年
	 */
	public function ad404StatisticsMonth(){
		if($_GET['place_id']){
			$map['place_id'] = intval($_GET['place_id']);
		}
		if($_GET['uid']){
			$map['uid'] = intval($_GET['uid']);
		}
	
		$map['date'] = $_GET['date'] ? intval($_GET['date']) : date('Y');
	
		$adMonthShowList = $this->getAd404MonthList($map,'show');
		foreach($adMonthShowList as $key=>$val){
			$adShowSum[] = $val['sum'] ? $val['sum'] : 0;
			$ad['adShowCount']['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$ad['adShowCount']['area_id_map'][$k] +=$v;
			}
		}
			
		//地区排序
		unset($ad['adShowCount']['area_id_map'][0]);
		arsort($ad['adShowCount']['area_id_map']);
		$ad['maxAreaShow'] = current($ad['adShowCount']['area_id_map']) ? current($ad['adShowCount']['area_id_map']) :
		$ad['adShowCount']['sum'];
		$ad['thisYearShow'] = implode(',',$adShowSum);
		$adMonthClickList = $this->getAd404MonthList($map,'click');
		foreach($adMonthClickList as $key=>$val){
			$adClickSum[] = $val['sum'] ? $val['sum'] : 0;
			$ad['adClickCount']['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$ad['adClickCount']['area_id_map'][$k] +=$v;
			}
		}
		//地区排序
		unset($ad['adClickCount']['area_id_map'][0]);
		arsort($ad['adClickCount']['area_id_map']);
		$ad['maxAreaClick'] = current($ad['adClickCount']['area_id_map']);
		$ad['thisYearClick'] = implode(',',$adClickSum);
		$this->assign('map',$map);
		$this->assign('ad',$ad);
		$this->display();
	}
	
	/**
	 * 404广告位数据统计
	 */
	public function place404(){
		$map['status'] = 2;
		$place['sum'] = D('Place404')->getCount($map);
		
		$map['uid'] = array('GT',0);
		$place['uid'] = D('Place404')->getCount($map);
		$map['uid'] = 0;
		$place['noUid'] = D('Place404')->getCount($map);
		
		$adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
		foreach($adSizeList as $key=>$val){
			$adSizeCountList[] = D('Place')->getCount(array('size_id'=>$val['id']));
		}
		$adSizeCount = implode(',',$adSizeCountList);
		
		
 		$thisYear = intval(date('Y'));		
		for( $i=1; $i < 13; $i++ ){
			$startDate = $thisYear.'-'.$i.'-01';
			$endDate = $thisYear.'-'.$i.'-31';
			$maps['create_time'] = array('between',array($startDate,$endDate));
			$place404Sum = D('Place404')->getCount($maps);
			$place404List[] = $place404Sum ? $place404Sum : 0;
		}
		$place404 = implode(',',$place404List);
		//$this->assign('adSizeName',$adSizeName);
		$this->assign('adSizeCount',$adSizeCount);
		$this->assign('place404',$place404);
		$this->assign('place',$place);
		$this->display();
	}
	
	/**
	 * 获取普通广告本月统计数据
	 * 由于本月未完，所以月数据只能通过日表统计
	 * @param string $map 数据地图
	 * @param string $type show/click
	 */
	public function getDataThisMonth($map,$type){
		$map['create_time'] = array('GT',date('Y-m-00'));
		$data = D('GetStatistics')->getAdDayStatistics($map,$type);
		return $data;
	}
	
	/**
	 * 获取404广告位本月数据
	 * @param string $map 数据地图
	 * @param string $type show/click
	 */
	public function get404DataThisMonth($map,$type){
		$map['create_time'] = array('GT',date('Y-m-00'));
		$data = D('GetStatistics')->get404DayStatistics($map,$type);
		return $data;
	}
	
	/**
	 * 获取广告位展示量的前20名数据
	 * 由于公用KEY较多，会导致数据不正确
	 * 暂用方法---待更新
	 */
	public function placeSumTop(){
		$date = $_GET['date'] ? $_GET['date'] : date('Y-m-d',strtotime("-1 day"));
		$tableName = 'st_404_statistics_show_day_2014';
		$where = ' place_id>0 and ';
		if($date){
			$where .= " create_time = '{$date}'";
		}
		$where = $where ? $where : 1;
		$sql = "SELECT place_id,sum(`sum`) as place_sum
				FROM {$tableName}
				WHERE {$where}
				GROUP BY place_id
				ORDER BY place_sum desc
				LIMIT 20
		";
		$placeSumList = M()->query($sql);
		foreach($placeSumList as $key=>$val){
			$placeSumList[$key]['placeInfo'] = D('Place404')->getDataByMap(array('list_id'=>$val['place_id']));
			$placeSumList[$key]['placeTotal'] = D('Place404')->getCount(array('list_id'=>$val['place_id']));
		}
		//dump($placeSumList);
		$this->assign('placeSumList',$placeSumList);
		$this->display();
	}
	
	public function place404Distribution($id){
		$id = (int)$id;
		$placeList = D('Place404')->getDataList(array('list_id'=>$id),'','',20);
		$this->assign('placeList',$placeList);
		//dump($placeList);
		$this->display();
	}
	
	/**
	 * 获取广告单年每个月的统计数据
	 * 包含没有完成的这个月
	 * @param array $map
	 * @param string $type
	 * @return array
	 */
	public function getAdMonthList($map,$type){
		$thisYear = intval(date('Y'));
		if($map['date'] && ($thisYear != $map['date']) ){
			$endDate = $map['date'].'-12-00';
		}else{
			$endDate = date('Y-m-0',strtotime('last month'));
		}
		$dateList = explode('-',$endDate);

		for( $i=1; $i <= intval($dateList[1]); $i++ ){
			$date = $dateList[0].'-'.$i.'-00';
			unset($map['date']);
			$map['create_time'] = $date;
			$monthInfo = D('GetStatistics')->getAdMonthStatistics($map,$type);
			//echo D('GetStatistics')->getlastsql();
			$adMonthList[] = $monthInfo;
		}
		if(count($adMonthList) != 12){
			//$data = implode(',',$adMonthList);
			if($map['ad_id']){
				$maps['ad_id'] = $map['ad_id'];
			}
			if($map['uid']){
				$maps['uid'] = $map['uid'];
			}
			$adMonthList[] = $this->getDataThisMonth($maps,$type);
		}
		return $adMonthList;
	}
	
	/**
	 * 获取每个月的统计数据
	 * @param string map 数据地图
	 * @param string $type show/click
	 */
	public function getAd404MonthList($map,$type){
		$thisYear = intval(date('Y'));
		if($map['date'] && ($thisYear != $map['date']) ){
			$endDate = $map['date'].'-12-00';
		}else{
			$endDate = date('Y-m-0',strtotime('last month'));
		}
		$dateList = explode('-',$endDate);
	
		for( $i=1; $i <= intval($dateList[1]); $i++ ){
			$date = $dateList[0].'-'.$i.'-00';
			unset($map['date']);
			$map['create_time'] = $date;
			$monthInfo = D('GetStatistics')->get404MonthStatistics($map,$type);
			//echo D('GetStatistics')->getlastsql();
			$adMonthList[] = $monthInfo;
		}
		if(count($adMonthList) != 12){
			//$data = implode(',',$adMonthList);
			if($map['place_id']){
				$maps['place_id'] = $map['place_id'];
			}
			if($map['uid']){
				$maps['uid'] = $map['uid'];
			}
			$adMonthList[] = $this->get404DataThisMonth($maps,$type);
		}
		return $adMonthList;
	}
	
	/**
	 * 基于广告尺寸统计的单月数据（整合）
	 * @param string date 要统计的月份，默认上个月
	 */
	public function statisticsAdBySizeId(){
		$date = $_GET['date'] ? $_GET['date'].'-00' : date('Y-m-00',strtotime('last month'));
		$adSizeList = D('AdSizeConfig')->getDataList('','`id`,`size_name`','`width` ASC,`height` ASC');
		foreach($adSizeList as $key=>$val){
			$adSizeNameList[$val['id']] = $val['size_name'];
			$adList = D('Ad')->getDataList(array('size_id'=>$val['id'],'status'=>2),'`id`');
			foreach($adList as $k=>$v){
				$map['ad_id'] = $v['id'];
				$map['create_time'] = $date;
				//$adSumInfo = D('GetStatistics')->getAdMonthStatisticsSum($map);
				$adSumInfo = D('GetStatistics')->getAdMonthStatistics($map);
				$adSizeSumList[$val['id']] += $adSumInfo['sum'];
			}
		}
		//dump($adSizeSumList);
		$adSizeSum = implode(',',$adSizeSumList);
		$this->assign('date',$date);
		$this->assign('adSizeSum',$adSizeSum);
		$this->assign('adSizeNameList',$adSizeNameList);
		$this->display();
	}
}