<?php
namespace Common\Model;
/**
 * @desc 合作伙伴Model
 **/
class CooperationModel extends BaseModel{
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
				case 'title':
					if(!$val){
						$this->_error="标题不能为空！";return false;
					}
					$datas['title'] = htmlspecialchars($val);
					break;
				case 'desc':
					if(!$val){
						$this->_error="描述不能为空！";return false;
					}
					$datas['desc'] = htmlspecialchars($val);
					break;
				case 'url':
					if(!$val){
						$this->_error="url不能为空！";return false;
					}
					$datas['url'] = htmlspecialchars($val);
					break;
				case 'logo':
					if(!$val){
						$this->_error="请上传LOGO！";return false;
					}
					$datas['logo'] = htmlspecialchars($val);
					break;
				default :
					$datas[$key] = $val;
					break;
			}
		}
		return $datas;
	}

}