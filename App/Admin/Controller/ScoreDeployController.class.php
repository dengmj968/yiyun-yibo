<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * @desc       积分配置ACTION
 * @author     liuqiuhui
 * @createTime 2014-11-25
 */
class ScoreDeployController extends CommonController{

    /**
     * @desc 积分配置
     */
    public function index(){
        import("Common.ORG.Page");
        $count = D( $this->className )->getCount();
        $Page = new Page($count, 20);
        $show = $Page->show();
        $data = D($this->className)->getScoreDeployList('', '', '', $Page->firstRow . ',' . $Page->listRows);
        $methodList = D("Method")->getDataList();
        $this->assign('method',$methodList);
        $this->assign('p',$_GET['p']);
        $this->assign('show',$show);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * @desc 修改积分配置页面
     */
    public function edit(){
        $id = intval($_GET['id']);
        $data = D( $this->className )->getDataById($id);
        $this->assign('p',$_GET['p']);
        $this->assign('data',$data);
        $methodList = D("Method")->getDataList();
        $this->assign('method',$methodList);
        $this->display();
    }

    /**
     * @desc 保存修改的积分配置
     */
    public function saveEdit(){
        $res = D( $this->className )->saveScoreDeploy($_POST);
        if($res){
            $this->success("修改积分配置成功","/Admin/ScoreDeploy/index/p/".$_POST['p']);
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc 添加积分配置页面
     */
    public function add(){
        $methodList = D("Method")->getDataList();
        $this->assign('method',$methodList);
        $this->display();
    }

    /**
     * @desc 保存添加积分配置
     */
    public function saveAdd(){
        $res = D( $this->className )->addScoreDeploy($_POST);
        if($res){
            $this->success("添加积分配置成功","/Admin/ScoreDeploy/index");
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc Ajax_修改积分配置的状态  启用/关闭
     */
    public function editStatus(){
        if(isset($_GET['id'])){
            $data['id'] = $_GET['id'];
            $_GET['status'] == 1 ? $data['status'] = 2 : $data['status'] = 1;

            $res = D($this->className)->saveScoreDeploy($data);
            if($res){
                echo 1;
            }else{
                echo 0;
            }
        }
    }

}