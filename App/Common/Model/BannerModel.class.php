<?php
namespace Common\Model;
/**
 * banner图
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0
 * creat Time  2015-03-04
 */
class BannerModel extends BaseModel{
    
	public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if(!in_array($key,$fields)){
				continue;
			}
			switch($key){
				case 'place':
					if(!$val){
						$this->_error="请选择显示位置！";return false;
					}
					$datas['place'] = htmlspecialchars($val);
					break;
                case 'url':
                    if ($val && strpos($val,'http://')===false){
                        $val = 'http://'.$val;
                    }
					$datas['url'] = htmlspecialchars($val);
					break;
				case 'pic':
					if(!$val){
						$this->_error="请上传图片！";return false;
					}
					$datas['pic'] = htmlspecialchars($val);
					break;
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
    
    public function getInfoByPlace($place, $is_all = 0){
        $map = array();
        $map['status'] = 1;
        $data = S('bannerImg');
        if ($data===false || !isset($data[$place])){
            $data = array();
            $info = $this->field('pic,url,place')->where($map)->order('sort ASC')->select();
            if ($info){
                foreach($info as $val){
                    $data[$val['place']][] = $val;
                }
                S('bannerImg', $data);
            }
        }
        
        if (is_array($data) && isset($data[$place])){
            if ($is_all==0){
                return $data[$place][0];
            }else{
                return $data[$place];
            }
        }else{
            return false;
        }
    }
    

}