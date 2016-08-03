<?php
namespace Common\Model;
/**
 * 关键字大类Model
 * @athor zhangliang
 * @create_date 2013-07-01
 */
class KeywordsCategoryModel extends BaseModel
{
	public $trueTableName = 'keywords_category';
	public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key => $val){
			if(!in_array($key,$fields)){
				continue;
			}
			switch($key){
				case 'category_name':
				if(!$val){
					$this->_error('关键字分类名称不能为空');
					return false;
				}
				$datas[$key] = htmlspecialchars($val);
				break;
				default:
					$datas[$key] = $val;
			}
		}
		return $datas;
	}

	/**
	 * 根据id获取某个字段
	 * @param int $id
	 * @param string $field
	 * @return mixed
	 */
	function getFieldById($id = 0, $field = '')
	{
		$id = intval($id);
		$field = trim($field);
		return $this->where("id={$id}")->getField($field);
	}
}