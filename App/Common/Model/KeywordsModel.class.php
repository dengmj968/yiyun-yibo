<?php
namespace Common\Model;
	class KeywordsModel extends BaseModel{
		public function parseData( array $data){
			$fields = $this->getFields();
			foreach($data as $key => $val){
				if(!in_array($key,$fields)){
					continue;
				}
				switch($key){
					case 'value':
						if(!$val){
							$this->_error='关键字不能为空';
							return false;
						}
						$datas[$key]=$val;
					case 'category_id':
						if(!$val){
							$this->_error='请选择一个分类类别';	
							return false;
						}
						$datas[$key]=$val;
						break;
					default :
						$datas[$key]=$val;
				}
			}
			return $datas;
		}

        /**
         * 根据名称得到id
         * @param string  关键字
         * @return int  id
         */
        function getIdByValue($value = '')
        {
            $value = filter_str($value);
            return $this->where("value='$value'")->getField("id");
        }
	}
