<?php
namespace Common\Model;
/**
 * 项目全局变量配置model
 * @author 宋小平 *
 **/
class DeployModel extends BaseModel{
    
	/**
     * @desc    字段过滤
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
					$datas['desc'] = htmlspecialchars($val);
					break;
				case 'url':
					if(!$val){
						$this->_error="目录名不能为空！";return false;
					}
					$datas['url'] = htmlspecialchars($val);
					break;
				case 'keywords':
					if(!$val){
						$this->_error="目录名不能为空！";return false;
					}
					$datas['keywords'] = htmlspecialchars($val);
					break;
				default :
					$datas[$key] = $val;
					break;
			}
		}
		return $datas;
	}
	
	/**
	 * @desc   获取全局变量值
     * @param  string $key 常量名称
     * @return 值
	 */
	public function getGlobal($key){
		$data = M('deploy')->where("`key`='{$key}'")->find();
		return $data['value'];
	}
}