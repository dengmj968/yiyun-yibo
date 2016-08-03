<?php
namespace Common\Model;
/**
 * 客户案例
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0
 * creat Time  2014-10-14 10:30
 */
class CustomerCaseModel extends BaseModel{
    
    /**
     * 添加或修改案例时进行数据过滤
     * @param array $data  数据
     * @return boolean|array  有非法数据返回false或者返回过滤好的数据
     */
	public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if(!in_array($key,$fields)){
				continue;
			}
			switch($key){
				case 'name':
					if(!$val){
						$this->_error="案例名称不能为空！";return false;
					}
					$datas['name'] = htmlspecialchars($val);
					break;
                case 'uid':
                    if ($val){
                        $datas['uid'] = intval($val);
                    }
                    break;
				case 'desc':
					if(!$val){
						$this->_error="案例描述不能为空！";return false;
					}
					$datas['desc'] = $val;
					break;
                case 'abstract':
					if(!$val){
						$this->_error="摘要不能为空！";return false;
					}
					$datas['abstract'] = htmlspecialchars($val);
					break;
				case 'start_time':
					if(!$val){
						$this->_error="请填写投放的开始时间！";return false;
					}
					$datas['start_time'] = strtotime($val);
					break;
                case 'end_time':
					if(!$val){
						$this->_error="请填写投放的结束时间！";return false;
					}
					$datas['end_time'] = strtotime($val);
					break;
				case 'pic':
					if(!$val){
						$this->_error="请上传案例图片！";return false;
					}
					$datas['pic'] = htmlspecialchars($val);
					break;
                case 'show_num':
                case 'click_num':
                case 'sort':
                case 'status':
                    $datas[$key] = intval($val);
                    break;
				default :
					$datas[$key] = $val;
					break;
			}
		}

		return $datas;
	}
    
    /**
     * 获取根据用户id获取用户名
     * @param int $uid  用户id
     * @return string|null  用户信息不存在返回NULL
     */
    function getUsername($uid){
        $info = D('Memcache')->getUserInfoById($uid);
        if (isset($info['extend']['true_name'])){
            return $info['extend']['true_name'];
        }else if(isset($info['extend']['user_name'])){
            return $info['extend']['user_name'];
        }else if (isset($info['email'])){
            return $info['email'];
        }  else {
            return NULL;
        }
    }

}