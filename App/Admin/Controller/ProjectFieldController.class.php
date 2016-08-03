<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 专题 ACTION
 * @author lixiaoli
 *
 */
class ProjectFieldController extends CommonController{
    public function setClassName(){
        $this->className = 'ProjectField';
    }

    /**
     * @desc 专题字段列表
     */
    public function index(){
        // 设置搜索条件
        if($_REQUEST['name']){
            $map['name'] = $_REQUEST['name'];
            $project_id = D('Project')->where($map)->getField('project_id');
        }
        $id         = intval($_REQUEST["id"]);
        $field_name = filter_str($_REQUEST["field_name"]);
        $widget     = filter_str($_REQUEST["widget"]);

        if ($id > 0) $map['id'] = $id;
        if ($project_id > 0) $map['project_id'] = $project_id;
        if (!empty($field_name)) $map['field_name'] = array('like', "%{$field_name}%");
        if (!empty($widget)) $map['widget'] = $widget;

        $_GET['project_id'] && $map['project_id'] = intval($_GET['project_id']);

        import("Common.ORG.Page");
        $count = D($this->className)->getCount();
        $Page = new Page($count, 15);
        $show = $Page->show();
        $projectFieldList =D($this->className)->getDataList($map, '' , 'sort_no', $Page->firstRow . ',' . $Page->listRows);
        foreach($map as $key=>$val) {
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }

        $this->assign('show', $show);
        $this->assign('project_field_list', $projectFieldList);
        $this->assign('project_id', intval($_GET['project_id']));
        $this->assign('map', $map);
        $this->assign('name', $_REQUEST['name']);
        $this->display();
    }

    /**
     * @desc 专题字段添加
     */
    public function add(){
        $projectId = intval($_GET['project_id']);
        $widgetOptions = D($this->className)->getWidgetOptions();
        $fileType = D($this->className)->getFileType();
        $projectData = D("Project")->getDataList();
        $this->assign('widget_options', $widgetOptions);
        $this->assign('file_type', $fileType);
        $this->assign('project_id', $projectId);
        $this->assign('project_data', $projectData);
        $this->display();
    }

    /**
     * @desc 专题字段添加保存
     */
    public function saveAdd(){
        $project_id = intval($_POST['project_id']);
        $id = D($this->className)->addData($_POST);
        $res = D($this->className)->where("id = {$id}")->setField('sort_no', $id);

        if($id && $res){
            $this->success('添加成功！','Admin/ProjectField/index?project_id=' . $project_id);
        }else{
            $this->error( D($this->className)->getLastError() );
        }

    }

    /**
     *@desc修改字段信息
     */
    public function edit(){
        $id = intval($_GET['id']);
        $project_id = intval($_GET['project_id']);
        $data = D($this->className)->getDataById($id);
        $widgetOptions = D($this->className)->getWidgetOptions();
        $fileType = D($this->className)->getFileType();
        $projectData = D("Project")->getDataList();

        $this->assign('widget_options', $widgetOptions);
        $this->assign('file_type', $fileType);
        $this->assign('data',$data);
        $this->assign('project_data',$projectData);
        $this->assign('project_id',$project_id);
        $this->display();
    }

    /**
     * @desc 保存修改的字段信息
     */
    public function saveEdit(){
        $project_id = intval($_POST['project_id']);
        !isset($_POST['is_must']) && $_POST['is_must'] = 0;
        !isset($_POST['is_search']) && $_POST['is_search'] = 0;

        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success('修改成功！','Admin/ProjectField/index?project_id=' . $project_id);
        }else{
            $this->error( D($this->className)->getLastError() );
        }

    }
	/**
	* @desc 上移操作
	*
	*/
    public function moveUp(){
        $id         = $_GET['id'];
        $sortNo     = $_GET['sortNo'];
        $prevID     = $_GET['prevID'];
        $prevSortNo = $_GET['prevSortNo'];

        $resOne = M( $this->className )->where("id={$id}")->setField('sort_no', $prevSortNo);
        $resTwo = M( $this->className )->where("id={$prevID}")->setField('sort_no', $sortNo);

        if($resOne && $resTwo){
            $res['errno'] = '1';
        }else{
            $res['errno'] = '-1';
            $res['error'] = '上移失败';
        }
        exit(json_encode($res));
    }
	/**
	 *	@desc 下移操作
	 */
    public function moveDown(){
        $id = $_GET['id'];
        $sortNo = $_GET['sortNo'];
        $nextID = $_GET['nextID'];
        $nextSortNo = $_GET['nextSortNo'];

        $resOne = M( $this->className )->where("id={$id}")->setField('sort_no', $nextSortNo);
        $resTwo = M( $this->className )->where("id={$nextID}")->setField('sort_no', $sortNo);

        if($resOne && $resTwo){
            $res['errno'] = '1';
        }else{
            $res['errno'] = '-1';
            $res['error'] = '下移失败';
        }

        exit(json_encode($res));
    }
	/**
	 * @desc 修改字段的值
	 *
	 */
    public function changeValAjax(){
        $map[$_GET['field']] = $_GET['val'];
        $map['id'] = $_GET['id'];
        $res = D($this->className)->saveDataById($map);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

}