<?php
namespace Common\Model;
/**
 * 专题管理model
 * @author songweiqing
 */
class ProjectModel extends BaseModel
{
    /**
     * @desc 字段数据过滤
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
                case 'name':
                    if(!$val){
                        $this->_error="名称不能为空！";return false;
                    }
                    $datas['name'] = htmlspecialchars($val);
                    break;
                case 'template_id':
                    if($val == 0){
                        $this->_error="请选择专题模板！";return false;
                    }
                    $datas['template_id'] = intval($val);
                    break;
                case 'desc':
                    if(!$val){
                        $this->_error="描述不能为空！";return false;
                    }
                    if(mb_strlen($val) > 300){
                        $this->_error="描述内容不能超过300字符或100个汉字！";return false;
                    }
                    $datas['desc'] = htmlspecialchars($val);
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }
		$datas['update_time'] = time();
		if($datas['start_date']){
			$datas['start_date'] = strtotime($datas['start_date']);
		}
		if($datas['end_date']){
			$datas['end_date'] = strtotime($datas['end_date']);
		}
       // $datas['status'] != '0' && $datas['status'] = 1;
	   if(isset($data['status'])){
			$datas['status'] = $data['status'];
	   }else{
			$datas['status'] = 0;
	   }

        return $datas;
    }

    /**
	 *  @desc 通过project_id获取专题名称
	 *	@param int $project_id
	 *	@return string $string
     */
    public function getValByProjectId($project_id){
        $map['id'] = intval($project_id);
        $val = $this->where($map)->getField('name');
        return $val;
    }

    /**
     * @desc 获取专题列表 默认条件是当前时间在专题开放时间范围内或专题时间为空;状态为1
	 * @param $fields null
	 * @param $order null
	 * @param $limit null
	 * @return array $data
     */
    public function getProjectList($fields=null,$order=null,$limit=null){
        $map['status'] = 1;
        $projectList = $this->getDataList($map, $fields, $order, $limit);

        foreach($projectList as $key => $val){
            if( $val['start_date'] > time() || ( !empty($val['end_date']) && ( $val['end_date'] < time() ) ) ){
                unset($projectList[$key]);
            }
        }

        return $projectList;
    }

    /**
	 * @desc 定义404专题模板
     * @return array $data
     */
    public function get404TemplateList(){
        $templateList = array(
            '1' => array(    //下标即为id标识,保存于project表中的template_id字段中
                'name' => '模板1',     //模板名称
                'pic' => '__PUBLIC__/images/404_template_1.jpg'     //模板对应的样式截图
            ),
            '2' => array(
                'name' => '模板2',
                'pic' => '__PUBLIC__/images/404_template_2.jpg'
            ),
            '3' => array(
                'name' => '模板3',
                'pic' => '__PUBLIC__/images/404_template_3.jpg'
            ),
			'5' => array(
                'name' => '通缉犯',
                'pic' => '__PUBLIC__/images/404_template_3.jpg'
            ),
/* 			'4' => array(
                'name' => '幸福列车',
                'pic' => '__PUBLIC__/images/404_template_4.jpg'
            ),
		    '5' => array(
                'name' => '通缉犯',
                'pic' => '__PUBLIC__/images/404_template_5.jpg'
            ),
			'6' => array(
                'name' => '地震救灾',
                'pic' => '__PUBLIC__/images/404_template_6.jpg'
            ),
			'7' => array(
                'name' => '宜农贷',
                'pic' => '__PUBLIC__/images/404_template_7.jpg'
            ),
			'8' => array(
                'name' => '公益广告大赛',
                'pic' => '__PUBLIC__/images/404_template_8.jpg'
            ),
			 */
			'14' => array(
                'name' => '马航专题',
                'pic' => '__PUBLIC__/images/mh/pic_mh370.jpg'
            ),			
			'11' => array(
                'name' => '原自定义模板',
                'pic' => '__PUBLIC__/images/404_pic2.jpg'
            ),
			'17' => array(
                'name' => '新自定义模板',
                'pic' => '__PUBLIC__/images/404_template_17.jpg'
            ),
			'16' => array(
                'name' => '设计师主题',
                'pic' => '__PUBLIC__/images/pic_mh370.png'
            )           
        );

        return $templateList;
    } 
}
