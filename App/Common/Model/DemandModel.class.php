<?php
namespace Common\Model;
/**
 * 设计师需求
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0
 * creat Time  2014-12-09 14:30
 */
class DemandModel extends BaseModel{
    
    private $statusText = array('未完成' ,'已完成');


    /**
     * 数据过滤
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
				case 'title':
                    $val = strip_tags($val);
					if(!$val){
						$this->_error="需求名称不能为空！";return false;
					}
					$datas['title'] = htmlspecialchars($val);
					break;
                case 'uid':
                    if ($val){
                        $datas['uid'] = intval($val);
                    }
                    break;
				case 'desc':
                    $val = strip_tags($val);
					if(!$val){
						$this->_error="需求描述不能为空！";return false;
					}
					$datas['desc'] = htmlspecialchars($val);
					break;
                case 'status':
                case 'score':
                    $datas[$key] = intval($val);
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
     * 获取根据用户id获取用户名
     * @param int $uid  用户id
     * @return string|null  用户信息不存在返回NULL
     */
    function getUsername($uid){
        $info = D('Memcache')->getUserInfoById($uid);
        //return $info['email'];
        if (isset($info['extend']['true_name']) && $info['extend']['true_name']){
            return $info['extend']['true_name'];
        }else if(isset($info['extend']['user_name']) && $info['extend']['user_name']){
            return $info['extend']['user_name'];
        }else if (isset($info['email'])){
            return $info['email'];
        }  else {
            return NULL;
        }
    }
    
    /**
     * 根据状态值获取状态描述
     * @param int $status 状态值
     */
    public function getStatusText($status){
        if (isset($this->statusText[$status])){
            return $this->statusText[$status];
        }
        return NULL;
    }

}