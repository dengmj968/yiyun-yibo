<?php
namespace Common\Model;
/**
 * 添加积分记录Key_model
 * @author liuqiuhui
 * @createTime 2014-12-31
 */
class ScoreHistoryKeyModel extends BaseModel
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
            $datas[$key] = $val;
        }

        $datas['create_time'] = time();
        return $datas;
    }
    
    /**
     * @desc 判断是否首次添加积分
     * @param $key 
     */ 
    public function isHave($key){
        if($key){
            $id = $this->where("key = '$key'")->getField('id');
            if($id){
                return false;
            }else{
                $data['key'] = $key;
                $data['create_time'] = time();
                $id = $this->add($data);
                return true;
            }
        }
        return true;
    }
    
}