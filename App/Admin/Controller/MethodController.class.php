<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * url资源管理
 * @author 邓明倦
 * create Time 2014-11-20 18:00
 */
class MethodController extends CommonController{

    //列表页
    public function index(){

        import("Common.ORG.Page");
        $map = array();

        if (isset($_GET['title']) && trim($_GET['title'])){
            $map['title'] = trim($_GET['title']); 
        }

        if (isset($_GET['group']) && trim($_GET['group'])){
            $map['group'] = trim($_GET['group']); 
        }

        if (isset($_GET['class']) && trim($_GET['class'])){
            $map['class'] = trim($_GET['class']); 
        }

        $pagesize = 10;

        $count = D($this->className)->getCount($map);
        $Page = new Page($count, $pagesize);

        $show = $Page->show();
        $data = D($this->className)->getDataList($map,'','`group`,`class`',$Page->firstRow.','.$Page->listRows);

        $p = !empty($_GET['p']) ? intval($_GET['p']) : 1;

        $k = 1;
        foreach ($data as &$v){
            $v['key'] = ($p - 1)*$pagesize + $k;
            $k++;
        }

        $this->assign('data',$data);
        $this->assign('show',$show);
        $this->display();
    }

		
    //编辑页
    public function edit(){
        $id = intval($_GET['id']);
        $info = D($this->className)->getDataById($id);
        $this->assign('info',$info);
        $this->display();
    }
	
    /**
     * 保存修改
     */
    public function saveEdit(){
        $result = D($this->className)->saveDataById($_POST);
        if ($result){
            $this->success('修改成功', U('Admin/Method/index'));
        }else{
            $this->error(D($this->className)->getError());
        }
    }

}