<?php
namespace Common\Model;
/**
 * 活动模块model
 * @author songweiqing
 */
class ActivityModel extends BaseModel
{
	/**
	 * @desc 数据字段过滤
	 * @param array $data
	 * @return array $datas
	 */
    public function parseData( array $data ){
        $fields = $this->getFields();
        foreach($data as $key=>$val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'title':
                    if(!$val){
                        $this->_error="标题不能为空！";
                        return false;
                    }
                    $datas['title'] = htmlspecialchars($val);
                    break;
                case 'initiator':
                    if(!$val){
                        $this->_error="发起人不能为空！";
                        return false;
                    }
                    $datas['initiator'] = htmlspecialchars($val);
                    break;
                case 'time':
                    if(!$val){
                        $this->_error="发起时间不能为空！";
                        return false;
                    }
                    $datas['time'] = $val;
                    break;
                case 'url':
                    if(!$val){
                        $this->_error="活动链接地址不能为空！";
                        return false;
                    }
                    $datas['url'] = $val;
                    break;
                case 'pic':
                    if(!$val){
                        $this->_error="背景图片不能为空！";
                        return false;
                    }
                    $datas['pic'] = $val;
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }

        if(ACTION_NAME == 'saveAdd') $datas['is_show'] = 0;
        return $datas;
    }
}
