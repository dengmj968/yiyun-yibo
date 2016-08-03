<?php
namespace Common\Model;
/**
 * @desc    积分配置信息Model
 * @author  liuqiuhui
 * @createTime 2014-12-2
 */ 
class ScoreDeployModel extends BaseModel
{   
    public $trueTableName = 'score_deploy';
    public $tableName     = 'method';
    
    /**
     * 字段过滤
     * @param array $data
     * @return array
     */
    public function parseData( array $data ){
        $fields = $this->getFields();
        foreach($data as $key=>$val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'method_id':
                    if(!$val){
                        $this->_error="操作方法不能为空！";
                        return false;
                    }
                    $datas['method_id'] = htmlspecialchars($val);
                    break;
                case 'score':
                    if(!$val){
                        $this->_error="积分不能为空!";
                        return false;
                    }
					$res = preg_match("/^-?\d+$/", $val);
					if(!$res){
						$this->_error="请输入数字!";
						return false;
					}
                    $datas['score'] = html_entity_decode($val);
                    break;
                case 'desc':
                    $datas['desc'] = htmlspecialchars($val);
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }
        $datas['update_time'] = time();
        return $datas;
    }

	/**
	 * 检测是否存在，新增/修改时用
	 * @param  string $type 枚举值 add/edit
	 * @param  string $value  size_name 尺寸名称
	 * @param  int $id 在修改时才有用，修改条目的id
	 * @return bool
	 */
	public function checkIsHaving($type = "add", $value = "", $id = 0)
	{
		$value = filter_str($value);
		$id = intval($id);
		if ($type == "add") {
			return D("ScoreDeploy")->where("method_id='$value'")->getField("id");
		} else {
			return D("ScoreDeploy")->where("id !={$id} and method_id='$value' ")->getField("id");
		}
	}
    
    /**
     * 获取数据结果集
     * @param array $map 条件数据
     * @param $fields 字段
     * @param $order  排序
     * @param $limit  显示条数
     * @return array
     */ 
    public function getScoreDeployList( $map,$fields=null,$order=null,$limit=null){
		$fields = $fields ? $fields : "$this->trueTableName.*,$this->tableName.title as name";
		$order = $order ? $order : "$this->trueTableName.`id` desc";
		$dataList = $this->join("$this->tableName ON $this->trueTableName.method_id = $this->tableName.id")->where($map)->field($fields)->order($order)->limit($limit)->select();
        return $dataList;        
	}

    /**
	 * @desc 获取积分配置表里面方法名'method'的数组集合
     * @return array
     */
    public function getMethodList(){
        $methodList = $this->getDataList('', 'method');
        foreach($methodList as $key => $val){
            $methodArr[] = $val['method'];
        }
        return $methodArr;
    }
    
    /**
     * 添加站内信系统配置数据
     * @param array $data 
     * @return array
     */ 
    public function addScoreDeploy(array $data){
        $data   = $this->parseData($data);        
        if(!$data){
            return false;
        }
        $isHave = $this->checkIsHaving("add", $data['method_id']);
        if($isHave){
            $this->_error='该操作方法已存在！';
            return false;
        }
        $methodInfo = D("Method")->getDataById($data['method_id']);
        $data['method'] = strtolower($methodInfo['group'].'_'.$methodInfo['class'].'_'.$methodInfo['method']);
        $data['create_time'] = date("Y-m-d H:i:s");
        $id=$this->add($data);
        if($id){
            return $id;
        }else{
            $this->_error='添加积分系统配置失败';
            return false;
        }
    }

    /**
     * 修改站内信系统配置数据
     * @param array $data 
     * @return array
     */
    public function saveScoreDeploy(array $data){
        
        $data=$this->parseData($data);
        if(!$data){
            return false;
        }
        $isHave = $this->checkIsHaving("edit", $data['method_id'],$data['id']);
        if($isHave){
            $this->_error='该操作方法已存在！';
            return false;
        }
        $methodInfo = D("Method")->getDataById($data['method_id']);
        $data['method'] = strtolower($methodInfo['group'].'_'.$methodInfo['class'].'_'.$methodInfo['method']);
        $id=$this->save($data);
        if($id){
            return $id;
        }else{
            $this->_error='修改积分系统配置失败';
            return false;
        }
    }
}
