<?php
namespace Common\Model;
class FaqsModel extends BaseModel
{
    public function parseData( array $data ){
        $fields = $this->getFields();
        foreach($data as $key=>$val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'question':
                    if(!$val){
                        $this->_error="标题不能为空！";return false;
                    }
                    $datas['question'] = htmlspecialchars($val);
                    break;
                case 'answer':
                    $datas['answer'] = $val;
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }
        $datas['update_time'] = time();
        return $datas;
    }
}
