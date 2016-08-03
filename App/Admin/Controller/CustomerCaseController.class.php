<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 客户案例管理
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Timt 2014-10-14 17:41
 */
class CustomerCaseController extends CommonController
{

    /**
     * 案例列表页
     */
	public function index() {
		import("Common.ORG.Page");
        
        // 设置搜索条件
        $name = isset($_GET['name']) ? filter_str($_GET['name']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $map = array();
        if (!empty($name)) $map['name'] = array('like', "%{$name}%");
        if ($status!=-1) $map['status'] = $status;
        
		$count = D($this->className)->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
        if (!empty($data)){
            foreach ($data as &$v){
                $v['username'] = NULL;
                if ($v['uid']){
                    $v['username'] = D($this->className)->getUsername($v['uid']);
                    $v['userurl'] = $this->deploy['CENTER_SERVER'].'/Home/HomePage/index/uid/'.$v['uid'];
                }
            }
        }
		$this->assign('name', $name);
		$this->assign('status', $status);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}

    /**
     * 添加案例表单页
     */
	public function add() {
		$this->display();
	}
    
    /**
     * 添加案例处理页面
     */
	public function saveAdd() {
		$_POST['create_time'] = date('Y-m-d H:i:s');
        $_POST['update_time'] = time();
		$res                  = D($this->className)->addData($_POST);
		if ($res) {
			$this->success("添加成功", "/Admin/CustomerCase/add");
		} else {
			$this->error('添加失败');
		}
	}
    
    /**
     * 修改案例表单页
     * @author 邓明倦  <dengmingjuan@neteasy.cn>
     */
	public function edit() {
		$id   = intval($_GET['id']);
		$data = D($this->className)->getDataById($id);
        if (empty($data)){
            $this->error('没有对应的数据',U('CustomerCase/index'));
        }
        $data['desc'] = stripslashes($data['desc']);
		$this->assign('data', $data);
		$this->display();
	}
    
    /**
     * 修改案例处理页面
     */
	public function saveEdit() {
        $_POST['update_time'] = time();
		$res = D($this->className)->saveDataById($_POST);
        
		if ($res) {
			$this->success("修改成功", "/Admin/CustomerCase/index");
		} else {
			$this->error(D($this->className)->getLastError());
		}
	}
    
    /**
     * 案例详情页
     * @author 邓明倦  <dengmingjuan@neteasy.cn>
     */
    public function detail(){
        $id   = intval($_GET['id']);
		$data = D($this->className)->getDataById($id);
        if (empty($data)){
            $this->error('没有对应的数据',U('CustomerCase/index'));
        }
        $data['username'] = NULL;
        if ($data['uid']){
            $data['username'] = D($this->className)->getUsername($data['uid']);
            $data['userurl'] = $this->deploy['CENTER_SERVER'].'/Home/HomePage/index/uid/'.$data['uid'];
        }
        $data['desc'] = stripslashes($data['desc']);
        $data['status_desc'] = $data['status'] ? '显示' : '不显示';
		$this->assign('data', $data);
		$this->display();
    }
    
    /**
     * 删除案例
     */
    public function delete(){
        $id = intval($_GET['id']);
        $res = D($this->className)->delById($id);
        if ($res){
            $this->success("删除成功", "/Admin/CustomerCase/index");
        }else{
            $this->error("删除失败");
        }
    }

}