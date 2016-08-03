<?php
namespace Common\Model;
/**
 * 数据统计方法
 * 流水写入，流水表统计,所有分表均由模板表生成
 * 表名规则参见《益播统计系统表名设计》文档
 * @author 宋小平
 * @version 1.0
 * @create time 2014-10-15
 */
class StatisticsModel extends BaseModel{
	//广告流水表模型
	private $modelHistoryTableName = "st_ad_history_model";
	//广告统计表模型
	private $modelAdStatisticsTableName = "st_ad_statistics_model";
	//404流水表模型
	private $model404HistoryTableName = "st_404_history_model";
	//404统计表模型
	private $model404StatisticsTableName = "st_404_statistics_model";
	
	public function parseData(array $data){
		if(empty($data)){
			return false;
		}
		return $data;
	}
	
	/**
	 * 获取对应的日期
	 * @param string $type
	 * @return string
	 */
	private function getDateName($type){
		switch($type){
			case 'day' :
				$date = date('Ymd');
				break;
			case 'month' :
				$date = date('Ym');
				break;
			case 'year' :
				$date = date('Y');
				break;
			default:
				$date = date('Ymd');
		}
		return $date;
	}
	
	/**public function getTableName($from,$type,$grade){
		$tableName = '';
		if($from == 'ad'){
			switch($grade){
				case 'history' :
					if($type == 'show'){
						$tableName = 'st_'.$from.'_'.$grade.'_'.$type.'_'.$this->getDateName('month');
					}else{
						$tableName = 'st_'.$from.'_'.$grade.'_'.$type.'_'.$this->getDateName('year');
					}
					break;
				case 'day' :
					$tableName = 'st_'.$from.'_statistics_'.$type.'_'.$grade.'_'.$this->getDateName('year');
					break;
				case 'month' :
					$tableName = 'st_'.$from.'_statistics_'.$type.'_'.$grade;
			}
				
		}elseif($from == 404){
			
		}
		return $tableName;
	}**/
	
