<?php
namespace Common\Model;
    /**
     *  404广告位model
     *  @author songweiqing
     */
	class Place404Model extends BaseModel{
		public $trueTableName = 'place404';
        /**
         * @desc 字段数据过滤
         * @param array $data
         * @return array $datas
         */
		public function parseData(array $data){
			$fields = $this -> getFields();
			foreach($data as $key => $val){
				if(!in_array($key,$fields) ){
					continue;
				}
				switch($key){
					case 'web_name':
						$datas[$key] = htmlspecialchars($val);
						break;
					default :
						$datas[$key] = $val;
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

        /**
         *  @desc 返回404专题模板编号
         *  @return array $data
         */
		public function get404Models(){
			return array(
				"1"=>"模板1","2"=>"模板2",'3'=>'模板3','4'=>'幸福列车','5'=>'通缉犯','6'=>'地震救灾','7'=>'宜农贷'
			);
		}
		
        /**
         *  @desc 根据条件获取单条数据
         *  @param array $map
         *  @return array $data
         */
		public function getSingleData($map){
			return $this->where($map)->find();
		}
        
		/**
		 * @desc 获取调用代码
		 * @param array $config
		 * @param string $host
		 */	
		public function getDefaultCode($config,$host){
			if(isset($config['list_id']) ){
				$ids = $config['list_id'];	
			}else{
				$ids = $config['id'];
			}
			return "<iframe scrolling='no' frameborder='0' src='".$host."/Home/Distribute/ad404/key/".$ids."' width='654' height='470' style='display:block;'></iframe>";
		}
		
		/**
		 * 调取404广告位（该方法由宋小平重写）
		 * 基本业务逻辑：主表list_id为副表ID
		 * 1、当没有key并且没有抓到域名的返回默认广告
		 * 2、key和域名都存在反会广告位
		 * 3、key存在host不存在，创建一个新的广告位（一个key多个host使用）
		 * 4、key不存在去副表查询，存在返回并存入主表
		 * 5、key主副表都不存在
		 * 
		 * @param int $list_id
		 */
		public function	getPlace404ById($list_id,$oldUrl){
			//获取url，优先取老表中的url
			$url = $oldUrl ? base64_decode($oldUrl) : $this->getWebHost();
			$urlList = parse_url($url);
			$host = $urlList['host'] ? $urlList['host'] : '';
			
			//1、没有key和url的返回默认
			if(!$list_id && !$host){
				return $this->getDefaultConfigs(0);
			}
			
			//组合查询条件
			$host && $map['web_host'] = $host;
			$map['list_id'] = intval($list_id);
			$map['status'] = 2;
 
			//2、第一次严格匹配主表，含域名和list_id和状态;
			//如果查到就返回数据，并判断更新访问时间
			$placeInfo = $this->getDataByMap($map);
			if($placeInfo){
				$last_select_date = date('Y-m-d');
				if($placeInfo['last_select_date'] != $last_select_date){
					$this->where($map)->save(array('last_select_date' =>$last_select_date));//更新最后访问日期
				}
				return $placeInfo;
			}
			
			//3、第二次较严格匹配,只通过list_id和状态来匹配；
			if($map['web_host']){
				unset($map['web_host']);
				$placeInfo = $this->getDataByMap($map);
			}
			if($placeInfo){     //如果查询到生存新的广告位，相同的list_id,但是域名不一样，解决一个广告位多个站点使用
				$data = $placeInfo;
				unset($data['id']);
				$data['web_host'] = $host;
				$data['create_time'] = date('Y-m-d H:i:s');
				$data['update_time'] = time();
				$data['web_name'] = $host;
				$data['last_select_date'] = date('Y-m-d');
				$data['uid'] = 0;
				$id = $this->addData($data);
				return $data;
			}else{
				$placeInfo = D('TempPlace404')->getDataById($list_id);
				if(!$placeInfo){
					$placeInfo = M('place404_total')->where("id = {$list_id}")->find();
				}
				if(!$placeInfo){
					if($host){
						$data['mid'] = 1;
						$data['web_host'] = $map['web_host'];
						$data['create_time'] = date('Y-m-d H:i:s');
						$data['update_time'] = time();
						$data['web_name'] = $map['web_host'];
						$data['last_select_date'] = date('Y-m-d');
						$data['status'] = 2;
						$data['list_id'] = $list_id;
						$data['is_push'] = $placeInfo['is_push'];
						$data['uid'] = 0;
						$id = $this->addData($data);
						return $data;
					}
					return $this->getDefaultConfigs(0);
				}else{
					$data = $placeInfo;
					unset($data['id']);
					$data['web_host'] = $host;
					$data['create_time'] = date('Y-m-d H:i:s');
					$data['status'] = 2;
					$data['list_id'] = $list_id;
					$data['update_time'] = time();
					$data['last_select_date'] = date('Y-m-d');
					$data['is_old'] = $oldUrl ? 1 : 0;
					if($data['web_host']){
						$res = $this->addData($data);
					}
					if($res){
						$data['id'] = $res;
						//添加积分的规则，如果该404有UID
						if($data['uid']){
							$methods['method'] = 'home_place404_insertadd';
							$list = D('ScoreDeploy')->getDataList($methods);
							$param['uid']    = $data['uid'] ? $data['uid'] : $_SESSION['userInfo']['id'];;
							$param['points'] = $list[0]['score'];
							$param['desc']   = $list[0]['desc'];
							$userKey = getScoreHistoryKey($data['uid'],$methods['method'],$list_id,date('Y-m-d'),time());
							$this->userAddScore($userKey,$param,$data['uid']);
						}
						return $data;
					}else{
						return $this->getDefaultConfigs(0);
					}
					
				}
			}			
		}
        
        /**
         * @desc 返回默认广告位
         * @param int $uid
         * @return array 
         */
		public function getDefaultConfigs($uid){
			return array(
					'web_name'=>'404广告位',
					'web_host'=>'',
					'colors'=>1,
					'mid'=>1,
					'id' =>0,
					'status'=>2,
					'uid'=>$uid,
			);
		}
		
        /**
         *  @desc 获取一条广告投放于404木板上
         *  @return array
         */
    	public function getWebInfo(){
    		$data = S('ad_404');
    		$single = array();
    		if(empty($data) ){
    			$data = D('Web404')->getDataList();
    			S('ad_404',$data,24*3600);
    		}
    		$randKey = array_rand($data,1);
    		$single = $data[$randKey];
    		$single["desc"] = mb_substr($single["desc"],0,80,"utf-8");
    		return $single;
    	}
	
    	/**
    	 * @desc返回模板名称
    	 * @param int mid
    	 * @return $string
    	 */
    	public function getTemplateModels($mid = 1){
    		$mid = intval($mid);
    		$models = $this->get404Models();
    		return $models[$mid];
    	}
	
    	/**
    	 * @desc获取域名
    	 * @return $string
    	 */
    	public function getWebHost(){
    		//$parseurl = parse_url($_SERVER['HTTP_REFERER']);
    		//return $parseurl['host'];
    		return $_SERVER['HTTP_REFERER'];
    	}
	
        /**
         *  @desc 根据模板获取该模板中的广告尺寸,暂时不能通用
         *  @param int $tempId 
         *  @return int $ad_id
         */
    	public function getAdSizeByTemp($tempId){
    		$AdSizeTemp = array('1' => 13,'2' => 13,'3' => 13,'4' => null,'5' => null);
    		return $AdSizeTemp[$tempId];
    	}
		
		//设计师是默认404页面
		public function getDefaultInfo(){
			$array = array(
				'pic'=>'/Public/images/404_template_default.jpg',
				'title'=>'益播社会创新中心',
				'desc'=>'益云是一家致力于“通过互联网推动公益发展与社会创新”的社会企业，旨在为公益和社会创新提供互联网信息技术解决方案，以i助爱，通过IT技术释放公益与社会创新的创益生产力!',
				//购买者
				'buyer'=>array(
						'extend'=>array(
								'desc'=>'设计师404公益正式上线，商家赞助的404设计作品将会出现在各大平台的404页面上，赞助者可以通过404作品这个渠道，投放自己的链接和品牌logo，来获取更多的流量和展示渠道。'
						),
						'logos'=>'/Public/images/logo.png',
						'logo_url'=>'http://yibo.iyiyun.com'
				),
				//设计者
				'deger'=>array(
						'extend'=>array(
								'user_name'=>'益云社会创新中心',
								
						)	
				)
			);
			return $array;

		}
	
}
