<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 用户意见ACTION
 * @author 小强,liuqiuhui
 * @create_time 2014-11-19
 */
class IdeaController extends CommonController{

    /**
     *意见建议首页
     */
    public function index(){
		import("Common.ORG.Page");
		$count = D( $this->className )->getCount();
		$Page = new Page($count, 15);
		$show = $Page->show();
		$ideaList = D( $this->className )->getDataList("","","",$Page->firstRow . ',' . $Page->listRows);
		$this->assign('show',$show);
		$this->assign('ideaList',$ideaList);
		$this->display();
	}
    /**
     *修改意见建议页面
     */
    public function editIdea(){
        $id = intval($_GET['id']);
        $idea = D( $this->className )->getDataById($id);
        $this->assign('idea',$idea);
        $this->display();
	}
    /**
     *保存修改意见建议
     */
    public function saveEditIdea(){
        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success("修改意见建议成功","/Admin/Idea/index");
        }else{
            $this->error( D( $this->className )->getLastError() );
        }
        $this->display();
    }

    /**
     *删除意见建议
     */
    public function delIdea(){
		$id = $_GET['id'];
        foreach($id as $k=>$v){
            if($v !== "all"){
                D( $this->className )->delById($v);
            }
        }
		echo 1;
	}
}