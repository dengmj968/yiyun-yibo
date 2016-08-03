<?php
namespace Common\Model;
class ScoreHistoryModel extends BaseModel
{
    public function parseData( array $data ){}

	/**
	 * 用户根据方法添加积分
	 * @param $method
	 * @param $uid
	 */
	public function addScoreByMethod($method, $uid){
		if (!empty($uid) && !empty($method)) {
			$data['uid']    = intval($uid);
			$data['method'] = trim($method);

			$where['method'] = $data['method'];
			$where['status'] = 1;
			//获取该方法的积分配置
			$info            = D('ScoreDeploy')->where($where)->find();

			if(!empty($info)){
				$data['score']       = $info['score'];
				$data['desc']        = $info['desc'];
				$data['create_time'] = date('Y-m-d H:i:s', time());

                if (D('ScoreHistory')->add($data)) {
                    //如果积分是增加的，则等级表里的积分也相应的增加
                    if(strpos($data['score'], '-') === false){
					//获取等级的配置
                        $levelList = D('LevelHistory')->getDataList("uid={$uid}");
                        //用户初次添加积分时候会创建相关信息,否则只是更新积分
                        if(!$levelList){
                            $levelData['uid'] = $uid;
                            $levelData['score'] = $data['score'];
                            D('LevelHistory')->addData($levelData);
                        }else{
                            D('LevelHistory')->where("uid={$uid}")->setInc('score', $data['score']);
                        }
                        //根据积分的变化要更改相应的积分等级
                        D('LevelHistory')->editLevel($uid);
                    }
                    return true;
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}
	

	/**
	 * @desc 根据设定的规则修改积分
	 * @param string $method
	 * @param int $points
	 * @param int $uid
	 * @param string $desc
	 */
	public function setScoreByRule($method,$points,$uid,$desc){
		if(!$uid){
			return 0;		
		}
		if(!$method){
			return 2;
		}
		
		$res = $this->getScoreDeploy($method);
		if(!$points && !$res['points']){	
			return 1;	
		}
		
		$scores = $points ? $points : $res['points'];
		$desc = $desc ? $desc : $res['desc'];
		$create_time = date('Y-m-d H:i:s');
		$data = array(
			'uid' => $uid,
			'score' => $scores,
			'method' => $method,
			'desc' => $desc,
			'create_time' => $create_time
		);
		$map['uid'] = $uid;
		$in_id = D('ScoreHistory')->add($data);
		//将数据插入到score_history
		if($in_id){
			$levelList = D('LevelHistory')->getDataByMap($map);
			if($levelList){
				//同步积分
				D('LevelHistory')->where('uid = '.$uid)->setInc('score',$points);	
			}else{
				//创建一条记录数据
				$level['uid'] = $uid;
				$level['score'] = $points;
				D('LevelHistory')->addData($level);	
			}
			//同步更新等级
			D('LevelHistory')->editLevel($uid);
		}else{
			echo 3;
			exit;
		}
		return true;
	}
	/**
	* @desc 根据方法获取配置信息
	* @param string $method
	* @return array  
	*/
	public function getScoreDeploy($method){
		$method = strtolower($method);
		$list = D('ScoreDeploy')->getDataList();
		$scoreDeploy = array();
		foreach($list as $key => $val){
			$scoreDeploy[$val['method']]['points'] = $val['score'];
			$scoreDeploy[$val['method']]['desc'] = $val['desc'];
		}
		return $scoreDeploy[$method];
	}

}
