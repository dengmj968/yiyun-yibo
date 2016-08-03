<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 活动Action
 * @author lixiaoli
 *
 */
class ActivityController extends CommonController{
    public function setClassName(){
        $this->className = 'Activity';
    }
    /**
     * 活动列表
     */
    public function index(){
        // 设置搜索条件
        $title = filter_str($_REQUEST["title"]);
        $initiator = filter_str($_REQUEST["initiator"]);

        if (!empty($title)) $map['title'] = array('like', "%{$title}%");
        if (!empty($initiator)) $map['initiator'] = $initiator;

        import("Common.ORG.Page");
        $count = D( $this->className )->getCount($map);
        $Page = new Page($count, 15);
        $data = D($this->className)->getDataList($map, '', '', $Page->firstRow . ',' . $Page->listRows);

        foreach($map as $key=>$val){
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }
        $show = $Page->show();

        $this->assign('show', $show);
        $this->assign('data', $data);
        $this->assign('title', $title);
        $this->assign('initiator', $initiator);
        $this->display();
    }

    /**
     *修改活动信息
     */
    public function edit(){
        $id = intval($_GET['id']);
        $data = D( $this->className )->getDataById($id);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     *报保存修改的活动信息
     */
    public function saveEdit(){
        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success("修改成功","/Admin/Activity/index");
        }else{
            $this->error( D( $this->className )->getLastError() );
        }
    }

    /**
     *添加活动
     */
    public function add(){
        $this->display();
    }

    /**
     *保存添加活动
     */
    public function saveAdd(){
        $res = D( $this->className )->addData($_POST);
        if($res){
            $this->success("添加成功","/Admin/Activity/index");
        }else{
            $this->error( D( $this->className )->getLastError() );
        }
    }

    /**
     *改变Ueditor 默认图片上传路径
     */
    public function checkPic(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->autoSub =true ;
        $upload->subType ='date' ;
        $upload->dateFormat ='ym' ;
        $upload->savePath =  './Public/upload/activity/';// 设置附件上传目录
        if($upload->upload()){
            $info =  $upload->getUploadFileInfo();
            echo json_encode(array(
                'url'=>$info[0]['savename'],
                'title'=>htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
                'original'=>$info[0]['name'],
                'state'=>'SUCCESS'
            ));
        }else{
            echo json_encode(array(
                'state'=>$upload->getErrorMsg()
            ));
        }
    }

    /**
     * Ajax更改活动是否在首页展示状态
     * 如果已经
     */
    public function changeIsShowAjax(){
        $map['is_show'] = $_GET['is_show'];
        $map['id'] = $_GET['id'];

        if($map['is_show'] == 1){
            $is_show = D($this->className)->getDataByMap("is_show=1");

            if($is_show){
                echo -1;
                die;
            }
        }

        $res = D($this->className)->saveDataById($map);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}