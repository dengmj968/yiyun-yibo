<?php
namespace Common\Model;
/**
 * 广告位流水表model
 * @author songweiqing
 */
class TempPlaceModel extends BaseModel{
	public $trueTableName='place_temp';
    /**
     * @desc 字段数据过滤
     * @param array $data
     * @return array $datas
     */
	public function parseData(array $data){
		$fields=$this->getFields();
		foreach($data as $key => $val){
			if( !in_array($key, $fields) ){
				continue;
			};
			switch($key){
				case 'name':
					if(!$val){
						$this->_error = '广告位名称不能为空';
						return false;
					}
					$datas[$key] = htmlspecialchars($val);
					break;
				case 'size_id':
					if(!$val){
						$this->_error = '请选择广告位尺寸';
						return false;
					}
					$datas[$key] = $val;
					break;
				case 'placeType':
					if(!$val){
						$this->_error = '请选择广告位类型';
						return false;
					}
					$datas[$key] = $val;
					break;
				default :
					$datas[$key] = $val;
					break;
			}
		}
		$datas['create_time'] = date('Y-m-d H:i:s',time() );
		$datas['status'] = 1;
		return $datas;
	}
    
    /**
     * @desc 获取一条数据记录
     * @param array $map
     * @return array $data
     */
	function getSingleData($map){
		$res = $this -> where($map)->find();
		if(!empty($res)){
			return $res;
		}else{
			return false;
		}
	}
}