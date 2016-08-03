<?php
namespace Common\Model;
/**
 * 首页所用的数据Model
 */
class IndexModel extends Model{

    /**
     * 得到展示量的合计
     */
    function GetAllShow(){
         return  M("ad_statistics")->sum("`show`");
    }

    /**
     * 得到点击的合计
     */
    function GetALlClick(){
            return M("ad_statistics")->sum("`click`");
    }

}

?>
