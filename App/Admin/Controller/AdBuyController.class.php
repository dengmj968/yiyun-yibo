<?php
namespace Admin\Controller;
use Common\ORG\Page;

/**
 * 广告赞助/购买记录
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Timt 2014-12-17 15:00
 */
class AdBuyController extends CommonController
{

    /**
     * 赞助列表页
     */
	public function index() {
		import("Common.ORG.Page");
        
        // 设置搜索条件
        $pid = isset($_GET['pid']) && $_GET['pid'] ? intval($_GET['pid']) : ''; //作品id
        $pro = isset($_GET['pro']) ? filter_str($_GET['pro']) : ''; //作品名
        $map = array();
        if ($pid){
            $map['ad_id'] = $pid;
        }else if (!empty($pro)){
            $pidArr = D('Ad')->where("title like '{$pro}%'")->getField('id', true);
            if ($pidArr){
                $map['ad_id'] = array('in', implode(',', $pidArr));
            }else{
                $map['id'] = 0;
            }
        }
        $map['type'] = 1;
        
        
		$count = D($this->className)->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
        //$statusText = array('无效' ,'成功');
        if (!empty($data)){
            foreach ($data as &$v){
                //$v['state'] = $statusText[$v['status']] ? $statusText[$v['status']] : '';
                $v['seller'] = D("Production")->getUsername($v['seller_id']);
                $v['buyer'] = D("Production")->getUsername($v['buyer_id']);
                $v['pro'] = D("Ad")->where('id='.$v['ad_id'])->getField('title');
                $v['start_date'] = date('y-m-d',$v['start_time']);
                $v['end_date'] = date('y-m-d',$v['end_time']);
            }
        }
		$this->assign('pid', $pid);
		$this->assign('pro', $pro);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}
    
    
    /**
     * 赞助详情页
     */
    public function detail(){
        $id   = intval($_GET['id']);
		$data = D('AdBuy')->getDataById($id);
        if (empty($data) || $data['type']!=1){
            $this->error('没有对应的数据',U('AdBuy/index'));
        }
        
        $proInfo = D('Ad')->getDataById($data['ad_id']);
        $data['seller'] = D('Production')->getUsername($data['seller_id']);
        $data['buyer'] = D('Production')->getUsername($data['buyer_id']);
        $data['start_date'] = date('Y-m-d H:i', $data['start_time']);
        $data['end_date'] = date('Y-m-d H:i', $data['end_time']);
        
        // 获取广告尺寸列表
        $adSizeList = D('AdSizeConfig')->getDataList('','','`width` ASC,`height` ASC');
        foreach ($adSizeList as $val){
            $sizeArr[$val['id']] = $val['size_name'];
        }
        $proInfo['size_name'] = $sizeArr[$proInfo['size_id']];
        
        
		$this->assign('proInfo', $proInfo);
		$this->assign('data', $data);
		$this->display();
    }
    
    
    /**
     * 购买列表页
     */
	public function buyList() {
		import("Common.ORG.Page");
        
        // 设置搜索条件
        $pro = isset($_GET['pro']) ? filter_str($_GET['pro']) : '';
        $map = array();
        if (!empty($pro)){
            $pidArr = D('Ad')->where("title like '{$pro}%'")->getField('id', true);
            if ($pidArr){
                $map['ad_id'] = array('in', implode(',', $pidArr));
            }else{
                $map['id'] = 0;
            }
        }
        
        $map['type'] = 2;
        
		$count = D($this->className)->getCount($map);
		$Page  = new Page($count, 10);
		$show  = $Page->show();
		$data  = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
        if (!empty($data)){
            foreach ($data as &$v){
                $v['seller'] = D("Production")->getUsername($v['seller_id']);
                $v['buyer'] = D("Production")->getUsername($v['buyer_id']);
                $v['pro'] = D("Ad")->where('id='.$v['ad_id'])->getField('title');
                $v['start_date'] = date('y-m-d',$v['start_time']);
                $v['end_date'] = date('y-m-d',$v['end_time']);
            }
        }
		$this->assign('pro', $pro);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('show', $show);
		$this->display();
	}
    
    

}