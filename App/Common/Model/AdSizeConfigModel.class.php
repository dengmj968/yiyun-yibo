<?php
namespace Common\Model;
/**
 * 广告规格配置Model
 * @athor 张良
 * @create_date 2013-07-02
 */
class AdSizeConfigModel extends BaseModel
{
	public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if(!in_array($key,$fields)){
				continue;
			}
			switch($key){
				case 'size_name':
					if(!$val){
						$this->_error="尺寸名称不可为空!";return false;
					}
					$datas['size_name'] = htmlspecialchars($val);
					break;
				case 'width':
					if(!$val){
						$this->_error="宽度不可为空!";return false;
					}
					$datas['width'] = htmlspecialchars($val);
					break;
				case 'height':
					if(!$val){
						$this->_error="长度不可为空!";return false;
					}
					$datas['height'] = htmlspecialchars($val);
					break;
				case 'pic':
					if(!$val){
						$this->_error="请上传图片!";return false;
					}
					$datas['pic'] = htmlspecialchars($val);
					break;
				default :
					$datas[$key] = $val;
					break;
			}
		}
		$datas['update_time'] = time();
		return $datas;
	}

    /**
     * 检测是否存在，新增/修改时用
     * @param  string $type 枚举值 add/edit
     * @param  string $value  size_name 尺寸名称
     * @param  int $id 在修改时才有用，修改条目的id
     * @return bool
     */
    public function checkIsHaving($type = "add", $value = "", $id = 0)
    {
        $value = filter_str($value);
        $id = intval($id);
        if ($type == "add") {
            return D("AdSizeConfig")->where("size_name='$value'")->getField("id");
        } else {
            return D("AdSizeConfig")->where("id !={$id} and size_name='$value' ")->getField("id");
        }
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