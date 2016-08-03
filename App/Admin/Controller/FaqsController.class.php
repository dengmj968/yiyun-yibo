<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 用户问答ACTION
 * @author 张强,liuqiuhui
 * @creat_time 2014-11-19
 */
class FaqsController extends CommonController{
    
    /**
     * @desc 问答列表
     */
    public function index(){
        // 获取搜索条件参数
        $question = filter_str($_REQUEST["question"]);
        $tid = intval($_REQUEST["tid"]);
        // 组合搜索条件
        if (!empty($question)) $where['question'] = array('like', "%{$question}%");
        if ($tid > 0) $where['tid'] = $tid;
        // 引入分页类
        import("Common.ORG.Page");
        // 获取数据总条数
        $count = D( $this->className )->getCount($where);
        // 设置每页显示条数
        $Page = new Page($count, 15);
        // 获取当前页显示数据
        $data = M( $this->className )->join('faqs_type ON faqs.tid = faqs_type.id')->field('faqs.*, faqs_type.name')->limit( $Page->firstRow.','.$Page->listRows )->where($where)->order('id')->select();
        // 组合搜索的url参数
        foreach($where as $key=>$val){
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }
        // 分页
        $show = $Page->show();
        // 问答类型
        $faqsType = $this->faqsType();
        $this->assign('show',$show);
        $this->assign('data',$data);
        $this->assign('faqsType', $faqsType);
        $this->assign('question', $question);
        $this->assign('tid', $tid);
        $this->display();
    }

    /**
     * @desc 获取faq分类
     * @return mixed
     */
    public function faqsType(){
        return D("FaqsType") ->getDataList( "","","sort_no","");
    }
    /**
     * @desc 修改问答页面
     */
    public function edit(){
        $id = intval($_GET['id']);
        $data = D( $this->className )->getDataById($id);
        // 获取问答分类
        $faqsType = $this->faqsType();
        $this->assign('faqsType',$faqsType);
        $this->assign('data',$data);
        $this->display();
    }

    /**
     * @desc 保存修改的问答
     */
    public function saveEdit(){
        $res = D( $this->className )->saveDataById($_POST);
        if($res){
            $this->success("修改问答成功","/Admin/Faqs/index");
        }else{
            $this->error( D( $this->className )->getLastError() );
        }
    }

    /**
     * @desc 添加问答页面
     */
    public function add(){
        $faqsType = $this->faqsType();
        $this->assign('faqsType',$faqsType);
        $this->display();
    }

    /**
     * @desc 保存添加问答
     */
    public function saveAdd(){
         $res = D( $this->className )->addData($_POST);
        if($res){
            $this->success("添加问答成功","/Admin/Faqs/index");
        }else{
            $this->error( D( $this->className )->getLastError() );
        }
    }
    /**
     * @desc 改变Ueditor 默认图片上传路径
     */
    public function checkPic(){
        import('ORG.Net.UploadFile');
        $upload = new UploadFile();// 实例化上传类
        $upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->autoSub =true ;
        $upload->subType ='date' ;
        $upload->dateFormat ='ym' ;
        $upload->savePath =  './Public/upload/faqs/';// 设置附件上传目录
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
}