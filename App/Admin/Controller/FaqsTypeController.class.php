<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 问答分类 ACTION
 * @author lixiaoli,liuqiuhui
 * @create_time 2014-11-19
 **/
class FaqsTypeController extends CommonController{
    
    /**
     * @desc 问答分类
     **/
    public function index(){
        // 引入分页类
        import("Common.ORG.Page");
        // 获取数据总条数
        $count = D( $this->className )->getCount();
        // 设置每页显示数据条数
        $Page = new Page($count, 15);
        // 分页样式
        $show = $Page->show();
        // 当前页数据列表
        $data =D( $this->className )->getDataList("","","`sort_no`",$Page->firstRow . ',' . $Page->listRows);
        $this->assign('show',$show);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * @desc 修改问答分类
     **/
    public function edit(){
        $id = intval($_GET['id']);
        $data = D( $this->className )->getDataById($id);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * @desc 保存修改的问答分类
     **/
    public function saveEdit(){
        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success("修改问答分类成功","/Admin/FaqsType/index");
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc 添加问答页面分类
     **/
    public function add(){
        $this->display();
    }

    /**
     * @desc 保存添加问答分类,sort_no默认与id一致
     **/
    public function saveAdd(){
        $id = D( $this->className )->addData($_POST);
        if($id){
            M( $this->className )->where("id={$id}")->setField('sort_no', $id);
            $this->success("添加问答分类成功","/Admin/FaqsType/index");
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }
    /**
     * @desc ajax_数据上移 
     * @暂时没用
     **/ 
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
     * @desc ajax_数据下移 
     * @暂时没用
     **/
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
}