<?php namespace Common\Model;
namespace Common\Model;
    /**
     * 广告评论model
     * @author songweiqing
     */
	class AdCommentModel extends BaseModel{
		protected $trueTableName ='ad_comment';
		/**
		 * @desc    字段过虑
		 * @return  $datas array
		 **/
		public function parseData(array $data){
			$fields = $this->getFields();
			foreach($data as $key => $val){
				if( !in_array($key,$fields) ){
					continue;
				}
				switch($key){
					case 'content':
						$datas[$key] = htmlspecialchars($val);
						break;
					default:
						$datas[$key] = $val;
						break;
				}
			}
			$datas['create_time'] = date('Y-m-d H:i:s');
			return $datas;	
		}
	}

