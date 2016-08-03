<?php
namespace Admin\Controller;
/**
 * 广告类型
 * @athor 张良,liuqiuhui
 * @create_date 2014-11-19
 * 目前只支持图片单一类型
 */
class AdTypeConfigController extends CommonController
{
    /**
     * 列表页
     */
    function index(){
       $list = D( $this->className )->getDataList('', '*', 'id');
       $this->assign("list",$list);
       $this->display();
    }
}
