<?php
namespace Common\Model;
class GetStatisticsModel extends BaseModel{
	//统计表模型
	private $modelAdStatisticsTableName = "st_ad_statistics";
	
	//单广告单月数据表
	private $modelAdStatisticsShowMonthTableName = "ad_statistics_show_month";
	
	//日期开始时间
	private $startDate = '2013-08-00';
	protected $trueTableName = 'ad_statistics';
	public function parseData(array $data){
		return $data;
	}
	
	/**
	 * 根据日期，用户ID，广告ID获取单天广告统计数据（相关表格为单天统计年表）
	 * 每条广告每天产生一条数据，表名结构：ad_statistics_show/click_2013(年份)
	 * 不支持跨年查询
	 * @param array $map 查询条件
	 * @param string $type 展示/点击
	 * @param string $fields 字段
	 * @param string $order 排序
	 * @param string $limit 截取
	 * @return array
	 */
	public function getAdDayStatisticsList($map,$type,$fields=null,$order=null,$limit=null){
		$type = $type ? $type : 'show';
		//获取表年份
		if(!$map['create_time'] || !is_string($map['create_time'])){
			$year = $map['date'] ? intval($map['date']) : date('Y');
			unset($map['date']);
		}else{
			$year = intval( $map['create_time'] );
		}
		$tableName = $this->modelAdStatisticsTableName.'_'.$type.'_day_'.$year;
		if ( strtotime($map['create_time']) > time() || !$this->isHaveTable($tableName) ){
			$this->error = "没有该日期的数据！";
			return false;
		}
        
//		$this->trueTableName = $tableName;
//		$data = $this->getDataList($map,$fields,$order,$limit);
        $data = array();
		foreach($data as $key=>$val){
			$data[$key] = $this->un_serialize($val);
		}
		return $data;
	}
	
	/**
	 * 普通广告的日统计方法
	 * 根据日期，用户ID，广告ID获取单天广告展示/点击量（相关表格为单天统计年表）
	 * 在获取数组后，会将数据统计成为一条数据，广告位和区域进行累加，用广告位ID，和区域ID做键
	 * @param array $map
	 * @param string $type
	 */
	public function getAdDayStatistics($map,$type){
		$field = $map['ad_id'] ? "`ad_id`,`uid`,`sum`,`area_id_map`,`place_id_map`" : "`sum`,`area_id_map`,`place_id_map`";
		$data = $this->getAdDayStatisticsList($map,$type,$field);
		if(!$data){
			return false;
		}
		foreach($data as $key=>$val){
			$statisticsData['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$statisticsData['area_id_map'][$k] +=$v;
			}
			foreach($val['place_id_map'] as $k=>$v){
				$statisticsData['place_id_map'][$k] +=$v;
			}
		}
		if($map['ad_id']){
			$statisticsData['uid'] = $data[0]['uid'];
			$statisticsData['ad_id'] = $data[0]['ad_id'];
		}
		return $statisticsData;
	}
	
	/**
	 * 已经完成月份数据获取
	 * @param array $map
	 * @param string $fields
	 * @param string $order
	 * @param string $limit
	 * @return array
	 */
	public function getAdMonthStatisticsList($map,$type,$fields=null,$order=null,$limit=null){
		$dateList = explode('-',$map['create_time']);
		if($dateList[2] != 00){
			$map['create_time'] = date( 'Y-m-00',strtotime( trim($map['create_time']) ) );
		}
		$type = $type ? $type : 'show';
		$this->trueTableName = "st_ad_statistics_{$type}_month";
		$data = $this->table($this->trueTableName)->getDataList($map,$fields,$order,$limit);

		foreach($data as $key=>$val){
			$data[$key] = $this->un_serialize($val);
		}
		return $data;
	}
	
	/**
	 * 广告月统计
	 * @param array $map
	 * @param string $type
	 */
	public function getAdMonthStatistics($map,$type){
		$field = $map['ad_id'] ? "`uid`,`ad_id`,`sum`,`place_id_map`,`area_id_map`" : "`sum`,`place_id_map`,`area_id_map`";
		$data = $this->getAdMonthStatisticsList($map,$type,$field);
		if(!$data){
			return false;
		}

		foreach($data as $key=>$val){
			$statisticsData['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$statisticsData['area_id_map'][$k] +=$v;
			}
			foreach($val['place_id_map'] as $k=>$v){
				$statisticsData['place_id_map'][$k] +=$v;
			}
		}
		
		if($map['ad_id']){
			$statisticsData['uid'] = $data[0]['uid'];
			$statisticsData['ad_id'] = $data[0]['ad_id'];
		}

		return $statisticsData;
	}
	
	/**
	 * 获取404日统计数据
	 * @param array $map
	 * @param string $type
	 * @param string $fields
	 * @param string $order
	 * @param string $limit
	 */
	public function get404DayStatistics($map,$type,$fields=null,$order=null,$limit=null){
		$year = $map[create_time] ? date('Y',strtotime($map['create_time'])) : date('Y');
		$type = $type ? $type : 'show';
		
		$tableName = 'st_404_statistics_'.$type.'_day_'.$year;
		$this->trueTableName = $tableName;
		$data = $this->getDataList($map,$fields,$order,$limit);
		foreach($data as $key=>$val){
			$data[$key] = $this->un_serialize($val);
		}
		
		foreach($data as $key=>$val){
			$statisticsData['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$statisticsData['area_id_map'][$k] +=$v;
			}
			foreach($val['project_id_map'] as $k=>$v){
				$statisticsData['project_id_map'][$k] +=$v;
			}
		}
		
		return $statisticsData;
	}
	
