<?php
namespace Home\Controller;
use Common\ORG\Page;
/**
 * @desc 用户发布的需求
 * @author 邓明倦
 * create Time 2014-12-19 14:00
 */
class DemandController extends CommonController {

    private function getUserScore($uid) {
        $data['uid'] = $uid;
        $score = json_decode(curl_post($this->deploy['CENTER_SERVER'] . "/Api/ScoreApi/getScoreByUid", $data), true);
        return $score;
    }

    /**
     * 发布的需求列表
     */
    public function index() {
        import("Common.ORG.Page");

        $map = array();
        $map['uid'] = $_SESSION['userInfo']['id'];

        // 设置搜索条件
        $title = isset($_GET['title']) ? filter_str($_GET['title']) : '';
        if (!empty($title))
            $map['title'] = array('like', "{$title}%");

        $count = D("Demand")->getCount($map);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $data = D("Demand")->getDataList($map, '', 'id desc', $Page->firstRow . ',' . $Page->listRows);

        $sizeList = D('AdSizeConfig')->getDataList('', 'id,`size_name`');
        $sizeArr = array();
        foreach ($sizeList as $val) {
            $sizeArr[$val['id']] = $val['size_name'];
        }

        foreach ($data as &$v) {
            $v['url'] =  '/Home/Production/inputDemandProduction/id/' . $v['id'];
            $v['size'] = explode(',', $v['size_id']);
            $v['state'] = D('Demand')->getStatusText($v['status']);
            $v['time'] = substr($v['create_time'], 0, 10);
            //$replyNum = D('AdLinkDemand')->where('demand_id=' . $v['id'])->count(); //需求作品数
            $v['allowEdit'] = $v['status'] ? '0' : '1'; //已完成需求不能修改
        }
        $this->assign('count', $count);
        $this->assign('title', $title);
        $this->assign('sizeArr', $sizeArr);
        $this->assign('page', $show);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 广告中心修改需求页面
     */
    public function edit() {
        $id = intval($_GET['id']); //需求id
        $info = D('Demand')->getDataById($id);
        if (empty($info) || $info['uid'] != $_SESSION['userInfo']['id']) {
            $this->error('数据不存在！');
            exit;
        }

        $replyNum = D('AdLinkDemand')->where('demand_id=' . $id)->count(); //需求作品数
        if ($replyNum) {
            $this->error('需求已经有作品上传了，不能修改！',  '/Demand/');
            exit;
        }

        $sizeList = D('AdSizeConfig')->getDataList('', '', "`width` asc");
        $sizeArr = array();
        $sizeidArr = explode(',', $info['size_id']);
        $price = 0;
        $size_list = array();
        foreach ($sizeList as &$val) {
            if (in_array($val['id'], $sizeidArr)) {
                $size_list[] = $val;
            }
        }
        //print_r($sizeList);exit;
        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $userScore = $row['score'];
        $this->assign('usercenter', $this->deploy['CENTER_SERVER']);
        $this->assign('sizeArr', $size_list);
        $this->assign('price', $price);
        $this->assign('userScore', $userScore);
        $this->assign('info', $info);
        $this->display();
    }
    
    /**
     * 广告中心修改需求页面
     */
    /*public function edit() {
        $id = intval($_GET['id']); //需求id
        $info = D('Demand')->getDataById($id);
        if (empty($info) || $info['uid'] != $_SESSION['userInfo']['id']) {
            $this->error('数据不存在！');
            exit;
        }

        $replyNum = D('AdLinkDemand')->where('demand_id=' . $id)->count(); //需求作品数
        if ($replyNum) {
            $this->error('需求已经有作品上传了，不能修改！',  '/Demand/');
            exit;
        }

        $sizeList = D('AdSizeConfig')->getDataList('', '', "`width` asc");
        $sizeArr = array();
        $sizeidArr = explode(',', $info['size_id']);
        $price = 0;

        foreach ($sizeList as &$val) {
            if (in_array($val['id'], $sizeidArr)) {
                $val['class'] = 'choose';
                $price += $val['price'];
            }
        }
        //print_r($sizeList);exit;
        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $userScore = $row['score'];
        $this->assign('usercenter', $this->deploy['CENTER_SERVER']);
        $this->assign('sizeArr', $sizeList);
        $this->assign('price', $price);
        $this->assign('userScore', $userScore);
        $this->assign('info', $info);
        $this->display();
    }
     * 
     */

    /**
     * 个人中心保存需求修改
     */
    function saveEdit() {
        $id = intval($_POST['id']); //需求id
        $info = D('Demand')->getDataById($id);

        if (empty($info) || $info['uid'] != $_SESSION['userInfo']['id']) {
            $this->error('修改失败，数据不存在！');
            exit;
        }
        
        $replyNum = D('AdLinkDemand')->where('demand_id=' . $id)->count(); //需求作品数
        if ($replyNum) {
            $this->error('需求已经有作品上传了，不能修改！',  '/Demand/');
            exit;
        }
        $size = isset($_POST['size_id']) ? trim($_POST['size_id'], ',') : '';
        $sizeArr = explode(',', $size);
        $sizeList = D('AdSizeConfig')->getDataList('', 'id,`price`,`score`');
        $price = 0; //所选尺寸广告总价值
        $size_ids = '';
        foreach ($sizeList as $val) {
            if (in_array($val['id'], $sizeArr)) {
                $price += $val['price'];
                $size_ids .= $val['id'] . ',';
            }
        }
        $size_ids = trim($size_ids, ',');
        $_POST['size_id'] = $size_ids;
        if ($size_ids == '') {
            $this->error('请至少选择一种尺寸');
            exit;
        }
        //print_r($_POST);
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        if ($score < $price) {
            $this->error('悬赏积分不能少于所选尺寸总积分');
            exit;
        }

        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $userScore = $row['score'];

        //用户积分不够
        $_score = $userScore + $info['score'];
        if ($_score < $score) {
            $this->error('您拥有' . $userScore . '积分，不够悬赏！');
            exit;
        }

        $res = D("Demand")->saveDataById($_POST);
        if (!$res) {
            $this->error('需求修改失败！' . D("Demand")->getLastError());
            exit;
        }
        $score2 = $info['score'] - $score;
        if ($score2 > 0) {
            $desc = "需求悬赏积分从{$info['score']}改为{$score},退还{$score2}积分";
            $scoreData[] = array('uid' => $_SESSION['userInfo']['id'], 'score' => $score2, 'from' => '2', 'desc' => $desc);
        } else if ($score2 < 0) {
            $score3 = abs($score2);
            $desc = "需求悬赏积分从{$info['score']}改为{$score},扣除{$score3}积分";
            $scoreData[] = array('uid' => $_SESSION['userInfo']['id'], 'score' => $score2, 'from' => '2', 'desc' => $desc);
        }

        if ($score2 != 0) {
            $res = json_decode(curl_post($this->deploy['CENTER_SERVER'] . "/Api/ScoreApi/setAdScore", $scoreData), true);
            if (empty($res) || $res['status'] != 4) { //积分数据更新失败
                $msg = '需求修改失败！';
                $errorMsg = array(2 => '无法更新积分。', 3 => '积分余额不足。', 5 => '积分更新失败！', 6 => '积分更新失败。');
                if (isset($res['status']) && isset($errorMsg[$res['status']])) {
                    $msg = '需求修改失败!' . $errorMsg[$res['status']];
                }
                D("Demand")->saveDataById($info); //还原需求
                $this->error($msg);
                exit;
            }
        }
        $_SESSION['userInfo']['score']['score'] = $userScore + $score2;
        //$this->success('需求修改成功！',  '/Home/Demand/index');
        redirect( '/Home/Demand/index');
    }

    /**
     * @desc删除需求
     */
    public function del() {
        $id = intval($_POST['id']);
        $info = D('Demand')->getDataById($id);
        
        if (empty($info) || $info['status']!=1){ //需求未完成不能删除
            $this->ajaxReturn(0);
        }

        $res = D('Demand')->delById($id);

        if ($res) {
            $this->ajaxReturn(1);
        }
        $this->ajaxReturn(0);
    }

    /**
     * 发布需求
     */
    public function add() {
        $groupName = $_SESSION['userInfo']['group']['group_name'];

        //作品列表列表
        $map['type'] = array('in', '2,4');
        $map['status'] = array('in', array(2, 3));
        $proList = D('Ad')->getDataList($map, 'id,title,likes,type,status', 'create_time desc', '10');

        foreach ($proList as &$val) {
            $val['tit'] = msubstr($val['title'], 0, 15);
            $val['url'] =  "/Stylist/detail/id/" . $val['id'];
        }

        $sizeList = D('AdSizeConfig')->getDataList('', '', "`width` asc");

        //获取用户积分
        if (!isset($_SESSION['userInfo']['score']['score'])) {
            $row = $this->getUserScore($_SESSION['userInfo']['id']);
            $score = $row['score'];
            $_SESSION['userInfo']['score']['score'] = $score;
        } else {
            $score = $_SESSION['userInfo']['score']['score'];
        }
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('demand_add');
        $this->assign('banner', $banner);

        $this->assign('usercenter', $this->deploy['CENTER_SERVER']);
        $this->assign('sizeList', $sizeList);
        $this->assign('score', $score);
        $this->assign('proList', $proList);
        $this->assign('isGroup', $groupName);
        $this->display();
    }

    /**
     * 发布需求提交页
     */
    public function saveAdd() {
        if (empty($_SESSION['userInfo']['group'])) {
            $this->error('您还未认证,不能发布需求！',  '/');
            exit;
        }

        $size = isset($_POST['size_id']) ? trim($_POST['size_id'], ',') : '';
        $sizeArr = explode(',', $size);
        $sizeList = D('AdSizeConfig')->getDataList('', 'id,`price`,`score`');
        $price = 0; //所选尺寸广告总价值
        $size_ids = '';
        foreach ($sizeList as $val) {
            if (in_array($val['id'], $sizeArr)) {
                $price += $val['price'];
                $size_ids .= $val['id'] . ',';
            }
        }
        $size_ids = trim($size_ids, ',');
        $_POST['size_id'] = $size_ids;
        if ($size_ids == '') {
            $this->error('请至少选择一种尺寸');
            exit;
        }
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        if ($score < $price) {
            $this->error('悬赏积分不能少于所选尺寸总积分');
            exit;
        }

        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $userScore = $row['score'];

        //用户积分不够
        if ($userScore < $score) {
            $this->error('您拥有' . $userScore . '积分，不够悬赏！');
            exit;
        }

        $_POST['uid'] = $_SESSION['userInfo']['id'];
        $_POST['create_time'] = date('Y-m-d H:i:s');
        $id = D("Demand")->addData($_POST);
        if ($id) {
            $scoreData = array();

            //发布需求扣除悬赏积分
            $scoreData[] = array('uid' => $_SESSION['userInfo']['id'], 'score' => '-' . $score, 'from' => '2', 'desc' => "发布需求扣除{$score}悬赏积分");
            $res = json_decode(curl_post($this->deploy['CENTER_SERVER'] . "/Api/ScoreApi/setAdScore", $scoreData), true);
            if (empty($res) || $res['status'] != 4) { //积分数据更新失败
                D("Demand")->delById($id);
                $msg = '需求发布失败！';

                $errorMsg = array(2 => '无法支付积分。', 3 => '积分余额不足。', 5 => '积分支付失败。', 6 => '积分更新失败。');
                if (isset($res['status']) && isset($errorMsg[$res['status']])) {
                    $msg = '需求发布失败!' . $errorMsg[$res['status']];
                }
                $this->error($msg);
                exit;
            }
            $_SESSION['userInfo']['score']['score'] = $userScore - $score;
            //$this->success('需求发布成功！',  '/Stylist/demandList/');
            redirect( '/Stylist/demandList/');
        } else {
            $this->error(D("Demand")->getLastError());
        }
    }

    /**
     * 修改需求（不在个人中心修改）
     */
    public function modify() {
        if (empty($_SESSION['userInfo']['group'])) {
            $this->error('您还未认证,不能发布需求！',  '/');
            exit;
        }

        //判断是否是修改需求
        $demand_id = isset($_GET['id']) ? intval($_GET['id']) : 0; //需求id 
        $info = D('Demand')->getDataById($demand_id);

        if (empty($info) || ($info['uid'] != $_SESSION['userInfo']['id'])) {
            $this->error('需求不存在！',  '/');
            exit;
        }

        $replyNum = D('AdLinkDemand')->where('demand_id=' . $demand_id)->count(); //需求作品数
        if ($replyNum) {
            $this->error('需求已经有作品上传了，不能修改！',  '/Stylist/inputDemandProduction/id/' . $demand_id);
            exit;
        }

        $sizeList = D('AdSizeConfig')->getDataList('', '', "`width` asc");

        $sizeArr = explode(',', $info['size_id']);
        $price = 0;  //广告总价值
        $size_list = array();
        foreach ($sizeList as &$val) {
            if (in_array($val['id'], $sizeArr)) {
                $size_list[] = $val;
                $price += $val['price'];
            }
        }

        //作品列表
        $map['type'] = array('in', '2,4');
        $map['status'] = array('in', array(2, 3));
        $proList = D('Ad')->getDataList($map, 'id,title,likes,type,status', 'create_time desc', '10');

        foreach ($proList as &$val) {
            $val['tit'] = msubstr($val['title'], 0, 15);
            $val['url'] =  "/Stylist/detail/id/" . $val['id'];
        }

        //获取用户积分
        if (!isset($_SESSION['userInfo']['score']['score'])) {
            $row = $this->getUserScore($_SESSION['userInfo']['id']);
            $score = $row['score'];
            $_SESSION['userInfo']['score']['score'] = $score;
        } else {
            $score = $_SESSION['userInfo']['score']['score'];
        }
        
        //banner图
        $banner = D("Banner")->getInfoByPlace('demand_edit');
        $this->assign('banner', $banner);

        $this->assign('usercenter', $this->deploy['CENTER_SERVER']);
        $this->assign('size_list', $size_list);
        $this->assign('score', $score);
        $this->assign('price', $price);
        $this->assign('info', $info);
        $this->assign('proList', $proList);
        $this->display();
    }

    /**
     * 需求修改提交页面（不在个人中心修改）
     */
    public function saveModify() {
        $demand_id = isset($_POST['id']) ? intval($_POST['id']) : ''; //需求id
        $info = D('Demand')->getDataById($demand_id);
        if (empty($info) || ($info['uid'] != $_SESSION['userInfo']['id'])) {
            $this->error('修改失败，数据不存在！');
            exit;
        }
        
        if ($info['status']==1){
            $this->error('需求已完成，不能修改！',  '/Stylist/inputDemandProduction/id/' . $demand_id);
            exit;
        }
        

//        $replyNum = D('AdLinkDemand')->where('demand_id=' . $demand_id)->count(); //需求作品数
//        if ($replyNum) {
//            $this->error('需求已经有作品上传了，不能修改！',  '/Stylist/inputDemandProduction/id/' . $demand_id);
//            exit;
//        }

        /*
        $size = isset($_POST['size_id']) ? trim($_POST['size_id'], ',') : '';
        $sizeArr = explode(',', $size);
        $sizeList = D('AdSizeConfig')->getDataList('', 'id,`price`,`score`');
        $price = 0; //所选尺寸广告总价值
        $size_ids = '';
        foreach ($sizeList as $val) {
            if (in_array($val['id'], $sizeArr)) {
                $price += $val['price'];
                $size_ids .= $val['id'] . ',';
            }
        }
        $size_ids = trim($size_ids, ',');
        $_POST['size_id'] = $size_ids;
        if ($size_ids == '') {
            $this->error('请至少选择一种尺寸');
            exit;
        }
        $score = isset($_POST['score']) ? intval($_POST['score']) : 0;
        if ($score < $price) {
            $this->error('悬赏积分不能少于所选尺寸总积分');
            exit;
        }

        $row = $this->getUserScore($_SESSION['userInfo']['id']);
        $userScore = $row['score'];

        //用户积分不够
        $_score = $userScore + $info['score'];
        if ($_score < $score) {
            $this->error('您拥有' . $userScore . '积分，不够悬赏！');
            exit;
        }
         * 
         */
        $data = array();
        $data['desc'] = $info['desc'].'<br/>'.htmlspecialchars($_POST['append_desc']);
        $data['result'] = $_POST['result'];
        //print_r($data);exit;
        //$res = D("Demand")->saveDataById($data);
        $res = D("Demand")->where('id='.$demand_id)->save($data);
        //echo D("Demand")->getLastSql();exit;
        if (!$res) {
            $this->error('需求修改失败！' . D("Demand")->getLastError());
            exit;
        }

        /*$score2 = $info['score'] - $score;
        if ($score2 > 0) {
            $desc = "需求悬赏积分从{$info['score']}改为{$score},退还{$score2}积分";
            $scoreData[] = array('uid' => $_SESSION['userInfo']['id'], 'score' => $score2, 'from' => '2', 'desc' => $desc);
        } else if ($score2 < 0) {
            $score3 = abs($score2);
            $desc = "需求悬赏积分从{$info['score']}改为{$score},补扣{$score3}积分";
            $scoreData[] = array('uid' => $_SESSION['userInfo']['id'], 'score' => $score2, 'from' => '2', 'desc' => $desc);
        }

        if ($score2 != 0) {
            $res = json_decode(curl_post($this->deploy['CENTER_SERVER'] . "/Api/ScoreApi/setAdScore", $scoreData), true);
            if (empty($res) || $res['status'] != 4) { //积分数据更新失败
                $msg = '需求修改失败！';
                $errorMsg = array(2 => '无法更新积分。', 3 => '积分余额不足。', 5 => '积分更新失败！', 6 => '积分更新失败。');
                if (isset($res['status']) && isset($errorMsg[$res['status']])) {
                    $msg = '需求修改失败!' . $errorMsg[$res['status']];
                }
                D("Demand")->saveDataById($info); //还原需求
                $this->error($msg);
                exit;
            }
        }
        $_SESSION['userInfo']['score']['score'] = $userScore + $score2;
         * 
         */
        if ($_GET['type'] == '1'){ //个人中心修改
            //$this->success('需求修改成功！',  '/Home/Demand/index');
            redirect( '/Home/Demand/index/');
        }else{
            //$this->success('需求修改成功！',  '/Stylist/demandList/');
            redirect( '/Stylist/demandList/');
        }
        
    }

}
