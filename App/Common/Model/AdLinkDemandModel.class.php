<?php
namespace Common\Model;
/**
 * AdLinkDemandModel
 */
class AdLinkDemandModel extends BaseModel{
    
    public function parseData( array $data ){
		$fields = $this->getFields();
		foreach($data as $key=>$val){
			if( !in_array($key,$fields) ){
				continue;
			}
            if($key != 'create_time'){
                $datas[$key] = intval($val);
            }else{
                $datas[$key] = $val;
            }            
        }
        return $datas;
     }
     
     /**
      * 获取需求作品
      */
     public function getAdByDemandList( array $map,$fields=null,$order=null,$limit=null,$group=null){
        $fields = $fields ? $fields : 'ad.*,ald.accept,ald.user_id as uid,ald.demand_id';
		$order = $order ? $order : 'ald.`id` desc';
		$dataList = $this->alias("ald")->join("LEFT JOIN ad ON ad.id=ald.ad_id")->where($map)->field($fields)->group($group)->order($order)->limit($limit)->select();
		return $dataList;
     }     
}