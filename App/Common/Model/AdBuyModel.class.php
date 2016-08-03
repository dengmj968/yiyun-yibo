<?php
namespace Common\Model;
/**
 * 广告认购方法
 * @author admin
 *
 */
class AdBuyModel extends BaseModel{
	
	public function parseData(array $data){
		$fields = $this->getFields();
		foreach($data as $key => $val){
			if( !in_array($key,$fields) ){
				continue;
			}
			switch($key){
				case 'ad_id':
					if(!$val){
						$this->_error="无关联作品！";return false;
					}
					$datas['ad_id'] = intval($val);
					break;
				case 'buyer_id':
					if(!$val){
						$this->_error="无买家信息！";return false;
					}
					$datas['buyer_id'] = intval($val);
					break;
				case 'seller_id':
					if(!$val){
						$this->_error="无卖家信息！";return false;
					}
					$datas['seller_id'] = intval($val);
					break;
				case 'show_num':
					$datas['show_num'] = intval($val);
					break;
				case 'show_time':
					if(!$val){
						$this->_error="购买天数不能为空！";return false;
					}
					$datas['show_time'] = intval($val);
					break;
                case 'url':
                    $val = strip_tags($val);
					if(!$val){
						$this->_error="跳转链接不能为空！";return false;
					}else if(strpos($val, 'http://') === false){
                        $val = 'http://'.$val;
                    }
					$datas['url'] = htmlspecialchars($val);
					break;
				default :
					$datas[$key] = htmlspecialchars($val);
					break;
			}
		}
		$datas['update_time'] = time();
		return $datas;
	}
	
	protected function getUserScore($uid){
		$data['uid'] = $uid;
		$score = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/ScoreApi/getScoreByUid",$data),true );
		return $score;
	}
	
	protected function sendUserScore($uid,$points){
		$data['uid'] = $uid;
		$data['points'] = $points;
		$bool = json_decode( curl_post($this->deploy['CENTER_SERVER']."/Api/ScoreApi/getScore",$data),true );
		return $bool;
	}
}