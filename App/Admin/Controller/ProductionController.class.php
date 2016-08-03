<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 设计师作品管理
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Timt 2014-12-09 10:00
 */
class ProductionController extends CommonController
{
    /**
     * 获取作品的状态描述
     * @param array $info 作品信息
     * @return string 状态描述
     */
    private function getStatusText($info){
        $stateArr = array(
            2 => array(1 => '审核中', 2 => '已赞助', 3 => '待赞助', 4 => '驳回'),
            3 => array(1 => '审核中', 2 => '待采纳', 3 => '待采纳', 4 => '驳回'),
            4 => array(1 => '审核中', 2 => '待售', 3 => '待售', 4 => '驳回'),
            5 => array(1 => '审核中', 2 => '已售出', 3 => '已售出', 4 => '驳回')
            
        );
        return $stateArr[$info['type']][$info['status']];
    }

    /**
     * 作品列表页
     */
	public function index() {
		import("Common.ORG.Page");
        
        // 设置搜索条件
        $id = isset($_GET['id']) && $_GET['id'] ? intval($_GET['id']) : '';
        $title = isset($_GET['title']) ? filter_str($_GET['title']) : '';
        $type = isset($_GET['type']) ? intval($_GET['type']) : -1;
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $map = array();
        if ($id) $map['id'] = $id;
        if (!empty($title)) $map['title'] = array('like', "{$title}%");
       // if ($status!=-1) $map['status'] = $status;
        if ( !in_array($type, array(2,3,4,5))){
            $map['type'] = array('gt',1);
        }else{
            $map['type'] = $type;
        }
        
        if ($status != -1){
            if ($status == 5){
                $map['type'] = 2;
                $map['status'] = 3;
            }else if ($status == 6){
                $map['type'] = 2;
                $map['status'] = 2;
            }else if ($status == 7){
                $map['type'] = 3;
                $map['status'] = array('in','2,3');
            }else if ($status == 8){
                $map['type'] = 4;
                $map['status'] = array('in','2,3');
            }else if ($status == 9){
                $map['type'] = 5;
                $map['status'] = array('in','2,3');
            }else if ($status == 1 || $status == 4){
                $map['status'] = $status;
            }
        }
        
		$count = D('Ad')->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D('Ad')->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
        
        // 获取广告尺寸列表
        $adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
        foreach ($adSizeList as $val){
            $sizeArr[$val['id']] = $val['size_name'];
        }
        //$statusText = array(1=>'待审核', 2=>'使用中', 3=>'已停用', 4=>'审核未通过');
        $typeText = array(2=>'赞助作品', 3=>'需求作品', 4=>'待售作品', 5=>'已售作品');
        if (!empty($data)){
            foreach ($data as &$v){
                //$v['state'] = $statusText[$v['status']];
                $v['state'] = $this->getStatusText($v);
                $v['username'] = D($this->className)->getUsername($v['user_id']);
                $v['size_name'] = $sizeArr[$v['size_id']];
                $v['create_time'] = date('Y-m-d', $v['create_time']);
                $v['desc'] = msubstr($v['desc'],0,50);
                $v['type_name'] = $typeText[$v['type']]; 
                $v['detailText'] = ($v['status'])==1 ? '审核' : '详情';
            }
        }
		$this->assign('id', $id);
		$this->assign('title', $title);
		$this->assign('type', $type);
		$this->assign('status', $status);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}

    
    
    
    /**
     * 作品详情页
     */
    public function detail(){
        $id   = intval($_GET['id']);
		$data = D('Ad')->getDataById($id);
        if (empty($data)){
            $this->error('没有对应的数据',U('Production/index'));
        }
        //$statusText = array(1=>'待审核', 2=>'使用中', 3=>'已停用', 4=>'审核未通过');
        $data['username'] = D('Production')->getUsername($data['user_id']);
        //$data['state'] = $statusText[$data['status']];
        $data['state'] = $this->getStatusText($data);
        
        
        // 获取广告尺寸列表
        $adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
        foreach ($adSizeList as $val){
            $sizeArr[$val['id']] = $val['size_name'];
        }
        $data['size_name'] = $sizeArr[$data['size_id']];
        
        $data['desc'] = stripslashes($data['desc']);
		$this->assign('data', $data);
		$this->display();
    }
    
    //作品审核
    public function examine(){
        $id = intval($_POST['id']);
        $proInfo = D('Ad')->getDataById($id);
        if (empty($proInfo)){
            $this->error('数据不存在！');
            exit;
        }
        $res = D('Ad')->where('id='.$_POST['id'])->save($_POST);
        if ($res){
            if($_POST['status'] == 3 && $proInfo['type'] != 4){
                $data['uid']    = $proInfo['user_id'];
                $data['points'] = D('AdSizeConfig')->where("id = $proInfo[size_id]")->getField('price');
                $data['desc']   = '作品审核通过奖励'.$data['points'].'积分';
                //$url = 'http://'.$_SERVER['HTTP_HOST'].'/Admin/Production/index/';
                $scoreKey = getScoreHistoryKey($data['uid'],strtolower(GROUP_NAME.'_'.MODULE_NAME . '_' . ACTION_NAME),$id);
                $this->userAddScore($scoreKey,$data);
                $this->userSendMessage(array(),$proInfo['user_id']);
            }
            $this->success('审核成功！','Admin/Production/index');
        }else{
            $this->error('审核失败！');
        }
    }

        /**
     * 删除作品
     */
    public function delete(){
        $id = intval($_GET['id']);
        $res = D('Ad')->delById($id);
        if ($res){
            $this->success("删除成功", "/Admin/Production/index");
        }else{
            $this->error("删除失败");
        }
    }

}