	/**
	 * 获取404月展示数据，然后统计计算
	 * @param array $map
	 * @param string $type
	 * @return boolean|unknown
	 */
	public function get404MonthStatistics($map,$type){
		$field = $map['ad_id'] ? "`uid`,`place_id`,`sum`,`project_id_map`,`area_id_map`" : "`sum`,`project_id_map`,`area_id_map`";
		$data = $this->get404MonthList($map,$type,$field);
		if(!$data){
			return false;
		}
	
		foreach($data as $key=>$val){
			$statisticsData['sum'] += $val['sum'];
			foreach($val['area_id_map'] as $k=>$v){
				$statisticsData['area_id_map'][$k] +=$v;
			}
			foreach($val['place_id_map'] as $k=>$v){
				$statisticsData['place_id_map'][$k] +=$v;
			}
		}
	
		if($map['ad_id']){
			$statisticsData['uid'] = $data[0]['uid'];
			$statisticsData['ad_id'] = $data[0]['ad_id'];
		}
		return $statisticsData;
	}
	
	/**
	 * 已经完成月份数据获取
	 * @param array $map
	 * @param string $fields
	 * @param string $order
	 * @param string $limit
	 * @return array
	 */
	public function get404MonthList($map,$type,$fields=null,$order=null,$limit=null){
		$dateList = explode('-',$map['create_time']);
		if($dateList[2] != 00){
			$map['create_time'] = date( 'Y-m-00',strtotime( trim($map['create_time']) ) );
		}
		$type = $type ? $type : 'show';
		$this->trueTableName = "st_404_statistics_{$type}_month";
		$data = $this->getDataList($map,$fields,$order,$limit);
		foreach($data as $key=>$val){
			$data[$key] = $this->un_serialize($val);
		}
		return $data;
	}
	
	public function getAdStatisticsSumById($id,$type='show'){
		$map['ad_id'] = $id;
		$data = $this->getAdMonthStatisticsList($map,$type);
		if(!$data){
			return false;
		}
		foreach($data as $key=>$val){
			$statisticsData['sum'] += $val['sum'];
		}
		$statisticsData['uid'] = $data[0]['uid'];
		$statisticsData['create_time'] = $data[0]['create_time'];
		return $statisticsData['sum'];
	}
	
	/**
	 * 判断数据表是否存在
	 * @param string $tableName
	 */
	private function isHaveTable($tableName){
//		$sql = "select * from {$tableName} where false";
//		$result = $this->query($sql);
//		if( is_array($result) ){
//			return true;
//		}else{
//			return false;
//		}
        return false;
	}
	
	/**
	 * 获取日期区间的所有月份
	 * @param string $startDate
	 * @param string $endDate
	 * @return string
	 */
	protected function getDateMap($startDate,$endDate,$isMonth=null){
		$startDate = $startDate ? $startDate : $this->startDate;
		if( strtotime($startDate) < strtotime($this->startDate) ){
			$startDate = $this->startDate;
		}
		$endDate = $endDate ? $endDate : date('Y-m-d');
		if( strtotime($endDate) > time() ){
			$startDate = date('Y-m-d');
		}
		$startTime = strtotime($startDate);
		$endTime = strtotime($endDate);
		if($startTime > $endTime){
			$t = $startDate;
			$startDate = $endDate;
			$endDate = $t;
		}
		$startDate = trim( substr($startDate,0,7),'-' );
		$startDate = explode('-',$startDate);
		
		$endDate = trim( substr($endDate,0,7),'-' );
		$endDate = explode('-',$endDate);
		
		if($startDate[0]==$endDate[0]){
			for($i=intval($startDate[1]);$i<=intval($endDate[1]);$i++){
				$data[]=$startDate[0].'-'.$i;
			}
			if(!$isMonth){
				foreach($data as $key=>$val){
					$data[$key] = str_replace('-','',$val);
				}
			}else{
				foreach($data as $key=>$val){
					$data[$key] = $val.'-00';
				}
			}
			return $data;
		}
		
		for($i=intval($startDate[1]);$i<=12;$i++){
			$data[]=$startDate[0].'-'.$i;
		}
		
		if($startDate[0] != $endDate[0]){
			for($i=($startDate[0]+1);$i<$endDate[0];$i++){
				for($k=1;$k<13;$k++){
					$data[]=$startDate[0].'-'.$i;
				}
			}
		}
		
		for($i=1;$i<=$endDate[1];$i++){
			$data[]=$startDate[0].'-'.$i;
		}
		if(!$isMonth){
			foreach($data as $key=>$val){
				$data[$key] = str_replace('-','',$val);
			}
		}else{
			foreach($data as $key=>$val){
				$data[$key] = $val.'-00';
			}
		}
		return $data;
	}
}