<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 需求管理
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Timt 2014-12-09 14:00
 */
class DemandController extends CommonController
{

    /**
     * 需求列表页
     */
	public function index() {
		import("Common.ORG.Page");
        
        // 设置搜索条件
        $title = isset($_GET['title']) ? filter_str($_GET['title']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $map = array();
        if (!empty($title)) $map['title'] = array('like', "{$title}%");
        if ($status!=-1) $map['status'] = $status;
        
		$count = D($this->className)->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
        
        
        if (!empty($data)){
            foreach ($data as &$v){
                $v['state'] = D($this->className)->getStatusText($v['status']);
                $v['username'] = D($this->className)->getUsername($v['uid']);
                $v['desc'] = msubstr($v['desc'],0,50);
            }
        }
		$this->assign('title', $title);
		$this->assign('status', $status);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}

    
    
    
    /**
     * 需求详情页
     */
    public function detail(){
        $id   = intval($_GET['id']);
		$data = D($this->className)->getDataById($id);
        if (empty($data)){
            $this->error('没有对应的数据',U('Demand/index'));
        }
        
        $sizeArr = explode(',', $data['size_id']);
        $sizeList = D('AdSizeConfig')->getDataList('','id,`size_name`');
        $sizeNameArr = array();
        foreach ($sizeList as $val){
            if (in_array($val['id'], $sizeArr)){
                $sizeNameArr[] = $val['size_name']; 
            }
        }
        
        $data['username'] = D($this->className)->getUsername($data['uid']);
        $data['state'] = D($this->className)->getStatusText($data['status']);
        
        $data['desc'] = stripslashes($data['desc']);
		$this->assign('sizeNameArr', $sizeNameArr);
		$this->assign('data', $data);
		$this->display();
    }
    
    //需求审核
    public function examine(){
        $res = D($this->className)->saveDataById($_POST);
        if ($res){
            $this->success('审核成功！');
        }else{
            $this->error(D($this->className)->getLastError());
        }
    }

    /**
     * 删除需求
     */
    public function delete(){
        $id = intval($_GET['id']);
        $res = D($this->className)->delById($id);
        if ($res){
            $this->success("删除成功", "/Admin/Demand/index");
        }else{
            $this->error("删除失败");
        }
    }

}