<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * @desc 用户积分ACTION
 * @author lixiaoli,liuqiuhui
 * @create_time 2014-11-19
 */
class ScoreHistoryController extends CommonController{

    /**
     * @desc 用户积分
     */
    public function index(){
        // 获取搜索参数
        $id          = intval($_REQUEST["id"]);
        $uid         = intval($_REQUEST["uid"]);
        $user_name   = filter_str($_REQUEST["user_name"]);
        $method      = filter_str($_REQUEST["method"]);
        $desc        = filter_str($_REQUEST["desc"]);
        $create_time = $_POST["create_time"];
        // 搜索条件组合
        if ($id > 0) $map['id'] = $id;
        if ($uid > 0) $map['uid'] = $uid;
        if (!empty($user_name)) $map['user_name'] = $user_name;
        if (!empty($method)) $map['method'] = $method;
        if (!empty($create_time)) $map['create_time'] = $create_time;
        if (!empty($desc)) $map['desc'] = array('like', "%{$desc}%");

        /* 分页 */
        // 引入分页类
        import("Common.ORG.Page");
        // 获取数据总条数
        $count = D( $this->className )->getCount($map);
        // 设置每页显示条数
        $Page = new Page($count, 20);
        // 当前页数据列表
        $data = D($this->className)->getDataList($map, '', '', $Page->firstRow.','.$Page->listRows);

		//多用户调用用户接口
		$uidArr = '';
		foreach ($data as $val){
			$uidArr[] = $val['uid'];
		}
		$uidStr = join(',', array_unique($uidArr));
        // 接口传递参数
		$param['id']  = $uidStr;
		$param['key'] = $this->deploy['USER_KEY'];
        // 获取用户信息数据接口
		$avatar = curl_post($this->deploy['CENTER_SERVER'].'/Api/DataApi/getUserInfoById', $param);
		$arr = json_decode($avatar, true);
        // 循环接口返回的用户信息
		foreach($arr as $val){
		    // 判断用户信息是否完整
			if(($username = $val['extend']['user_name']) || ($username = $val['extend']['true_name']) || ($username = $val['email'])){
				$userList[$val['id']] = $username;
			}
		}

		// 拼装用户信息数组
		foreach ($data as $key => $val) {
			$data[$key]['user_name'] = $userList[$val['uid']];
		}
        // url搜索参数组合
		foreach ($map as $key => $val) {
			$Page->parameter .= "$key=".urlencode($val).'&';
		}
        //分页样式
		$show = $Page->show();
        $this->assign('show',$show);
        $this->assign('data',$data);
        $this->assign('map', $map);
        $this->assign('desc', $desc);
        $this->display();
    }

}