<?php
namespace Common\Model;
class LevelHistoryModel extends BaseModel
{
    public function parseData( array $data ){
        $data['update_time'] = time();
        return $data;
    }

    /**
     * @param $uid
     * @return bool
     * 根据用户id更新积分等级id
     */
    public function editLevel($uid)
	{
        $info = $this->getDataByMap("uid={$uid}");
        $map['score_lower'] = array('elt', $info['score']);
        $map['score_upper'] = array('egt', $info['score']);
        $map['_logic'] = 'AND';

        $levelInfo = D('LevelDeploy')->where($map)->find();

        if($info['level_id'] != $levelInfo['id']){
            $data['id'] = $info['id'];
            $data['level_id'] = $levelInfo['id'];

            if($this->saveDataById($data)){
                return true;
            }else{
                return false;
            }
        }

        return true;
	}
	
	/**
	 * @desc根据用户id获取积分和等级
	 * @param int $uid 
	 * @return array $scoreInfo
	 */
	public function getScoreLevel($uid){
		$map['uid'] = intval($uid);
		$scoreInfo = $this->getDataByMap($map);
		if($scoreInfo){
			$levelInfo = D('LevelDeploy')->getDataById($scoreInfo['level_id']);
			$scoreInfo['name'] = $levelInfo['name'];
			return $scoreInfo;
		}else{
			$scoreInfo['score'] = 0;
			$scoreInfo['name'] = 1;
			return $scoreInfo;
		}
	}
}
