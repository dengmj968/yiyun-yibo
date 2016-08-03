<?php
namespace Admin\Controller;
/**
 * banner图
 * @author 邓明倦  <dengmingjuan@neteasy.cn>
 * @version 1.0    
 * create Timt 2015-04-14 17:41
 */
class BannerController extends CommonController
{
    
    private $place_list = array(
        'index_focus'  => '首页轮播图',
        'stylist'  => '广告齐创作',
        'production_add' => '上传我的作品',
        'demand_add'  => '发布我的需求',
        'demand_list'  => '全部需求列表',
        'demand_edit'  => '编辑需求页面'
    );
    
    private $size_list = array(
        'index_focus'  => '1920*446',
        'stylist'  => '1920*440',
        'production_add' => '1920*440',
        'demand_add'  => '1920*440',
        'demand_list'  => '1920*440',
        'demand_edit'  => '1920*440'
    );

    /**
     * 列表页
     */
	public function index() {
        
        // 设置搜索条件
        $place = isset($_GET['place']) ? filter_str($_GET['place']) : '';
        $status = isset($_GET['status']) ? intval($_GET['status']) : -1;
        $map = array();
        if (!empty($place)) $map['place'] = $place;
        if ($status!=-1) $map['status'] = $status;
        
		
		$data  = D($this->className)->where($map)->select();
        if (!empty($data)){
            foreach($data as &$val){
                $val['place_name'] = $this->place_list[$val['place']];
                
            }
        }
        $count = D($this->className)->where($map)->count();
		$this->assign('place', $place);
		$this->assign('status', $status);
		$this->assign('count', $count);
		$this->assign('data', $data);
		$this->assign('place_list', $this->place_list);
		$this->display();
	}

    /**
     * 添加
     */
	public function add() {
        $this->assign('place_list',  $this->place_list);
        $this->assign('size_list',  $this->size_list);
		$this->display();
	}
    
    /**
     * 添加案例处理页面
     */
	public function saveAdd() {
		$_POST['create_time'] = date('Y-m-d H:i:s');
        $_POST['update_time'] = time();
		$res = D($this->className)->addData($_POST);
		if ($res) {
			$this->success("添加成功", "/Admin/Banner/add");
		} else {
			$this->error('添加失败');
		}
	}
    
    /**
     * 修改表单页
     * @author 邓明倦  <dengmingjuan@neteasy.cn>
     */
	public function edit() {
		$id   = intval($_GET['id']);
		$data = D($this->className)->getDataById($id);
        if (empty($data)){
            $this->error('没有对应的数据',U('Banner/index'));
        }
		$this->assign('data', $data);
        $this->assign('place_list',  $this->place_list);
        $this->assign('size_list',  $this->size_list);
		$this->display();
	}
    
    /**
     * 修改案例处理页面
     */
	public function saveEdit() {
        $_POST['update_time'] = time();
		$res = D($this->className)->saveDataById($_POST);
		if ($res) {
			$this->success("修改成功", "/Admin/Banner/index");
		} else {
			$this->error(D($this->className)->getLastError());
		}
	}
    
    
    /**
     * 删除
     */
    public function delete(){
        $id = intval($_GET['id']);
        $res = D($this->className)->delById($id);
        if ($res){
            $this->success("删除成功", "/Admin/Banner/index");
        }else{
            $this->error("删除失败");
        }
    }

}