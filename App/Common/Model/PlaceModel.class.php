<?php 
namespace Common\Model;
    /**
     * 广告位正式表model
     * @author songweiqing
     */
	class PlaceModel extends BaseModel{
		protected $trueTableName='place';
        /**
         *  @desc 字段数据过滤
         *  @param array $data
         *  @return array $datas
         */
		public function parseData(array $data){
			$fields=$this->getFields();
			foreach($data as $key => $val){
				if( !in_array($key, $fields) ){
					continue;
				};
				switch($key){
					case 'name':
						if(!$data['name']){
							$this->_error = '广告位名称不能为空';
							return false;
						}
						$datas[$key] = htmlspecialchars($val);
						break;
					case 'size_id':
						if(!$data['size_id']){
							$this->_error = '请选择广告位尺寸';
							return false;
						}
						$datas[$key] = $val;
						break;
					case 'placeType':
						if(!$data['placeType']){
							$this->_error = '请选择广告位类型';
							return false;
						}
						$datas[$key] = $val;
						break;
					default :
						$datas[$key] = $val;
						break;
				}	
			}
			$datas['update_time'] = time();
			if(isset($data['status'])){
				$datas['status'] = $data['status'];
			}else{
				$datas['status'] = 2;
			}
			return $datas;	
		}
		
		public function getDataByPid($id){
			return $this->where('pid = '.$id)->find();
		}

        /**
         *  @desc 获取域名
         *  @return $string
         */
		public function getWebHost(){
			$parseurl = parse_url($_SERVER['HTTP_REFERER']);
       		return $parseurl['host']; 
		}
		
		/**
		 * @desc 获取广告位的配置信息
		 * @param $id int 
		 */
		public function getPlaceById($id){
			$id = intval($id);
			$map_first['pid'] = $id;
			//$map_first['status'] = 2;
			$placeInfo = $this->getSingleData($map_first);

			if( !empty($placeInfo) ){
				return $placeInfo;
			}
			
			//$map_second['id'] = $id;
			//$map_second['status'] = 2;
			$tempPlaceInfo = D('TempPlace')->getDataById($id);

			if( empty($tempPlaceInfo) ){
				return false;
			}
			
			$placeData['name'] = $tempPlaceInfo['name'];
			$placeData['uid'] = $tempPlaceInfo['uid'];
			$placeData['pid'] = $tempPlaceInfo['id'];
			$placeData['size_id'] = $tempPlaceInfo['size_id'];
			$placeData['keywords'] = $tempPlaceInfo['keywords'];
			$placeData['keyword_ids'] = $tempPlaceInfo['keyword_ids'];
			$placeData['type'] = $tempPlaceInfo['type'];
			$placeData['placeType'] = $tempPlaceInfo['placeType'];
			$placeData['create_time'] = $tempPlaceInfo['create_time'];
			$placeData['status'] = 2;
			
			//调用的域名暂时未存
			$placeData['url']=$this->getWebHost()?$this->getWebHost():'localhost';
			$insert_id = $this->addData($placeData);
			
			//如果第一次调取则可以获取积分
			if($insert_id){
				//添加积分的规则，如果该404有UID
				if($tempPlaceInfo['uid']){
					$methods['method'] = 'home_place_saveadd';
					$list = D('ScoreDeploy')->getDataList($methods);
					$param['uid']    = $tempPlaceInfo['uid'] ? $tempPlaceInfo['uid'] : $_SESSION['userInfo']['id'];;
					$param['points'] = $list[0]['score'];
					$param['desc']   = $list[0]['desc'];
					$userKey = getScoreHistoryKey($tempPlaceInfo['uid'],$methods['method'],$list_id,date('Y-m-d'),time());
					$this->userAddScore($userKey,$param,$tempPlaceInfo['uid']);
				}
			}
			return $this->getDataById($insert_id);
		}

        /**
         * @desc 查询一条数据记录
         * @param array $map
         * @return array 
         */
		public function getSingleData($map){
			$res = $this -> where($map)->find();
			if(!empty($res)){
				return $res;
			}else{
				return false;
			}
		}

        /**
         * @desc 返回广告位调用代码
         * @param int $id
         * @param string $host
         * @return $string
         */
		public	function createCodes($id,$host){
			$id =intval($id);
			$random = $this->getRandNums();
			$code = '<script type="text/javascript"> var yibo_id ='.$id.';</script><script src="'.$host.'/yibo.js?random='.$random.'" type="text/javascript"></script>';
			return $code;
			
		}

        /**
         * @desc 返回6位数的随机数
         * @return $int
         */
		public function getRandNums(){
			return substr(time(),-6,6);
		}
		
		/**
		 * @desc 返回QQ空间的生成代码
		 * @param int $id
		 * @param string $host
		 * @return $string
		 */
		public function createQQCodes($id,$host){
			return $host."/Distribute/index/placeId/".$id;
		}

        /**
         * @desc 返回博客生成代码
         * @param int $id
         * @param string $host
         * @return $string
         */
		public function createBlogCodes($id,$host){
			return '<div><a href="'.$host.'/Index/window" target="_blank"><img src="'.$host.'/Distribute/blog/placeId/'.$id.'" style="max-width: 690px;" /></a></div>';
		}
		
		/**
		 * @desc 请求用户中心添加积分接口
		 */ 
		public function userAddScore($key='',$data = array(),$uid=''){
			if($key){
				$isTrue = D('ScoreHistoryKey')->isHave($key);
				if(!$isTrue) return false;
			}        
			if(empty($data)){
				$method = strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME);
				$data['method'] = $method;
				$list = D('ScoreDeploy')->getDataList($data);
				$param['uid']    = $uid?$uid:$_SESSION['userInfo']['id'];;
				$param['points'] = $list[0]['score'];
				$param['desc']   = $list[0]['desc'];
			}else{
				$param = $data;
			}
			
			$param['from'] = 2;
			//$userUrl = $this->deploy['CENTER_SERVER'];
		
			$userUrl = D('Deploy')->getGlobal('CENTER_SERVER');

			$res = curl_post($userUrl.'/Api/ScoreApi/getScore',$param);  
		}
	
	}
