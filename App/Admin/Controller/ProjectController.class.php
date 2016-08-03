<?php
namespace Admin\Controller;
/**
 * 专题 ACTION
 * @author lixiaoli
 *
 */
class ProjectController extends CommonController{
    public function setClassName(){
        $this->className = 'Project';
    }

    /**
     * @desc 专题列表
     */
    public function index(){
        $projectList = D($this->className)->getDataList();
        $this->assign('porject_list', $projectList);
        $this->display();
    }

    /**
     * @desc 添加专题
     */
    public function add(){
        $templateList = D($this->className)->get404TemplateList();
        $this->assign('template_list', $templateList);
        $this->display();
    }

    /**
     * @desc 保存专题
     */
    public function saveAdd(){
		if(strpos($_POST['url'],"http://") === false){
			$_POST['url'] = "http://".$_POST['url'];
		}
		if($_POST['branch_url'] && strpos($_POST['branch_url'],"http://") === false){
			$_POST['branch_url'] = "http://".$_POST['branch_url'];
		}

        $id = D( $this->className )->addData($_POST);
        //添加项目时默认添加url字段
        $data[0]['project_id'] = $id;
        $data[0]['field_name'] = 'url';
        $data[0]['widget'] = 'web';
        $data[0]['is_must'] = 1;
        $data[0]['status'] = 1;
        $data[0]['options'] = 'system';
        $data[0]['is_individual'] = 1;
        $data[0]['create_time'] = date('Y-m-d H:i:s');
        $data[0]['update_time'] = time();

        //添加项目时默认添加img字段
        $data[1]['project_id'] = $id;
        $data[1]['field_name'] = 'img';
        $data[1]['widget'] = 'file';
        $data[1]['is_must'] = 1;
        $data[1]['status'] = 1;
        $data[1]['options'] = 'system';
        $data[1]['is_individual'] = 1;
        $data[1]['create_time'] = date('Y-m-d H:i:s');
        $data[1]['update_time'] = time();

        $urlId = D("ProjectField")->addAll($data);
        $logoId = $urlId + 1;

        $urlRes = D("ProjectField")->where("id = {$urlId}")->setField('sort_no', $urlId);
        $logoRes = D("ProjectField")->where("id = {$logoId}")->setField('sort_no', $logoId);

        if($id && $urlId && $urlRes && $logoRes){
            $this->success("添加成功","/Admin/Project/index");
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc 修改专题
     */
    public function edit(){
        $id = intval($_GET['id']);
        $data = D( $this->className )->getDataById($id);
        $templateList = D($this->className)->get404TemplateList();
        $this->assign('template_list', $templateList);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * @desc 保存修改的专题
     */
    public function saveEdit(){
        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success("修改成功","/Admin/Project/index");
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc 更改专题状态 Ajax
     */
    public function changeStatusAjax(){
        $data['id'] = $_GET['id'];
        $data['status'] = $_GET['status'];
        $res = D($this->className)->saveDataById($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * @desc 通过id删除专题，同时删除此专题相关的字段及信息
     */

    public function delAllByIdAjax(){
        $id = intval($_GET['id']);
        $map['project_id'] = $id;
        $res = D($this->className)->delById($id);   //删除对应id的专题
        $field_res = D("ProjectField")->where($map)->delete(); //删除字段表中此专题对应的字段
        $detail_res = D("ProjectDetail")->where($map)->delete(); //删除详情表中此专题对应的内容

        if($res && $field_res && $detail_res){
            echo 1;
        }else{
            echo 0;
        }
    }
	/**
	 *	@desc强推专题
	 */
	public function pushProject(){
		$id = intval($_POST['id']);
		$is_push = intval($_POST['is_push']);
		if($is_push == 1){
			$res = D('Project')->where('is_push = 1 and status = 1')->find();
			if($res){
				$this->ajaxReturn($res,"【{$res['name']}】专题已经被主推，请先取消已主推专题",0);
			}
		}
		
		$projectInfo = D('Project')->field('status')->find($id);
		$data['status'] = $projectInfo['status'];
		$data['is_push'] = $is_push;	
		$data['id'] = $id;
		$result = D('Project')->save($data);
		if($result){
			$this->ajaxReturn($result,'操作成功',1);
		}else{
			$this->ajaxReturn($sql,"操作失败",0);
		}
	
	}
}