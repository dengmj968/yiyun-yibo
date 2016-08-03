<?php
namespace Common\Model;
    /**
     * 404广告位流水表model
     * @author songweiqing
     */
	class TempPlace404Model extends BaseModel{
		public $trueTableName='place404_temp';
        /**
         * @desc 字段数据过滤
         * @param array $data
         * @return array $datas
         */
		public function parseData(array $data){
			$fields = $this->getFields();
			foreach($data as $key => $val){
				if(!in_array($key,$fields)){
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
			$datas['status'] = 1;
			$datas['create_time'] = date('Y-m-d H:i:s',time() );
			return $datas;
		}
		
        /**
         * @desc 获取域名
         * @return string $string
         */
		function get_web_host(){
			$parseurl = parse_url($_SERVER['HTTP_REFERER']);
			return $parseurl['host']; 
		}
	 
}
