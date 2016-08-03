<?php 
namespace Common\Model;
/**
 * 站内信系统消息配置_Model
 * @author liuqiuhui
 * @createTime 2014-11-21
 */ 
class MessageConfigModel extends BaseModel{

    public $trueTableName = 'message_config';
    public $tableName     = 'method';
    
    /**
     * 字段过滤
     * @param array $data
     * @return array
     */ 
    public function parseData(array $data){
        $fields=$this->getFields();
        foreach($data as $key => $val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'method_id':
                    if(!$val){
                        $this->_error('请选择操作！');
                        return false;
                    }
                    $datas[$key] = $val;
                break;
                case 'title':
                    if(!$val){
                        $this->_error('请填写消息标题！');
                        return false;
                    }
                    $datas[$key] = htmlspecialchars($val);
                break;
                case 'content':
                    if(!$val){
                        $this->_error('请填写消息内容！');
                        return false;
                    }
                    $datas[$key] = htmlspecialchars($val);
                break;
                default:
                    $datas[$key]=$val;
                break;
            }
        }
        $datas['update_time'] = time();
        return $datas;
    }
    
    /**
     * 获取数据结果集
     * @param array $map 条件数据
     * @param $fields 字段
     * @param $order  排序
     * @param $limit  显示条数
     * @return array
     */ 
    public function getMessageConfigList( $map,$fields=null,$order=null,$limit=null){
		$fields = $fields ? $fields : "$this->trueTableName.*,$this->tableName.title as name";
		$order = $order ? $order : "$this->trueTableName.`id` desc";
		$dataList = $this->join("$this->tableName ON $this->trueTableName.method_id = $this->tableName.id")->where($map)->field($fields)->order($order)->limit($limit)->select();
        return $dataList;
        
	}
    
    /**
     * 添加站内信系统配置数据
     * @param array $data 
     * @return array
     */ 
    public function addMessageConfig(array $data){
        $data=$this->parseData($data);
        if(!$data){
            return false;
        }
        $methodInfo = D("Method")->getDataById($data['method_id']);
        $data['method'] = $methodInfo['group'].'_'.$methodInfo['class'].'_'.$methodInfo['method'];
        $data['create_time'] = date("Y-m-d H:i:s");
        $id=$this->add($data);
        if($id){
            return $id;
        }else{
            $this->_error='添加系统消息配置失败';
            return false;
        }
    }

    /**
     * 修改站内信系统配置数据
     * @param array $data 
     * @return array
     */
    public function saveMessageConfig(array $data){
        $data=$this->parseData($data);
        if(!$data){
            return false;
        }
        $methodInfo = D("Method")->getDataById($data['method_id']);
        $data['method'] = $methodInfo['group'].'_'.$methodInfo['class'].'_'.$methodInfo['method'];
        $id=$this->save($data);
        if($id){
            return $id;
        }else{
            $this->_error='修改系统消息配置失败';
            return false;
        }
    }
}