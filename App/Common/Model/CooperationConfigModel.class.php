<?php
namespace Common\Model;
/**
 * @desc 合作伙伴分类Model
 **/
class CooperationConfigModel extends BaseModel
{
	/**
     * @desc    字段过虑
     * @return  $datas array
     **/
    public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if(!in_array($key,$fields)){
				continue;
			}
			switch($key){
				case 'name':
					if(!$val){
						$this->_error="标题不能为空！";
						return false;
					}
					$datas['name'] = htmlspecialchars($val);
					break;
				case 'desc':
					$datas['desc'] = htmlspecialchars($val);
					break;
				default :
					$datas[$key] = $val;
					break;
			}
		}
		return $datas;
	}
}