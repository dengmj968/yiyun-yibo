<?php
namespace Admin\Controller;
/**
 * @desc 配置前端网站页面的keywords description Action
 * @athor 张良
 * @create_date 07-30
 */
class WebTitleController extends CommonController
{
	public function setClassName(){
		$this->className = 'WebTitle';
	}
    /**
     * @desc 列表页
     */
    public function index() {
        $this->display();
    }

    /**
     * @desc 修改
     */
    public function edit(){
		$id = intval($_GET['id']);
		$list = D( $this->className )->getDataById($id);
		$this->assign('list', $list);
        $this->display();
    }

    /**
     * @desc 修改保存
     */
    public function editSave(){
		$data['id']          = intval($_POST["id"]);
		$data["title"]       = filter_str($_POST["title"]);
		//$data["method"]      = filter_str($_POST["method"]);
		$data["keywords"]    = filter_str($_POST["keywords"]);
		$data["description"] = filter_str($_POST["description"]);

		$res = D( $this->className )->saveDataById($data);

		if ($res) {
			$this->success("修改成功", "/Admin/WebTitle/index");
		} else {
			$this->error(D($this->className)->getLastError());
		}
    }

}