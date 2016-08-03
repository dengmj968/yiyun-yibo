<?php
namespace Common\Model;
/**
 * 设计师作品
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0
 * creat Time  2014-12-09 10:30
 */
class ProductionModel extends BaseModel{
    
    private $statusText = array('待审核' ,'审核未通过' ,'审核通过' ,'已认购');


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
					if(!$val){
						$this->_error="作品名称不能为空！";return false;
					}
					$datas['title'] = htmlspecialchars($val);
					break;
                case 'uid':
                    if ($val){
                        $datas['uid'] = intval($val);
                    }
                    break;
				case 'desc':
					if(!$val){
						$this->_error="设计理念不能为空！";return false;
					}
					$datas['desc'] = htmlspecialchars($val);
					break;
				case 'img':
					if(!$val){
						$this->_error="请上传作品图片！";return false;
					}
					$datas['img'] = htmlspecialchars($val);
					break;
                case 'size_id':
					if(!$val){
						$this->_error="请选择作品尺寸！";return false;
					}
					$datas['size_id'] = intval($val);
					break;
                case 'status':
                    $datas[$key] = intval($val)?intval($val):0;
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
    
    /**
     * 获取作品列表
     */
   
    public function getProductionList( array $map,$fields=null,$order=null,$limit=null,$group=null){
		$fields = $fields ? $fields : '*';
		$order = $order ? $order : '`id` desc';
		$dataList = $this->where($map)->field($fields)->group($group)->order($order)->limit($limit)->select();
		$this->getlastsql();
		return $dataList;
	} 

}