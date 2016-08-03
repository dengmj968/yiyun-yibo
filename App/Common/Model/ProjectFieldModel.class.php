<?php
namespace Common\Model;
class ProjectFieldModel extends BaseModel
{
	
    protected $_widget = array(
        'text'     => '文本框',
        'select'   => '下拉选项',
        'checkbox' => '多选',
        'radio'    => '单选',
        'file'     => '上传',
        'textarea' => '文章框',
        'time'     => '时间',
        'web'      => '网址',
        'contact'  => '联系方式'
    );
    protected $_widgetNoIsSearch = array( 'select', 'checkbox', 'radio', 'file', 'contact', 'web');
	
	/**
	 *	@desc 返回form标签类型
	 *	@return array $data
	 */
	
    public function getWidgetOptions()
    {
        return $this->_widget;
    }

	/**
	 *	@desc  unknow
	 */
    public function getFileType(){
        return $this->_fileType;
    }
	
	/**
	 *	@desc根据文本标签的键获取值
	 *	@return string $string 
	 */
    public function getWidgetValByKey($key){
        $widgetArr = $this->_widget;
        return $widgetArr[$key];
    }
	
	/**
	 *	@desc 返回表单标签类型
	 *	@return array $array
	 */
    public function getWidgetNoIsSearch(){
        $widgetArr = $this->_widgetNoIsSearch;
        return $widgetArr;
    }

	/**
	 *	@desc 过滤数据
	 *   @param array $data
	 *	@return array $data	
	 */
    public function parseData( array $data ){
        $fields = $this->getFields();
        foreach($data as $key=>$val){
            if(!in_array($key,$fields)){
                continue;
            }
            switch($key){
                case 'field_name':
                    if(!$val){
                        $this->_error="字段名称不能为空！";
                        return false;
                    }
                    $datas['field_name'] = htmlspecialchars($val);
                    break;
                case 'widget':
                    if(!$val){
                        $this->_error="请选择字段类型！";
                        return false;
                    }
                    $datas['widget'] = htmlspecialchars($val);
                    break;
                case 'options':
                    if(is_array($val)){
                        $datas['options'] = array_filter($val);
                        if(empty($datas['options'])){
                            unset($datas['options']);
                        }
                    }
                    if(is_string($val)){
                        $datas['options'] = htmlspecialchars($val);
                    }
                    break;
                default :
                    $datas[$key] = $val;
                    break;
            }
        }

        ACTION_NAME == 'saveAdd' && $datas['status'] = 1;
        $datas['create_time'] = date("Y-m-d H:i:s");
        $datas['update_time'] = time();
        return $datas;
    }

    /**
	 * @desc 通过id获取字段值
     * @param $id
     * @param $field
     * @return mixed
     * 
     */
    public function getFieldById($id, $field){
        $id = intval($id);
        $field = $this->where("id = {$id}")->getField($field);
        return $field;
    }

}
