<?php
namespace Common\Model;
class IdeaModel extends BaseModel {
	public function parseData(array $data) {
		$fields = $this->getFields ();
		foreach ( $data as $key => $val ) {
			if (! in_array ( $key, $fields )) {
				continue;
			}
			switch ($key) {
				case 'email' :
					if (! $val) {
						$this->_error = "email不能为空！";
						return false;
					}
					$datas ['email'] = htmlspecialchars ( $val );
					break;
				case 'desc' :
					if (! $val) {
						$this->_error = "建议不能为空！";
						return false;
					}
					$datas ['desc'] = $val;
					break;
				default :
					$datas [$key] = $val;
					break;
			}
		}
		$datas ['create_time'] = time ();
		return $datas;
	}
}
