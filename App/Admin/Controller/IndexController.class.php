<?php
namespace Admin\Controller;
/**
 * 后台框架
 * @author 宋小平
 *
 */
class IndexController extends CommonController{
    //后台框架页
    public function index() {
    	$this->assign('channel', $this->_getChannel());
    	$this->assign('menu',    $this->_getMenu());
        $this->display();
    }

    //后台首页
    public function main() {
    	echo '<h2>这里是后台首页</h2>';
    	$this->display();
    }
	public function login(){
		$this->display();
	}
    protected function _getChannel() {
    	return array(
            'index'     => '全局',
            'Ad'        => '广告',
    		'adPlace' => '广告位',
            'Keywords'  => '关键字',     
            'Idea'      => '用户意见',
            'score'     => '积分管理',
    		'Message'     => '消息管理',
            'Project'     => '专题管理',
    		'statistics'  => '统计分析',
    	);
    }

    protected function _getMenu() {
    	$menu = array();
    	//全局
    	$menu['index'] 	=   array(
			'全局配置' => array(
				'全局配置'   => U('Admin/Deploy/index'),
				//'地区配置'   => U('Admin/Area/index'),
				'合作伙伴'   => U('Admin/Cooperation/index'),
				'合作伙伴分类' => U('Admin/CooperationConfig/index'),
				'活动管理' => U('Admin/Activity/index'),
				'全站节点' => U('Admin/Method/index'),
				'Banner图' => U('Admin/Banner/index'),
				'SEO配置'   => U('Admin/WebTitle/index'),
				'问答列表' => U('Admin/Faqs/index'),
				'问答分类' => U('Admin/FaqsType/index'),
				'清除缓存'   => '/cleancache.php'
     		),
    	);

    	//广告1
    	$menu['Ad'] = array(
			'广告管理' => array(
				'广告管理'      => U('Admin/Ad/index'),
				'广告位尺寸配置' => U('Admin/AdSizeConfig/index'),
				//'广告类型配置'   => U('Admin/AdTypeConfig/index'),
				'评论管理'   => U('Admin/Comment/index'),
				'广告缓存'      => U('Admin/Ad/cache'),
                '广告作品' => U('Admin/Production/index'),
                '广告需求' => U('Admin/Demand/index'),
				'广告赞助'=> U('Admin/AdBuy/index'),
				'广告购买'=> U('Admin/AdBuy/buyList'),
			),
    	);

    	//关键字管理
    	$menu['Keywords'] 	=	array(
    		    '应用' =>	array(
    			'关键字类别管理'	=>	U('Admin/KeywordsCategory/index'),
    			'关键字管理'	=>	U('Admin/Keywords/index'),
    		),
    	);


    	$menu['adPlace']	=	array(
			'广告位配置' => array(
				'广告位管理'    => U('Admin/Place/index'),
				'404广告位查看' => U('Admin/Place404/index'),
				//'垃圾信息管理' => U('Admin/Place404/clear'),
    		),
    	);

    	$menu['statistics'] = array(
    		'统计' 		=> array(
    			'广告统计' => U('Admin/Statistics/ad'),
    			'站长广告位' => U('Admin/Statistics/place'),
    			'404广告位'	=> U('Admin/Statistics/place404'),
    			'404广告位top20' =>U('Admin/Statistics/placeSumTop'),
    			'基于尺寸分析'	=> U('Admin/Statistics/statisticsAdBySizeId'),
    		)

    	);
        $menu['Idea'] = array(
            '用户意见' => array(
                '意见列表' => U('Admin/Idea/index'),
            )
        );

        $menu['score'] = array(
            '积分管理' => array(
                '积分配置' => U('Admin/ScoreDeploy/index'),
                '用户积分' => U('Admin/ScoreHistory/index')
            )
        );
        $menu['Message'] = array(
        		'消息管理' => array(
					'系统消息配置' => U('Admin/MessageConfig/index')
        		)
        );
        $menu['Project'] = $this->ProjectMenu();
        return $menu;
    }

    public function ProjectMenu(){
        $projectList = D("Project")->getProjectList('', 'id');
        $menu['Project'] = array(
            '专题管理' => array(
                '专题列表' => U('Admin/Project/index'),
            	'案例推广' => U('Admin/CustomerCase/index'),
            )
        );
        foreach($projectList as $key => $val){
            $menu['Project']['专题管理']["{$val['name']}列表"] = '/Admin/ProjectDetail/index/project_id/' . $val['id'];
        }
        return $menu['Project'];
    }
}
