<?php 
namespace Home\Controller;
use Common\ORG\Page;
	/**
	 * @desc 用户的赞助记录
	 * @author 邓明倦
     * create Time 2014-12-19 15:00
	 */
	class AdBuyController extends CommonController{
        
		/**
		 * 赞助的记录列表
		 */
		 public function index(){
            import("Common.ORG.Page");
			
            // 设置搜索条件
            $title = isset($_GET['title']) ? filter_str($_GET['title']) : '';
            //$status = isset($_GET['status']) ? intval($_GET['status']) : -1;
            $map = array();
            if (!empty($title)){
                $pidArr = D('Ad')->where("title like '{$title}%'")->getField('id', true);
                if ($pidArr){
                    $map['ad_id'] = array('in', implode(',', $pidArr));
                }else{
                    $map['id'] = 0;
                }
            }
            $map['buyer_id'] = $_SESSION['userInfo']['id'];
            $map['status'] = array('in',array(1,2));

            $count = D($this->className)->getCount($map);
            $Page  = new Page($count, 10);
            $show  = $Page->show();
            $data  = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);
            if (!empty($data)){
                foreach ($data as &$v){
                    $v['seller'] = D("Production")->getUsername($v['seller_id']);
                    $v['title'] = D("Ad")->where('id='.$v['ad_id'])->getField('title');
                    $v['start_date'] = date('Y-m-d H:i',$v['start_time']);
                    $v['end_date'] = date('Y-m-d H:i',$v['end_time']);
                    $v['create_date'] = substr($v['create_time'],0,10);
                }
            }
            $this->assign('title', $title);
            $this->assign('count', $count);
            $this->assign('data', $data);
            $this->assign('page', $show);
            $this->display();
		 
		 }
		
		
	}