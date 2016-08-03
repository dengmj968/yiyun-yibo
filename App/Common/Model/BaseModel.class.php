<?php
namespace Common\Model;
use Think\Model;
/**
 * 基础model
 * @author 宋小平
 * @version 1.0
 * @create time 2014-04-11
 */
abstract class BaseModel extends Model{
	public $_error='';
	
	/**
	 * 抽象方法，子类实现，用于数据过滤
	 * @param array $data
	 */
	abstract function parseData( array $data );
	
	/**
	 * 获取最后一个错误信息
	 */
	public function getLastError(){
		return $this->_error;
	}
	
	/**
	 * 获取本表字段列表
	 * @return array 字段
	 */
	public function getFields(){
		$fields = $this->fields;
		foreach($fields as $k=>$v){
			if(!is_numeric($k)){
				unset($fields[$k]);
			}
		}
		return $fields;
	}
	
	/**
	 * 获取整的数据条数
	 * @param array $map
	 */
	public function getCount($map){
		return $this->where($map)->count();
	}
	
	/**
	 * 获取最后一条数据ID
	 */
	public function getLastId(){
		$info = $this->field('id')->order("`id` desc")->limit("0,1")->select();
		return $info[0]['id'];
	}
	
	/**
	 * 验证用户是否对数据有权限
	 * @param int $id
	 * @param int $uid
	 * @param string $field
	 * @return boolean
	 */
	public function isHaveJurisdiction( int $id, int $uid=null, string $field=null ){
		$uid = $uid ? $uid : $_SESSION['userInfo']['id'];
		$field = $field ? $field : 'uid';
		$map['id'] = $id;
		$map[$field] = $uid;
		$data = $this->getDataByMap( $map );
		return $data ? true : false;
	}
		
	/**
	 * 添加数据
	 * @param array $data
	 */
	public function addData( array $data ){
		$data = $this->parseData($data);
		$data = $this->beforeSave($data);
		if(!$data){
			return false;
		}
		$id = $this->add($data);
		if($id){
			return $id;
		}else{
			$this->_error="添加失败！";
			return false;
		}
	}
	
	/**
	 * 根据ID删除数据
	 * @param int $id
	 */
	public function delById($id){
		$id = intval($id);
		if(!$id){
			$this->_error = "要删除的数据ID不能为空！";
			return false;
		}
		$id = $this->where("id=$id")->delete();
		return $id;
	}
	
	/**
	 * 根据ID保存数据
	 * @param array $data 数据数组，必须含有主键ID
	 * @return boolean
	 */
	public function saveDataById($data){
		$id = intval($data['id']);
		$data = $this->parseData($data);
		$data  = $this->beforeSave($data);
		if(!$id || !$data){
			return false;
		}
		$option = $data;
		unset($option['id']);
		$result = $this->where("id = {$id}")->save($option);
		if($result){
			return $result;
		}else{
			$this->_error = "保存失败！";
			return false;
		}
	}
	
	/**
	 * 根据ID获取数据
	 * @param int $id
	 * @param string $fields
	 */
	public function getDataById(int $id,string $fields=null){
		$id = intval($id);
		$fields = $fields ? $fields : '*';
		$info = $this->where("id=$id")->field($fields)->find();
		$info = $this->afterGetData($info);
		return $info;
	}
	
	/**
	 * 根据地图查询数据
	 * @param array $map
	 * @param string $fields
	 */
	public function getDataByMap( $map ,string $fields=null){
		$fields = $fields ? $fields : '*';
		$info = $this->where( $map )->field($fields)->find();
		$info = $this->afterGetData($info);
		return $info;
	}
	
	/**
	 * 获取数据列
	 * @param array $map
	 * @param string $fields
	 * @param string $order
	 * @param string $limit
	 */
	public function getDataList( $map,$fields=null,$order=null,$limit=null){
		$fields = $fields ? $fields : '*';
		$order = $order ? $order : '`id` desc';

        $dataList = $this->where($map)->field($fields)->order($order)->limit($limit)->select();
		return $dataList;
	}
	
	/**
	 * 数据返回前处理方法
	 * @param array $data
	 */
	public function afterGetData( $data ){
		if( !is_array($data) ){
			return false;
		}
		$data = $this->un_serialize( $data );
		return $data;
	}
	
	/**
	 * 數據寫入前的處理方法
	 * @param array $data
	 */
	public function beforeSave( array $data ){
			$data = $this->on_serialize( $data );
		return $data;
	}
	
	/**
	 * 判断是否是序列化数据
	 * @param unknown_type $data
	 */
	public function is_serialized( $data ) {
		$data = trim( $data );
		if ( 'N;' == $data )
			return true;
		if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
			return false;
		switch ( $badions[1] ) {
			case 'a' :
			case 'O' :
			case 's' :
				if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
					return true;
				break;
			case 'b' :
			case 'i' :
			case 'd' :
				if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
					return true;
				break;
		}
		return false;
	}
	
	/**
	 * 解开序列化
	 * @param array $data
	 */
	public function un_serialize(array $data){
		foreach($data as $key=>$val){
			if( $this->is_serialized($val) ){
				$data[$key] = unserialize($val);
			}
		}
		return $data;
	}
	
	/**
	 * 对数据进行序列化
	 * @param array $data
	 */
	public function on_serialize(array $data){
		foreach($data as $key => $val){
			if(is_array($val)){
				$data[$key] = serialize($val);
			}
		}
		return $data;
	}
	
	/**
	 +----------------------------------------------------------
	 * 字符串截取，支持中文和其它编码
	 +----------------------------------------------------------
	 * @static
	 * @access public
	 +----------------------------------------------------------
	 * @param string $str 需要转换的字符串
	 * @param string $start 开始位置
	 * @param string $length 截取长度
	 * @param string $charset 编码格式
	 * @param string $suffix 截断显示字符
	 +----------------------------------------------------------
	 * @return string
	 +----------------------------------------------------------
	 */
	function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
		if(function_exists("mb_substr"))
			$slice = mb_substr($str, $start, $length, $charset);
		elseif(function_exists('iconv_substr')) {
			$slice = iconv_substr($str,$start,$length,$charset);
		}else{
			$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
			$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
			$re['gbk']	  = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
			$re['big5']	  = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
			preg_match_all($re[$charset], $str, $match);
			$slice = join("",array_slice($match[0], $start, $length));
		}
		if($suffix && $str != $slice) return $slice."...";
		return $slice;
	}
	
	/**
	 * @desc 请求用户中心添加积分接口
	 * @param $key string
	 * @param $data array default null
	 * @param $uid int
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