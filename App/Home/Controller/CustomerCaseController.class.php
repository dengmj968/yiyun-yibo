<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Common\ORG\Page;
/**
 * 推广案例展示
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Time 2014-10-15 17:44
 */
class CustomerCaseController extends BaseController{
    /**
     * 案例展示列表页
     */
    public function index(){
        import("Common.ORG.Page");
        $map = array('status'=>1); //获取状态为显示的
        $order = array('sort'=>'desc');
        $count = D($this->className)->getCount($map);
        $Page  = new Page($count, 10);
        $show  = $Page->show();
        $field = 'id,name,uid,pic,start_time,end_time,show_num,click_num,abstract';
        $data  = D($this->className)->getDataList($map, $field, $order, $Page->firstRow.','.$Page->listRows);

        if ($data){
            foreach ($data as &$val){
                $val['abstract'] = strip_tags($val['abstract']);
                
                $val['url'] = U('Home/CustomerCase/detail',array('id'=>$val['id']));//详情页链接
                $val['username'] = NULL;
                if ($val['uid']){
                    $val['username'] = D($this->className)->getUsername($val['uid']);
                    $val['userurl'] = $this->deploy['CENTER_SERVER'].'/Home/HomePage/index/uid/'.$val['uid'];
                }
                if ($val['username']){
                    $val['abstract'] = msubstr($val['abstract'],0,56);
                }else{
                    $val['abstract'] = msubstr($val['abstract'],0,120);
                }
                $val['show_num'] = number_format($val['show_num'], 0, '.', ',');
                $val['click_num'] = number_format($val['click_num'], 0, '.', ',');
            }
        }
        $this->assign('count', $count);
        $this->assign('data', $data);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * 推广案例详情页
     */
    public function detail(){
        $id   = intval($_GET['id']);
        $data = D($this->className)->getDataById($id);
        
        //没有记录跳转到列表页
        if (empty($data) || $data['status']==0){
            $this->redirect(U('Home/CustomerCase','',''));
        }
        $data['username'] = NULL;
        $data['userpic'] = $this->deploy['CENTER_SERVER'].'/Public/image/default.jpg';
        if ($data['uid']){
            $info = D('Memcache')->getUserInfoById($data['uid']);
            if (isset($info['extend']['pic']) && $info['extend']['pic']){
                $data['userpic'] = $this->deploy['CENTER_SERVER'].$info['extend']['pic'];
            }
            $data['username'] = D($this->className)->getUsername($data['uid']);
            $data['userurl'] = $this->deploy['CENTER_SERVER'].'/Home/HomePage/index/uid/'.$data['uid'];
        }
        $data['desc'] = stripslashes($data['desc']);
        $this->assign('data', $data);
        $this->display();
    }

}
