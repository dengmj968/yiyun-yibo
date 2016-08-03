<?php
namespace Home\Controller;
/**
 * 用户中心
 * 框架页
 */
class UserCenterController extends CommonController {
	
	/**
	 * 个人资料
	 */
	function index() {
		$uid = $this->userInfo['id'];
		$map['user_id'] = $uid;
		// 获取该用户的所有广告的计数
		$userData['adNum'] = D('Ad')->getCount( $map );
		// 获取该用户的所有广告位的计数
		$userData['placeNum'] = D('Place')->getCount ( array('uid'=>$uid) );
		$userData['placeNum'] += D('Place404')->getCount ( array('uid'=>$uid) );
		// 获取昨日总点击和总展示数目

		$userData['yesterdayShowNum'] = $this->getDataYesterday(); 
		// 昨日总点击和总展示数目

		 $userData['yesterdayClickNum'] = $this->getDataYesterday('click');
		//本月展示数目
		//$userData['thisMonthShowNum'] = $this->getDataThisMonth();
		//$userData['thisMonthClickNum'] = $this->getDataThisMonth('click');
	
 		$adMonthShowList = $this->getAdMonthList('show');
		$adMonthClickList = $this->getAdMonthList('click');
			
		//普通广告位点击和展现量
		$myPlaceShowSum = $this->getPlaceDataThisMonth('show');
		$myPlaceClickSum = $this->getPlaceDataThisMonth('click'); 


		//dump($myPlaceShowSum);
		
// 		$my404Place = D('Place404')->getDataList(array('uid'=>$uid),'id');
// 		$data = D('GetStatistics')->getAdMonthStatisticsSum();
// 		foreach($myPlace as $key=>$val){
// 			$myPlaceShowSum += $data['place_id_map'][$val['id']];
// 		}

		$data = D('GetStatistics')->getAdMonthStatistics(array('uid'=>$uid),'show');

		$userData['sumShow'] = $data['sum'] ? $data['sum'] : 0;
		$data = D('GetStatistics')->getAdMonthStatistics(array('uid'=>$uid),'click');
		$userData['sumClick'] = $data['sum'] ? $data['sum'] : 0;
		//被注释
/* 		$userData['sumShow'] = mt_rand(5000,40000);
		$userData['sumClick'] = mt_rand(500,2000);
		$userData['adNum'] = mt_rand(1,20);
		$userData['placeNum'] = mt_rand(2,30);
		$userData['yesterdayShowNum'] = mt_rand(1000,8000);
		$userData['yesterdayClickNum'] = mt_rand(20,200);
		$userData['thisMonthShowNum'] = mt_rand(1000,10000);
		$userData['thisMonthClickNum'] = mt_rand(300,2000);
		$myPlaceShowSum = mt_rand(1000,10000);
		$myPlaceClickSum = mt_rand(20,100);
		$adMonthShowList = mt_rand(5000,40000);
		$adMonthClickList = mt_rand(300,2000); */
		
		$this->assign ('myPlaceShowSum',$myPlaceShowSum);
		$this->assign ('myPlaceClickSum',$myPlaceClickSum);
		$this->assign ('userData',$userData);
		$this->assign ('adMonthShowList',$adMonthShowList);
		$this->assign ('adMonthClickList',$adMonthClickList);
		$this->display ();
	
	}
	
	/**
	 * 获取广告昨天的展示/点击量
	 * @param unknown_type $type
	 */
	public function getDataYesterday($type) {
		$map['create_time'] = date( 'Y-m-d',strtotime("-1 day") );
		$map['uid'] = $this->userInfo['id'];
		// 获取昨日总点击和总展示数目
		$data = D('GetStatistics')->getAdDayStatistics($map,$type);
		$sum = $data['sum'] ? $data['sum'] : 0;
		return $sum;
	}
	
	/**
	 * 获取当前用户所有广告位的展示和点击量
	 * @param string $type
	 */
	public function getPlaceDataThisMonth($type){
		$uid = $this->userInfo['id'];
		$myPlaceList = D('Place')->getDataList(array('uid'=>$uid),'pid');
		$map['create_time'] = array('GT',date('Y-m-01'));
		$data = D('GetStatistics')->getAdDayStatistics($map,$type);
		foreach($myPlaceList as $key=>$val){
			$sum += $data['place_id_map'][$val['pid']];
		}
		
		$monthData = D('GetStatistics')->getAdMonthStatistics('',$type);
		foreach($myPlaceList as $key=>$val){
			$sum += $monthData['place_id_map'][$val['pid']];
		}
		$sum = $sum ? $sum : 0;
		return $sum;
	}
	
	/**
	 * 本月广告展示/点击量
	 * @param unknown_type $type
	 */
	public function getDataThisMonth($type){
		$map['create_time'] = array('GT',date('Y-m-01'));
		$map['uid'] = $this->userInfo['id'];
		$data = D('GetStatistics')->getAdDayStatistics($map,$type);
		$sum = $data['sum'] ? $data['sum'] : 0;
		return $sum;
	}
	
	/**
	 * 获取本年度每个月的广告点击/展示量
	 * @param unknown_type $type
	 */
	public function getAdMonthList($type){
		$strsrtDate = date('Y-01-0',strtotime('last month'));
		$endDate = date('Y-m-0',strtotime('last month'));
		$dateList = explode('-',$endDate);
		$map['uid'] = $this->userInfo['id'];
		for( $i=1; $i <= intval($dateList[1]); $i++ ){
 			$date = $dateList[0].'-'.$i.'-00';
 			$map['create_time'] = $date;
 			$monthInfo = D('GetStatistics')->getAdMonthStatistics($map,$type);
 			$adMonthList[] = $monthInfo['sum'] ? $monthInfo['sum'] : 0;
		}
		$map['create_time'] = array('GT',date('Y-m-00'));
		$data = D('GetStatistics')->getAdDayStatistics($map,$type);
		$adMonthList[] = $data['sum'];
		return implode(',',$adMonthList);
	}
}

?>