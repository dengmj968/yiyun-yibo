<?php
namespace Common\Model;
/**
 * 专题信息管理model
 * @author songweiqing
 */
class ProjectDetailModel extends BaseModel
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
                case 'project_id':
                    if(!$val){
                        $this->_error="专题id不能为空！";return false;
                    }
                    $datas['project_id'] = intval($val);
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }
        return $datas;
    }

    /**
     * @desc 通过字段的属性获取相应的html标签
     * @param int $id
     * @param  string $value
     * @return string
     */
    public function getHtmlByAttr($id, $value){
        $id = intval($id);
        $tagId = 'field_' . $id;    //标签Id
        $fieldInfo = D('ProjectField')->getDataById($id);
        $html = '';

        //通过`widget`字段的类型设置相应的html样式
        //如果字段是必填的，则增加类is_must
        switch($fieldInfo['widget']){
            case 'text':    //文本类型
                $html = '<div class="user_input "><input type="text" name="' . $tagId . '" value="'.$value.'"';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '/></div>';
                break;
            case 'select':  //下拉菜单
                $html = '<div class="user_select fl"><select name="' . $tagId . '" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '>';
                $html .= '<option name="0">--请选择--</option>';
                foreach($fieldInfo['options'] as $_k => $_v){
                    if($_v == $value){
                        $html .= '<option name="' . $_v . '" selected="selected">' . $_v . '</option>';
                    }else{
                        $html .= '<option name="' . $_v . '" >' . $_v . '</option>';
                    }
                }
                $html .= '</select></div>';
                break;
            case 'radio':   //单选按钮
                $html .= '<div class="user_radio fl">';
                foreach($fieldInfo['options'] as $_k => $_v){
                    if($_v == $value){
                        $html .= '<label><input type="radio" name="' . $tagId . '" value="' . $_v . '" checked="checked" ';
                    }else{
                        $html .= '<input type="radio" name="' . $tagId . '" value="' . $_v . '" ';
                    }

                    $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                    $html .= ' />' . $_v . '</label>';
                }
                $html .= '</div>';
                break;
            case 'checkbox':    //多选按钮
                $html .= '<div class="user_radio fl">';
                foreach($fieldInfo['options'] as $_k => $_v){
                    if(in_array($_v, $value)){
                        $html .= '<label><div class="user_radio"><input type="checkbox" name="' . $tagId . '[]" value="' . $_v . '" checked="checked" ';
                    }else{
                        $html .= '<div class="user_radio"><input type="checkbox" name="' . $tagId . '[]" value="' . $_v . '" ';
                    }
                    $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                    $html .= ' />' . $_v . '</label>';
                }
                $html .= '</div>';
                break;
            case 'file':    //文件上传
                $html .= '<div class="user_photo">';
                $html .= '<input type="file" name="" id="file_upload" ' . ' />';
                if(empty($value)){
                    $html .= '<img src="/Public/js/uploadify/default.png" width="300" height="200" id="thumbpic" align="absmiddle" alt="缩略图" />';
                }else{
                    $html .= '<img src="' . $value . '" width="300" height="200" id="thumbpic" align="absmiddle" alt="缩略图" />';
                }
                //$html .= '<img src="' . $value . '" width="300" height="200" id="thumbpic" align="absmiddle" alt="缩略图" />';
                $html .= '<input class="input" type="hidden" name="' . $tagId . '" value="' . $value . '" style="height: 300px" id="file" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '/></div>';
                break;
            case 'textarea':  //textarea
                $html .= '<div class="user_textarea "><textarea style="font-size: 12px" name="' . $tagId . '" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '>' . $value . '</textarea></div>';
                break;
            case 'time':    //日期类型，增加了日期控件
                $html .= '<div class="user_input "><input type="text" id="'.$tagId.'" name="' . $tagId . '" value="' . $value . '" time="time" onclick="return showCalendar(\''.$tagId.'\', \'y-mm-dd\');" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '/></div>';
                break;
            case 'web':     //url链接
                $html .= '<div class="user_input "><input type="text" name="' . $tagId . '" url="url" value="' . $value . '" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '/></div>';
                break;
            case 'contact':  //联系方式
                $html .= '<div class="user_input "><input type="text" name="' . $tagId . '" tel="tel" value="' . $value . '" ';
                $fieldInfo['is_must'] == 1 && $html .= ' class="is_must"';
                $html .= '/></div>';
                break;
            default:
                $html = '';
        }
        //如果字段必填，此类型的html增加.yanzheng类，在页面上会有红色'*'标识
        $fieldInfo['is_must'] == 1 && $html .= '<div class="user_des"><span class="yanzheng" style="padding-left: 30px; color: #ff0000">*</span></div>';
        return $html;
    }

    /**
     * @desc 通过专题id:project_id获取此专题的所有信息
     * @param int $projectId
     * @return array
     */
    public function getInfoByProjectId($projectId){
        $map['project_id'] = intval($projectId);
        $projectInfo = D("Project")->getDataById(intval($projectId));

        //如果专题状态不为1 或者 开始时间大于当前时间,或者 结束时间小于当前时间的情况下，将获取不到此专题信息return -1 
        if(($projectInfo['status'] != 1) || $projectInfo['start_date'] > time() || ( !empty($projectInfo['end_date']) && ( $projectInfo['end_date'] < time() ) ) ){
            return -1;
        }else{
			$map['status'] = 1;
            $projectInfo = $this->getDataList($map);
            foreach($projectInfo as $key => $val){
                $info = unserialize($val['info']);

                foreach($info as $key => $val){
                    $fieldInfo = D('ProjectField')->getDataById($key, 'is_individual, field_name, status, sort_no');

                    if($fieldInfo['status'] == 1){      //字段状态为1
                        if($fieldInfo['is_individual'] == 1){   //如果字段is_individual(是否单独显示)为1，组成一个数组
                            $temp[$fieldInfo['field_name']] = $val;
                        }else{                                  //其他的组成另外的数组
                            $tempVal[$fieldInfo['sort_no']] = $val;
                            $tempName[$fieldInfo['sort_no']] = $fieldInfo['field_name'];
                        }
                    }
                }
                //按sort_no排序，保证信息展示也是按sort_no排序
                ksort($tempVal);
                ksort($tempName);
                $tempInfo = array_combine($tempName, $tempVal);
                $newInfo = $temp;
                $newInfo['info'] = $tempInfo;

                $infoArr[] = $newInfo;
            }

            return $infoArr;
        }
    }


}
