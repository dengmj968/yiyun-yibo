<?php
namespace Common\Model;
use Think\Model;
/**
 * 广告点击展示统计缓存器
 * 用于流水数据统计缓存，益播2.0使用3.0已经弃用，但是不要删除
 * @author 宋小平
 */
class AdStatisticsModel extends Model{
	
	/**
	 * 广告id
	 * @var int
	 */
	public $ad_id;
	
	/**
	 * 地区点击展示数组
	 * @var array
	 */
	public $area = array();
	
	/**
	 * 点击次数
	 * @var int
	 */
	public $click;
	
	/**
	 * 展示次数
	 * @var int
	 */
	public $show;
	
	/**
	 * 统计时间
	 * @var int
	 */
	public $create_time;
	
	/**
	 * 地区id
	 * @var int
	 */
	public $areaId;
	
	/**
	 * 缓存对象
	 * @var object
	 */
	public $instance;
	
	/**
	 * 统计类型
	 * @var string
	 */
	public $type;
	
	/**
	 * 统计日期
	 * @var date
	 */
	public $date;
	
	/**
	 * 构造函数
	 * @param int $adId
	 * @param int $areaId
	 * @param string $type
	 */
	public function __construct($adId,$areaId,$type){
		if(!$adId) return false;
		$this->ad_id = $adId;
		if(intval($areaId)){
			$this->areaId = intval($areaId);
		}
		
		$this->type = $type;
		$this->_initialize();
	}
	
	/**
	 * 初始化函数
	 * 建立数据库数据，建立数据对象
	 */
	public function _initialize(){
		import("@.Entity.AdEntity");//加载数据结构
		$this->date = date('Y-m-d');//获取日期
		$key = $this->getKey();//获取key
		if(S($key)){//是否又缓存，有就更新
			$this->instance = S($key);
			$this->updateData();
			S($key,$this->instance);
		}else{
			$data = M('ad_statistics')->where("ad_id={$this->ad_id} AND create_time='{$this->date}'")->find();
			if($data){
				$data['area'] = unserialize($data['area']);
				$this->instance=new AdEntity();
				$this->instance->id    = $data['id'];
				$this->instance->ad_id = $data['ad_id'];
				$this->instance->area  = $data['area'];
				//$this->instance->click = $data['click'];
				//$this->instance->show  = $data['show'];
				if($this->type == 1){
					$this->instance->show = $data['show']+1;
					$datas['show'] = $this->instance->show;
					if($this->areaId){
						$this->instance->area[$this->areaId]['show'] +=1;
					}

				}elseif($this->type == 2){
					$this->instance->click = $data['click']+1;
					$datas['click'] = $this->instance->click;
					if($this->areaId){
						$this->instance->area[$this->areaId]['click'] +=1;
					}
				}
				$datas['area'] = serialize($this->instance->area);
				$this->instance->create_time= time();
				M('ad_statistics')->where("ad_id={$this->ad_id} AND create_time='{$this->date}'")->save($datas);
				S($key,$this->instance);
			}else{
				$key = $this->getKey();
				$this->instance = new AdEntity();
				$this->instance->ad_id = $this->ad_id;
				$this->instance->create_time = time();
				if($this->type == 1){
					$this->instance->show += 1;
					if($this->areaId){
						$this->instance->area[$this->areaId]['show'] += 1;

					}
				}elseif($this->type == 2){
					$this->instance->click += 1;
					if($this->areaId){
						$this->instance->area[$this->areaId]['click'] += 1;
					}
				}
					
				$data['ad_id'] = $this->instance->ad_id;
				$data['area'] = serialize($this->instance->area);
				$data['click'] = $this->instance->click;
				$data['show'] = $this->instance->show;
				$data['create_time'] = date("Y-m-d",$this->instance->create_time);
				$id = M('ad_statistics')->add($data);
				if($id){
					$handel = fopen('./statistics.txt','a+');
					fwrite($handel,"添加新记录条数，id={$id},ad_id={$data['ad_id']}\r\n");
				}
				$this->instance->id = $id;
				S($key,$this->instance);
			}
			
			
		}
	}
	
	/**
	 * 获取广告数据对象的key
	 * @param date $date
	 */
	public function getKey($date=null){
		if(!$date){
			$date = $this->date;
		}		
		$key = $date.'-adStatistics-'.$this->ad_id;
		return $key;
	}
	
	/**
	 * 更新数据对象
	 */
	public function updateData(){
		$key = $this->getKey();
		if($this->type == 1){
			$this->instance->show += 1;
			if($this->areaId){
				$this->instance->area[$this->areaId]['show'] += 1;
               // @file_put_contents("./a.txt",$this->ad_id."=>".$this->instance->show."=>".$this->instance->area[$this->areaId]['show']."\n");
			}			
		}elseif($this->type == 2){
			$this->instance->click += 1;
			if($this->areaId){
				$this->instance->area[$this->areaId]['click'] += 1;
			}
		}
		if(($this->instance->create_time + 5) < time()){
			$this->instance->create_time = time();
			$this -> sync();
		}
		S($key,$this->instance);
	}
	
	/**
	 * 同步数据库
	 */	
	public function sync(){
		$ad_id = $this->instance->ad_id;
		$data['click'] = $this->instance->click;
		$data['show'] = $this->instance->show;
		$data['area'] = serialize($this->instance->area);
		M('ad_statistics')->where("ad_id={$ad_id} AND create_time='{$this->date}'")->data($data)->save();
	}
}