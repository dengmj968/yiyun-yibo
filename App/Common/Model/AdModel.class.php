<?php
namespace Common\Model;
/**
 * 广告Model
 * 所有广告数据接口
 * author lixiaoli
 */
class AdModel extends BaseModel
{
	public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if( !in_array($key,$fields) ){
				continue;
			}
			switch($key){
				case 'title':
					if(!$val){
						$this->_error="请填写广告标题！";return false;
					}
					$datas['title'] = htmlspecialchars($val);
					break;
				case 'desc':
					$datas['desc'] = htmlspecialchars($val);
					break;
				case 'size_id':
					if(!$val){
						$this->_error="请选择广告大小！";return false;
					}
					$datas['size_id'] = intval($val);
					break;
				case 'pic':
					if(!$val){
						$this->_error="请上传广告图片！";return false;
					}
					$datas['pic'] = htmlspecialchars( trim($val) );
					break;
				case 'url':
                    if($val){
                        if(strstr($val,'http://')){
                            $val = htmlspecialchars($val);
                        }else{
                            $val = 'http://'.htmlspecialchars($val);
                        }
                    }
					if(!$val){
						$this->_error="请填写广告链接地址！";return false;
					}
					$datas['url'] = htmlspecialchars( trim($val) );
					break;
				case 'area':
                    if(is_array($val) && $val){
                        if( $val && count($val) != 34 ){
                            $datas['area'] = join(',', $val);
                        }
                    }else if($val){
                        $areaArr = explode(',',$val);
                        if( $areaArr && count($areaArr) != 34 ){
                            $datas['area'] = $val;
                        }
                    }
					break;
				case 'keywords':
                    $datas[$key] = str_replace(array('，', '、'), ',', trim($val));
					break;
				case 'start_date' :
					$datas[$key] = $val ? strtotime($val) : time();
					break;
				case 'end_date' :
					$datas[$key] = $val ? strtotime($val) : ( time()+360*24*3600 );
					break;
				case 'user_id' :
					if(!$val){
						$this->_error="未登录用户！";return false;
					}
					$datas['user_id'] = intval($val);
					break;
				default :
					$datas[$key] = htmlspecialchars($val);
					break;
			}
		}
		if( $datas['start_date'] > $datas['end_date'] ){
			$this->_error="结束时间不能小于开始时间！";return false;
		}
		$datas['create_time'] = time();
		$datas['type_id'] = 1;
		$datas['status'] = $data['status'] ? $data['status'] : 1;
		return $datas;
	}

    /**
     * 获取广告列表
     * @param array $map 参数地图，字段=>val
     * @param string $field 想要的字段
     * @param bool $order 默认倒排序
     * @param int $limit 截取范围
     * @author 宋小平
     */
    function getAdListByMap($map=null,$field=null,$order=null,$limit=null){
    	$field = $field ? $field : "`id`,`user_id`,`size_id`,`type_id`,`pic`,`title`,`desc`,`keyword_ids`,`keywords`,`url`,`create_time`,`status`,`area`,`is_recommend`,`start_date`,`end_date`";
    	$order = $order ? $order: "`id` DESC";

    	if($map['keyword_ids']){
    		$maps['keyword_ids']=$map['keyword_ids'];
    		unset($map['keyword_ids']);
    	}

        if($map['area']){
            $maps['area']=$map['area'];
            unset($map['area']);
        }

    	if(!$maps){
            $data = $this->getDataList($map, $field, $order, $limit);
			return $data;
    	}else{
            $data = $this->getDataList($map, $field, $order);

		    if($maps['keyword_ids']){
    			$keywordIds = explode(',', $maps['keyword_ids']);
    		}

            if($maps['area']){
                $areaIds = explode(',', $maps['area']);
            }

            foreach($data as $key => $val){
                $dataKeywordIds = explode(',', $val['keyword_ids']);
                $dataAreaIds = explode(',', $val['area']);

                if( !array_intersect($keywordIds, $dataKeywordIds) ){
                    unset($data[$key]);
                }

                if( $dataAreaIds != '' && isset($maps['area']) ){
                    if( ($maps['area'] == '') || !array_intersect($areaIds, $dataAreaIds) ){
                        unset($data[$key]);
                    }
                }
            }

    		if($limit){
    			$lim = explode(',',$limit);
    			return array_slice($data , $lim[0], $lim[1]);
    		}else{
    			return $data;
    		}
		}
    }

    /**
     * 根据广告位id获取广告id的数组
     * @param array $map
     * return array adIdList
     */
    public function getAdIdListByMap(array $map){
    	$map['status'] = 2;
    	$adList = $this->getAdListByMap($map);

    	if(empty($adList)){
    		return false;
    	}else{
    		$i=0;
    		foreach($adList as $key=>$val){
    			$adIdList[$i]['id'] = $val['id'];
    			$adIdList[$i]['area'] = $val['area'];
    			$i++;
    		}
    		return $adIdList;
    	}
    }
}