<?php
namespace Common\Model;
use Think\Model;
/**
 * 全局地区配置model
 * 
 * @author 宋小平
 */
class AreaModel extends Model {
	/**
	 * 初始化表名
	 * @var string
	 */
	protected $tableName = 'area';
	
	private $__depth = 3;
	
	/**
     * 当指定pid时，仅查询该父地区的所有子地区；否则查询所有地区
     * 
	 * @param $pid 父地区ID
	 * @return array
	 */
	public function getAreaList($pid = -1) {
		$map = array();
		$pid != -1 && $map['pid'] = $pid;
		return $this->where($map)->order('`area_id` ASC')->select();
	}
	
	/**
	 * 保存新添加的位置
	 * @param string $data
	 */
	public function saveAddArea($data){
		return $this->add($data);
	}
	
	/**
	 * 删除地区
	 * @param unknown_type $option
	 */
	public function delAreas($option){
		return $this->where($option)->delete();
	}
	
	/**
	 * 保存修改地区
	 * @param array $data
	 * @param array $option
	 */
	public function saveArea($data,$option){
		return $this->where($option)->save($data);
	}
	
	/**
	 * 根据ID获取地区名称
	 * @param int $id
	 */
	public function getAreaById($id){
		$map['area_id'] = intval($id);
		return $this->where($map)->find();
	}
	
	/**
	 * 仅取前两级地区的结构树
     * @param $pid 父地区ID
     * @return array
	 */
	public function getAreaTree($pid) {
		$output	= array();
		$list	= $this->getAreaList();
		
		// 先获取省级
		foreach ($list as $k1 => $p) {
			if ($p['pid'] == 0) {
				// 获取当前省的市
				$city  = array();
				foreach ($list as $k2 => $c) {
					if($c['pid'] == $p['area_id']) {
						
						$area  = array();
						foreach($list as $k3 => $a){
							if($a['pid'] == $c['area_id']){
								$area[] = array($a['area_id'] => $a['title']);
								unset($list[$k3]);
							}
							
							
						}

						$city[] = array(
									$c['area_id'] => $c['title'],
									'citys' => $area,
								);
						
						unset($list[$k2]);
						
					}
				}
				$output['provinces'][] = array(
									       'id'		=> $p['area_id'],
											'name'	=> $p['title'],
											'citys'	=> $city,
									   	  );
				unset($list[$k1], $city);
			}
		}

		unset($list);
		return $output;
	}
}