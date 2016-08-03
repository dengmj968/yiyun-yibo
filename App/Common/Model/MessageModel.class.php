<?php 
namespace Common\Model;
    /**
     * 消息发送model
     * @author songweiqing
     */
	class MessageModel extends BaseModel{
		/**
		 * @desc 字段数据过滤
		 * @param array $data
		 * @return array $datas
		 */
		public function parseData(array $data){
			$fields=$this->getFields();
			foreach($data as $_key => $_val){
				if(!in_array($_key,$fields)){
					continue;
				}
				switch($_key){
					case 'title':
						$datas['title']=$_val;
							if(!$datas['title']){
								$this->_error='标题不能为空';
								return false;
							}
						break;
					case 'content':
						$datas['content']=htmlspecialchars_decode(stripslashes($_val));
						if(!$datas['content']){
							$this->_error='内容不能为空';
							return false;
						}
						break;
					default :
						$datas[$_key]=$_val;
						break;
				}	
			}
			$datas['create_time']=time();
			return $datas;
		}
	}