	/**
	 * 创建数据表
	 * @param string $tableName 要创建的表名
	 * @param string $modelName 模板表名
	 */
	private function createTable($tableName,$modelName){
		$bool = $this->isHaveTable($tableName);
		if($bool){
			$this->trueTableName = $tableName;
			return true;
		}else{
			$sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` like {$modelName}";
			$result = $this->query($sql);
			$this->trueTableName = $tableName;
			return true;
		}
	}
	
	/**
	 * 判断数据表是否存在
	 * @param string $tableName
	 */
	private function isHaveTable($tableName){
		$sql = "select * from {$tableName} where false";
		$result = $this->query($sql);
		if( is_array($result) ){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 更新404页面上普通广告点击流水表记录
	 * @param array $data
	 * @param string show/click
	 */
	public function update404adHistoryTable($data,$type){
		$type = $type ? $type : 'show';
		if($type == 'show'){
			$tableName = 'st_404ad_history_'.$type.'_'.$this->getDateName('month');
		}else{
			$tableName = 'st_404ad_history_'.$type.'_'.$this->getDateName('year');
		}
		$this->createTable($tableName,$this->modelHistoryTableName);
		$id = $this->addData($data);
		if($id){
			$this->statistics404adHistoryToDay($data,$type);
		}
	}
	
	/**
	 * 广告日统计
	 * 1、每年一张表
	 * 2、每天每条广告产生一条数据
	 * @param array $data
	 */
	public function statistics404adHistoryToDay($data,$type){
		$type = $type ? $type : 'show';
		$tableName = 'st_404ad_statistics_'.$type.'_day_'.$this->getDateName('year');
		$this->createTable($tableName,$this->modelAdStatisticsTableName);
		$map['ad_id'] = $data['ad_id'];
		$map['create_time'] = $date = date('Y-m-d');
		$adInfo = $this->getDataByMap($map);  //查找是否有日统计数据
		if($adInfo){
			$adInfo['place_id_map'][$data['place_id']] += 1;
			$adInfo['area_id_map'][$data['area_id']] += 1;
			$adInfo['sum'] += 1;
			$this->saveDataById($adInfo);
		}else{
			$option['ad_id'] = $data['ad_id'];
			$option['uid'] = $data['uid'];
			$option['place_id_map'] = array($data['place_id']=>1);
			$option['area_id_map'] = array($data['area_id'] =>1);
			$option['create_time'] = date('Y-m-d');
			$option['sum'] = 1;
			$this->addData($option);
		}
	}
	
	/**
	 * 更新普通广告流水表记录
	 * @param array $data
	 * @param string show/click
	 */
	public function updateAdHistoryTable($data,$type){
		$type = $type ? $type : 'show';
		if($type == 'show'){
			$tableName = 'st_ad_history_'.$type.'_'.$this->getDateName('month');
		}else{
			$tableName = 'st_ad_history_'.$type.'_'.$this->getDateName('year');
		}
		$this->createTable($tableName,$this->modelHistoryTableName);
		$id = $this->addData($data);
		if($id){
			$this->statisticsAdHistoryToDay($data,$type);
		}
	}
	
	/**
	 * 广告日统计
	 * 1、每年一张表
	 * 2、每天每条广告产生一条数据
	 * @param array $data
	 */
	public function statisticsAdHistoryToDay($data,$type){
		$type = $type ? $type : 'show';
		$tableName = 'st_ad_statistics_'.$type.'_day_'.$this->getDateName('year');
		$this->createTable($tableName,$this->modelAdStatisticsTableName);
		$map['ad_id'] = $data['ad_id'];
		$map['create_time'] = $date = date('Y-m-d');
		$adInfo = $this->getDataByMap($map);  //查找是否有日统计数据
		if($adInfo){
			$adInfo['place_id_map'][$data['place_id']] += 1;
			$adInfo['area_id_map'][$data['area_id']] += 1;
			$adInfo['sum'] += 1;
			$this->saveDataById($adInfo);
		}else{
			$option['ad_id'] = $data['ad_id'];
			$option['uid'] = $data['uid'];
			$option['place_id_map'] = array($data['place_id']=>1);
			$option['area_id_map'] = array($data['area_id'] =>1);
			$option['create_time'] = date('Y-m-d');
			$option['sum'] = 1;
			$this->addData($option);
		}
	}
	
	/**
	 * 普通广告日表统计到月表
	 * 月表不分表
	 * @param string $date
	 * @param string $type
	 */
	public function statisticsAdDayToMonth($date,$type){
		set_time_limit(0);
		$date = $date ? $date : date('Y-m-d',strtotime('last month'));
		$create_time = date('Y-m-00',strtotime($date));
		if($create_time == date('Y-m-00')){
			$this->error = '本月未完！';
			return false;
		}
		$type = $type ? $type : 'show';
        $tableName = 'st_ad_statistics_'.$type.'_month';
		if(!$this->isHaveTable($tableName)){
			$this->error = '类型表不存在！';
			return false;
		}
		$result = M("$tableName")->where("create_time = '{$create_time}'")->limit(1)->select();
		if($result && !empty($result)){
			$this->error = '该月数据已经同步！';
			return false;
		}
		$startDate = date('Y-m-01',strtotime($date));
		$endDate = date('Y-m-t',strtotime($date));
        
        $dateList = explode('-',$date);
        $dayTableName = 'st_ad_statistics_'.$type.'_day_'.$dateList['0'];

		$sql = "select distinct ad_id from $dayTableName where create_time between '{$startDate}' and '{$endDate}'";
		$adIdList= M()->query($sql);
		if(!$adIdList || empty($adIdList)){
			return;
		}
	
		foreach($adIdList as $key=>$val){
			$map[ad_id] = $val['ad_id'];
			$map['date'] = $date;
			$map['create_time'] = array('between',array($startDate,$endDate));
			$data = D('GetStatistics')->getAdDayStatistics($map,$type);
			$data['create_time'] = $create_time;
			$data['area_id_map'] = serialize($data['area_id_map']);
			$data['place_id_map'] = serialize($data['place_id_map']);
			M("$tableName")->add($data);
		}
	}
	
	/**
	 * 广告流水表统计后添加到日统计表，默认统计前一天的
	 * 采用的一次性查取，循环计算插入
	 * @param strint $date 要统计的日期
	 */
	public function statisticsHistoryToDay($date){
		set_time_limit(0);
		$date = $date ? $date : date('Y-m-d',(time()-3600*24));
		$tableName = 'ad_statistics_show_'.intval($date);
		$this->createTable($tableName,$this->modelAdStatisticsTableName);//检查是否存在表
		$result = M("$tableName")->where("create_time = '{$date}'")->limit(1)->select();
		if($result && !empty($result)){
			echo '该天已经同步！';
			return;
		}
		
		$endDate = date( 'Y-m-d',(strtotime($date)+3600*24) );
		$table = 'ad_history_show_'.date('Ym',strtotime($date));
		$sql = "select distinct ad_id from $table where create_time between '{$date}' and '{$endDate}'";
		$adIdList= M()->query($sql);
		if(!$adIdList || empty($adIdList)){
			return;
		}
		foreach($adIdList as $key=>$val){
			$adList = M("$table")->where("ad_id = {$val['ad_id']} and create_time > '{$date}' and create_time < '{$endDate}'")->select();
			foreach($adList as $k=>$v){
				$data['uid'] = $v['uid'];
				$data['ad_id'] = $v['ad_id'];
				$data['sum'] +=1;
				$data['place_id_map'][$v['place_id']]+=1;
				$data['area_id_map'][$v['area_id']]+=1;
				$data['create_time']=$date;
			}
			$data['place_id_map'] = serialize($data['place_id_map']);
			$data['area_id_map'] = serialize($data['area_id_map']);
			M("$tableName")->add($data);
			unset($data);
		}
	}
	
	/**
	 * 更新404广告流水表
	 * @param array $data
	 */
	public function update404HistoryTable($data,$type='show'){
		if($type == 'show'){
			$tableName = 'st_404_history_'.$type.'_'.$this->getDateName('month');
		}else{
			$tableName = 'st_404_history_'.$type.'_'.$this->getDateName('year');
		}
		$this->createTable($tableName,$this->model404HistoryTableName);
		$this->addData($data);
	}
	
	/**
	 +------------------------------------------
	 *		该方法已经停用，数据改为定时任务统计
	 +------------------------------------------
	 * 404日表实时插入方法，现在已经改为统计插入,该方法已经停用
	 * 将一条流水记录更新到日统计表
	 * @param string $data
	 * @param string $type
	 */
	public function update404Day($data,$type){
		$type = $type ? $type : 'show';
		$year = date('Y');
		$tableName = 'st_404_statistics_'.$type.'_day_'.intval($year);
		$this->createTable($tableName,'ad404statistics');
		$map['place_id'] = $data['place_id'];
		$map['create_time'] = $date = date('Y-m-d');
		$adInfo = $this->getDataByMap($map);  //查找是否有日统计数据
		if($adInfo){
			$adInfo['project_id_map'][$data['project_id']] += 1;
			$adInfo['area_id_map'][$data['area_id']] += 1;
			$adInfo['sum'] += 1;
			$this->saveDataById($adInfo);
		}else{
			$option['place_id'] = $data['place_id'];
			$option['uid'] = $data['uid'];
			$option['project_id_map'] = array($data['project_id']=>1);
			$option['area_id_map'] = array($data['area_id'] =>1);
			$option['create_time'] = date('Y-m-d');
			$option['sum'] = 1;
			$this->addData($option);
		}
	}
	
	/**
	 * 404流水表循环统计进入日统计表
	 * @param string $date 日期
	 * @param string $type show/click 展示/点击
	 */
	public function statistics404HistotyToDay($date=null,$type=null){
		set_time_limit(0);
		$type = $type ? $type : 'show';
		//默认日期是参数，没有就是当前日期的前一天
		$date = $date ? $date : date('Y-m-d',strtotime('last day'));
		$dateList = explode('-',$date);
		//流水表明
		if($type == 'show'){
			$historyTableName = 'st_404_history_'.$type.'_'.$dateList['0'].$dateList[1];
		}else{
			$historyTableName = 'st_404_history_'.$type.'_'.$dateList['0'];
		}
	
	
		//日统计表名
		$statisticsTableName = 'st_404_statistics_'.$type.'_day_'.$dateList['0'];
		if(!$this->isHaveTable($statisticsTableName)){
			$this->createTable($statisticsTableName,$this->model404StatisticsTableName);
		}
		$Info = M("$statisticsTableName")->where("create_time = '{$date}'")->find();
		if($Info && !empty($Info)){
			echo '该日已经统计！';
			return;
		}
		//查询开始数据条数，获取ID
		$startTime = $date.' 00:00:00';
		$endTime = $date.' 23:59:59';
		$sql = "SELECT min(`id`) as startId,max(`id`) as endId
				FROM {$historyTableName}
				WHERE create_time BETWEEN '{$startTime}' and '{$endTime}'
				LIMIT 1
				";
		$result = M()->query($sql);
		$startId = $result[0]['startId'];
		$endId = $result[0]['endId'];
		if(!$startId || !$endId){
			return false;
		}
	
		$i = $startId;
		while($i <= $endId){
			$listData = M("$historyTableName")->where("id = $i")->find();
			if(!$listData || empty($listData)){
				$i++;
				continue;
			}
	
			$map['place_id'] = $listData['place_id'];
			$map['create_time'] = $date;
			$data = M("$statisticsTableName")->where($map)->find();
	
			if($data && !empty($data)){
				$data['project_id_map'] = unserialize($data['project_id_map']);
				$data['area_id_map'] = unserialize($data['area_id_map']);
				$data['sum'] +=1;
				$data['project_id_map'][$listData['project_id']] = $data['project_id_map'][$listData['project_id']] + 1;
				$data['area_id_map'][$listData['area_id']] = $data['area_id_map'][$listData['area_id']] + 1;
				$data['project_id_map'] = serialize($data['project_id_map']);
				$data['area_id_map'] = serialize($data['area_id_map']);
				unset($data['id']);
				M("$statisticsTableName")->where($map)->save($data);
				unset($data);
			}else{
				$option['uid'] =$listData['uid'];
				$option['place_id'] =$listData['place_id'];
				$option['sum'] =1;
				$option['create_time'] = $date;
				$option['project_id_map'][$listData['project_id']] =1;
				$option['area_id_map'][$listData['area_id']] =1;
				$option['project_id_map'] = serialize($option['project_id_map']);
				$option['area_id_map'] = serialize($option['area_id_map']);
				M("$statisticsTableName")->add($option);
				unset($option);
			}
			$i++;
		}
	}
	
	/**
	 * 404日数据统计到月表
	 * @param string $date
	 * @param string $type
	 */
	public function statistics404DayToMonth($date,$type){
		set_time_limit(0);
		$type = $type ? $type : 'show';
		//默认日期是参数，默认统计前一个月的数据
		$date = $date ? $date : date('Y-m-d',strtotime('last month'));

		//该月的第一天和最后一天
		$startDate = date('Y-m-01',strtotime($date));
		$endDate = date('Y-m-t',strtotime($date));

		$dateList = explode('-',$date);
		$date = $dateList[0].'-'.$dateList[1].'-00';
		//组合日表表名
		$dayTableName = 'st_404_statistics_'.$type.'_day_'.$dateList['0'];
		$monthTableName = 'st_404_statistics_'.$type.'_month';
		
		//判断月表是否存在，不在就创建
		if(!$this->isHaveTable($monthTableName)){
			$this->createTable($monthTableName,$this->model404StatisticsTableName);
		}
		
		//判断月表里面是否有该月数据，有就返回
		$Info = M("$monthTableName")->where("create_time = '{$date}'")->find();

		if($Info && !empty($Info)){
			$this->error = '该日已经统计！';
			return false;
		}
		//查询开始数据条数，获取ID
		$sql = "SELECT min(`id`) startId,max(`id`) endId 
				FROM {$dayTableName}
				WHERE create_time BETWEEN '{$startDate}' and '{$endDate}'
				LIMIT 1
		";
		$result = M()->query($sql);
		$startId = $result[0]['startId'];
		$endId = $result[0]['endId'];
		if(!$startId || !$endId){
			return false;
		}
		$i = $startId;

		while($i <= $endId){
			$listData = M("$dayTableName")->where("id = $i")->find();

			if(!$listData || empty($listData)){
				$i++;
				continue;
			}
			$map['place_id'] = $listData['place_id'];
			$map['create_time'] = $date;
			$data = M("$monthTableName")->where($map)->find();
			$projectIdMap = unserialize($listData['project_id_map']);

			$areaIdMap = unserialize($listData['area_id_map']);
			if($data && !empty($data)){
				$project_id_map = unserialize($data['project_id_map']);
				$area_id_map = unserialize($data['area_id_map']);
				foreach($projectIdMap as $k=>$v){
					$project_id_map[$k] += $v;
				}
				foreach($areaIdMap as $k=>$v){
					$area_id_map[$k] += $v;
				}
				$data['sum'] += $listData['sum'];
				$data['project_id_map'] = serialize($project_id_map);
				$data['area_id_map'] = serialize($area_id_map);
				unset($data['id']);
				M("$monthTableName")->where($map)->save($data);
				unset($data);
			}else{
				$option['uid'] =$listData['uid'];
				$option['place_id'] =$listData['place_id'];
				$option['sum'] =$listData['sum'];
				$option['create_time'] = $date;
				$option['project_id_map'] = $listData['project_id_map'];
				$option['area_id_map'] = $listData['area_id_map'];
				M("$monthTableName")->add($option);
				unset($option);
			}
			$i++;
		}
	}
}