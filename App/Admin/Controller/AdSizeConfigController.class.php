<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 广告位尺寸配置
 * @athor zhangliang,liuqiuhui
 * @create_date 2013-07-10
 */
class AdSizeConfigController extends CommonController
{
	/**
     * 默认上传Path
     */
    private static $SizeImgPath = "./Public/upload/adSizeConfig/";

    /**
     * 列表页
     */
    function index()
    {
        import("Common.ORG.Page");
        $count = D( $this->className  )->getCount();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = D( $this->className  )->getDataList('', '*', 'size_name', $Page->firstRow . ',' . $Page->listRows);
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }

    /**
     * 新增页
     */
    function add()
    {
        $this->display();
    }

    /**
     * 新增保存页
     */
    function saveAdd()
    {
        if (D( $this->className )->checkIsHaving("add", $_POST["size_name"], 0)) $this->error("尺寸名称重复!");

        if (D( $this->className )->addData($_POST)) {
			$this->success("添加成功！","/Admin/AdSizeConfig/index");
        } else {
			$this->error(D( $this->className )->getLastError());
        }
    }


    /**
     * 修改页
     */
    function edit()
    {
        $id = intval($_GET["id"]);
        $data = D( $this->className )->getDataById($id);
        $this->assign("data", $data);
		$this->display();
    }

    /**
     * 修改保存页
     */
    function saveEdit()
    {
		$_POST["size_name"] = filter_str($_POST["size_name"]);
		$_POST["width"]     = intval($_POST["width"]);
		$_POST["height"]    = intval($_POST["height"]);

        if (D( $this->className )->checkIsHaving("edit", $_POST["size_name"], $_POST['id'])) $this->error("尺寸名称重复!");

        if (D( $this->className )->saveDataById($_POST)) {
			$this->success("修改成功！","/Admin/AdSizeConfig/index");
        } else {
            $this->error("修改失败!");
        }
    }

}

