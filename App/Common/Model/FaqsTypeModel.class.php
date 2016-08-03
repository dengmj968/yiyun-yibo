<?php
namespace Common\Model;
class FaqsTypeModel extends BaseModel
{
    public function parseData( array $data ){
        $fields = $this->getFields();
        foreach($data as $key=>$val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'name':
                    if(!$val){
                        $this->_error="名称不能为空！";return false;
                    }
                    $datas['name'] = htmlspecialchars($val);
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }
        return $datas;
    }
}
