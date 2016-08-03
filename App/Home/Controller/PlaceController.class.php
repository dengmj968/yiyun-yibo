<?php 
namespace Home\Controller;
	/**
	 * @desc 普通广告位
	 * @author songweiqing
	 */
	class PlaceController extends CommonController{		
		/**
		 * @desc 站长广告位
		 */
		 public function index(){
			//获取用户ID
			$uid = $this->getUid();
			$where =" 1 and uid = ".$uid;
			if( isset($_POST['name']) && $_POST['name'] != '' ){
				$where .= " and name like '%".$_POST['name']."%' ";
				$searchs['name'] = htmlspecialchars($_POST['name']);
			}
			if( isset($_POST['size_id'] ) && $_POST['size_id'] != ''){
				$where .= " and size_id = ".$_POST['size_id'];
				$searchs['size_id'] = $_POST['size_id'];
			}
			import('@.ORG.Page');
			//联合查询临时表和正式表。并过滤临时表的数据
			$sql_nums = 'select id,name,pid,status,size_id,create_time,placeType from place where '.$where;
			$sql_nums .= ' union all select id,name,id = 0 as pid,status,size_id,create_time,placeType from place_temp where '.$where.' and id not in (select pid from place where uid = '.$uid.')'; 
			$placeList_nums = M()->query($sql_nums);
			$count = count($placeList_nums);
			$Page = new Page($count,5);
			$show = $Page->show(); 
			
			$sql = 'select id,name,pid,status,size_id,create_time,placeType from place where '.$where;
			$sql .= ' union all select id,name,id = 0 as pid,status,size_id,create_time,placeType from place_temp where '.$where.' and id not in (select pid from place where uid = '.$uid.') order by `create_time` desc limit '.$Page->firstRow.','.$Page->listRows; 
			$placeList = M()->query($sql);
			$size = D('AdSizeConfig')->getDataList('','','`width` asc','');	
			$this->assign('size',$size);
			$this->assign('placeList',$placeList);
			$this->assign('show',$show);
			$this->assign('searchs',$searchs);
			$this->display();
		 
		 }
		
		/**
		 * @desc 添加广告位
		 */
		public function add(){
			//关键字分类
			$keywords = M('keywords_category')->select();
			//获取尺寸数据
			$map['size_id'] = 10;
			$map['status'] = 2;
			$map['type'] = 1;
			$list = D('Ad')->getDataList($map,'',"",4);
			$adSizes = D('AdSizeConfig')->getDataList('','','`width` asc','');
			$this->assign('adSizes',$adSizes);
			$this->assign('keywords',$keywords);
			$this->assign('list',$list);
			$this->display();
		}
		
		/**
		 * @desc保存广告位
		 */
		public function saveAdd() {
			$deploy = $this->deploy;
			$uid = $this->getUid();
			$map['uid'] = $uid;
			$map['size_id'] = $_POST['size_id'];
			$map['placeType'] = $_POST['placeType'];
			$_POST['uid'] = $this->getUid();

            $formalres = D ( $this->className )->getSingleData ( $map );
		
			//主表有直接返回代码
			if( $formalres ) {
				if($_POST['placeType'] != 2){
					$code = D($this->className)->createCodes($formalres['pid'],$deploy['YIBO_YU']);
				}else{
					$code = D($this->className)->createBlogCodes($formalres['pid'],$deploy['YIBO_YU']);
				}
				$this->ajaxReturn($code,1,1);
			}
			//临时表存在博客的话需要直接就存入正式表中
			$tempres = D('TempPlace')->getSingleData($map);
			if($tempres){
				if($_POST['placeType'] != 2 ){
					$code = D($this->className)->createCodes($tempres['id'],$deploy['YIBO_YU']);
				}else{
					//博客广告位
					$code = D($this->className)->createBlogCodes($tempres['id'],$deploy['YIBO_YU']);
				}
				$this->ajaxReturn($code,1,1);
			}
			$id = D ( 'TempPlace' )->addData ( $_POST );
			
			if ($id) {
				//成功添加广告位，添加3积分
				$this->userAddScore();
				if($_POST['placeType'] != 2){
					$code = D($this->className)->createCodes($id,$deploy['YIBO_YU']);
				}else{
					$code = D($this->className)->createBlogCodes($id,$deploy['YIBO_YU']);
				}
				$this->ajaxReturn($code,1,1);
			}
		}
		
		/**
		 *  @desc 编辑广告位 
		 *  $pid int 0-临时表 1-正式表
		 */
		public function edit(){
			$keywords = D('keywords_category')->select();
			$size = D('AdSizeConfig')->getDataList('','','`width` asc','');
			$id = intval($_GET['id']);
			$pid = intval($_GET['pid']);
			if($pid == 0){
				$placeInfo = D('TempPlace')->getDataById($id);
			}else{
				$placeInfo = D('Place')->getDataById($id);
			}
			$this->assign('size',$size);
			$this->assign('pid',$pid);
			$this->assign('keywords',$keywords);
			$this->assign('placeInfo',$placeInfo);
			$this->display();
		}
		
		/**
		 * @desc 保存编辑
		 */	
		public function saveEdit(){
			$pid = intval($_POST['pid']);
			$uid = $this->getUid();
			if($pid == 0){
				$save_id = D('TempPlace')->saveDataById($_POST);
			}else{
				$save_id = D('Place')->saveDataById($_POST);
			}
			if($save_id){	
				$this -> ajaxReturn($save_id,1,1);
			}
		}
		
		/**
		 * @desc 停用/启用广告位
		 */
		public function resetPlace(){
			$pid = $_POST['pid'];
			if($_POST['status'] == 2){
				$data['status'] = 3;
				$info = '已停用';
			}else{
				$data['status'] = 2;
				$info = '使用中';
			}
			$data['id'] = intval($_POST['id']);
			if($pid != 0){
				$save_id = D('Place')->saveDataById($data);
			}else{
				$save_id = D('TempPlace')->saveDataById($data);	
			}
 			if($save_id){
				$this -> ajaxReturn($data,$info,1);
			} 
		}
		
		/**
		 * @desc 删除广告位
		 */
		public function delPlace(){
            //获取用户ID
            $uid = $this->getUid();
			$id = intval($_POST['id']);
			$pid = $_POST['pid'];
			if($pid == 0){
                $userId = D('TempPlace')->getDataById($id,"uid");
                if($userId['uid'] != $uid) {
                    $this->ajaxReturn(false,flase,0);
                }else{
                    $del_id = D('TempPlace')->delById($id);
                }

			}else{
                $userId = D('Place')->getDataById($id,"uid");
                if($userId['uid'] != $uid) {
                    $this->ajaxReturn(false,false,0);
                }else{
                    $del_id = D('Place')->delById($id);
                }
			}
			if($del_id){
				$this->ajaxReturn($del_id,1,1);
			}
		}
		
		/**
		 * @desc 获取广告位的配置信息
		 */
		public function getPlace(){
		
			 $info = D($this->className)->getPlaceById(2); 
			 dump($info);
			 exit;
		}
		
		/**
		 * @desc 显示图片
		 */
		public function getPicById(){
			$id = intval($_POST['id']);
			//调取默认广告
			$picInfo = D('AdSizeConfig')->field('pic')->find($id);
			//调取列表广告
			$map['size_id'] = $id;
			
			//根据尺寸定义图片的数量和类的属性
			if(in_array($id,array(25,24))){
				$types = 3;
				$getNum = 18;
			}elseif(in_array($id,array(18,2)) ){
				$types = 3;
				$getNum = 12;
			}elseif(in_array($id,array(17)) ){
				$types = 3;
				$getNum = 3;
				//type3 end
			}elseif(in_array($id,array(19,6,7,20,21))){
				$types = 2;
				$getNum = 8;
			
			}elseif(in_array($id,array(1)) ){
				$types = 2;
				$getNum = 2;
			
			}elseif(in_array($id,array(10))){
				$types = 2;
				$getNum = 4;
			
			}elseif(in_array($id,array(14))){
				$types = 2;
				$getNum = 12;
				//type2 end
			}elseif(in_array($id,array(54))){
				$types = 1;
				$getNum = 3;
			
			}elseif(in_array($id,array(23,8,9))){
				$types = 1;
				$getNum = 2;
			
			}elseif(in_array($id,array(35))){
				$types = 1;
				$getNum = 4;
			
			}elseif(in_array($id,array(13,16,3,4,5))){
				$types = 1;
				$getNum = 6;
			}else{
				$types = 3;
				$getNum = 18;
			
			}
			$map['status'] = 2;
			$map['type'] = 1;
			$list = D('Ad')->getDataList($map,'',"create_time desc",$getNum);
 			if(empty($list)){
				$map['size_id'] = 10;
				$list = D('Ad')->getDataList($map,'',"create_time desc",4);
				$types = 3;	
			} 
			$picInfo['picList'] = $list;
			$picInfo['types'] = $types;
			$this->ajaxReturn($picInfo,1,1);
		}
		
		/**
		 * @desc 获取用户uid
		 */
		public function getUid(){
			$userInfo = $this->getUserInfo();
			return $userInfo['id'];
		}
		
		/**
		 * @desc 调用代码
		 * @param $pid int 0-临时表 1-正式表
		 */
		public function getCodes(){
			$deploy = $this->deploy;
			$id = intval($_POST['id']);
			$pid = intval($_POST['pid']);
			//调取广告位的调用代码
			if($pid != 0){
				$placeInfo = D($this->className)->getDataById($id);
				$id = $placeInfo['pid'];
				if($_POST['placeType'] == 1 ){
					$codes = D($this->className)->createCodes($id,$deploy['YIBO_YU']);
				}else{
					$codes = D($this->className)->createBlogCodes($id,$deploy['YIBO_YU']);
				}
			}else{
				if($_POST['placeType'] == 1 ){
					$codes = D($this->className)->createCodes($id,$deploy['YIBO_YU']);
				}else{
					$codes = D($this->className)->createBlogCodes($id,$deploy['YIBO_YU']);
				}		
			}
		
			$this->ajaxReturn($codes,1,1);
		}

	}