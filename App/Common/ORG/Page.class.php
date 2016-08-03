<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Page.class.php 2712 2012-02-06 10:12:49Z liu21st $
namespace Common\ORG;
class Page {

    // 分页栏每页显示的页数
    public $rollPage = 7;
    // 页数跳转时要带的参数
    public $parameter;
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow;
    // 分页总页面数
    protected $totalPages;
    // 总行数
    protected $totalRows;
    // 当前页数
    protected $nowPage;
    // 分页的栏的总页数
    protected $coolPages;
    // 分页显示定制
    protected $config = array('header' => '条记录', 'prev' => '', 'next' => '', 'first' => '', 'last' => '', 'theme' => '%end% <div class="fr m_page_stylist_m clearfix"><i class="m_page_stylist_i fl"> %totalRow% %header% </i>%first% %upPage% %prePage% %linkPage% %nextPage% %downPage% %lastPage%</div>');
    // 默认分页变量名
    protected $varPage;

    /**
      +----------------------------------------------------------
     * 架构函数
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
      +----------------------------------------------------------
     */
    public function __construct($totalRows, $listRows = '', $parameter = '') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->varPage = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!empty($listRows)) {
            $this->listRows = intval($listRows);
        }
        $this->totalPages = ceil($this->totalRows / $this->listRows);     //总页数
        $this->coolPages = ceil($this->totalPages / $this->rollPage);
        $this->nowPage = !empty($_GET[$this->varPage]) ? intval($_GET[$this->varPage]) : 1;
        if (!empty($this->totalPages) && $this->nowPage > $this->totalPages){
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows * ($this->nowPage - 1);
    }

    public function setConfig($name, $value) {
        if (isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
      +----------------------------------------------------------
     * 分页显示输出
      +----------------------------------------------------------
     * @access public
      +----------------------------------------------------------
     */
    public function show() {
        if (0 == $this->totalRows)
            return '';
        $p = $this->varPage;
        //$nowCoolPage = ceil($this->nowPage / $this->rollPage);
        $url = $_SERVER['REQUEST_URI'] . (strpos($_SERVER['REQUEST_URI'], '?') ? '' : "?") . $this->parameter;
        $parse = parse_url($url);
        if (isset($parse['query'])) {
            parse_str($parse['query'], $params);
            unset($params[$p]);
            $url = $parse['path'] . '?' . http_build_query($params);
        }
        //上下翻页字符串
        $upRow = $this->nowPage - 1;
        $downRow = $this->nowPage + 1;
        if ($upRow > 0) {
            $upPage = "<a class='prev' href='" . $url . "&" . $p . "=$upRow'>" . $this->config['prev'] . "</a>";
        } else {
            $upPage = "";
        }

        if ($downRow <= $this->totalPages) {
            $downPage = "<a class='next' href='" . $url . "&" . $p . "=$downRow'>" . $this->config['next'] . "</a>";
        } else {
            $downPage = "";
        }
        // << < > >>
        if ($this->nowPage <= 1) {
            $theFirst = "";
            $prePage = "";
        } else {
            $preRow = $this->nowPage - $this->rollPage;
            $prePage = "<a class='prev'  href='" . $url . "&" . $p . "=$preRow' >上" . $this->rollPage . "页</a>";
            $theFirst = "<a class='first' href='" . $url . "&" . $p . "=1' >" . $this->config['first'] . "</a>";
        }
        if ($this->nowPage == $this->totalPages) {
            $nextPage = "";
			$lastPage = "";
            //$theEnd = "";
        } else {
            $nextRow = $this->nowPage + $this->rollPage;
            $theEndRow = $this->totalPages;
            $nextPage = "<a class='next' href='" . $url . "&" . $p . "=$nextRow' >下" . $this->rollPage . "页</a>";
			$lastPage = "<a class='last' href='" . $url . "&" . $p . "=$theEndRow' >" . $this->config['last'] . "</a>";
        }
		
		$theEnd = "<form class='fr' action='".U(__ACTION__,$_GET)."'><input type='text' style='height:22px;' name='p' placeholder='页数' class='m_page_stylist_input' /><input type='submit' style='height:32px;' class='m_page_stylist_go' value='GO' /></form>";
		
        /**
         * 修改的分页效果 ThinkPHP 3.0 正式版 作者：tomato QQ:11965994
         */
        $linkPage = "";
        $mid = floor($this->rollPage / 2); //返回整数 
        if ($this->nowPage <= $mid) {
            $start = 1;
            if ($this->totalPages < $this->rollPage) {
                $end = $this->totalPages;
            } else {
                $end = $this->rollPage;
            }
        } else {
            if (($this->nowPage + $mid) > $this->totalPages) {
                //$start = $this->nowPage - $mid;
                $start = $this->totalPages - $this->rollPage +1;
                if ($start < 1){
                    $start = 1;
                }
                $end = $this->totalPages;
                
            } else {
                //$start = $this->nowPage - $mid;
                $start = $this->nowPage + $mid - $this->rollPage +1;
                $end = $this->nowPage + $mid;
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            if ($i != $this->nowPage) {
                $linkPage .= "<a href='" . $url . "&" . $p . "=$i'>&nbsp;" . $i . "&nbsp;</a>";
            } else {
                $linkPage .= "<span class='choose'>" . $i . "</span>";
            }
        }

        if($this->totalPages > 9){
			$jumpPage = "<form class='fr' action='".$_SERVER['REQUEST_URI']."'><input type='text' name='p' placeholder='页数' style='height:22px;' class='m_page_stylist_input' /><input class='m_page_stylist_go' style='height:32px;' type='submit' value='GO'/></form>";
		}else{
			$jumpPage = '';
		}
        $pageStr = str_replace(
                array('%header%', '%nowPage%', '%totalRow%', '%totalPage%', '%upPage%', '%first%', '%prePage%', '%linkPage%', '%downPage%', '%nextPage%', '%lastPage%', '%end%'), 
				array($this->config['header'], $this->nowPage, $this->totalRows, $this->totalPages, $upPage, $theFirst, '', $linkPage, $downPage, '', $lastPage, $theEnd),
				$this->config['theme']
		);
		//echo $pageStr;exit;
        return $pageStr;

    }

}