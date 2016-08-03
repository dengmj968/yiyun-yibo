<?php
namespace Common\Model;
	class Ad404Model extends BaseModel{
		public $trueTableName = 'web404';
		
		public function parseData(array $data){
			return $data;
		}
		
		
		
	}