<?php
namespace Admin\Controller;
use Common\ORG\Page;
/**
 * 专题 ACTION
 * @author lixiaoli
 *
 */
class ProjectDetailController extends CommonController{
    public function setClassName(){
        $this->className = 'ProjectDetail';
    }

    /**
     * @desc 专题列表
     */
    public function index(){
        $project_id = intval($_GET['project_id']);
		$projectInfo = D('Project')->find($project_id);
		if($projectInfo['type'] == 2){
			$map['project_id'] = $project_id;
			$project_list = M('project_ad')->where($map)->select();
			$this->assign('project_list',$project_list);
			$this->assign('project_id',$project_id);
			$this->display('pro_index');
			exit;
		
		}
        //按条件搜索对应的字段列表
        $fieldMap['project_id'] = $project_id;
        $fieldMap['is_search'] = 1;
        $filedData = D("ProjectField")->getDataList($fieldMap, '', 'sort_no');
	
        //搜索条件
        $map['project_id'] = $project_id;
        if($_POST['id']){
            $id = intval($_POST['id']);
            if ($id > 0) $map['id'] = $id;
            unset($_POST['id']);
        }

        $_POST = array_filter($_POST);
        if(!empty($_POST)){
            $k = 0;
            $search = null;
            foreach($_POST as $key => $val){
                $indexMap[$k+1] = $val; //页面搜索信息
                $newKey = substr($key, strrpos($key, '_')+1);
                $i = $newKey;
                $keyword = $val;

                $search[$k] = array('regexp','i:'.$i.';s:[0-9]+:"([a-z0-9]|[\u4e00-\u9fa5])*'.$keyword);
                $k++;
            }
            $map['info'] = $search;
        }

        //分页
        import("Common.ORG.Page");
        $count = D($this->className)->getCount($map);
        $Page = new Page($count, 15);
        $show = $Page->show();
        $projectList =D($this->className)->getDataList($map, '' , '', $Page->firstRow . ',' . $Page->listRows);
        foreach($map as $key=>$val) {
            $Page->parameter   .=   "$key=".urlencode($val).'&';
        }

        $this->assign('show', $show);
        $this->assign('porject_list', $projectList);
        $this->assign('project_id', $project_id);
        $this->assign('field_data', $filedData);
        $this->assign('index_map', $indexMap);
        $this->assign('id', $id);
        $this->display();
    }
	/**
	*	@desc 后台信息详情展示老页面
	*/
    public function info(){
        $id = intval($_GET['id']);
        $projectInfo = D($this->className)->getDataById($id);
        $info = $projectInfo['info'];
        foreach($info as $key => $val){
            if(is_array($val)){
                $info[$key] = join('&nbsp;&nbsp;', $val);
            }
        }
        $this->assign('info', $info);
        $this->display();
    }

    /**
     *@desc 添加专题
     */
    public function add(){
        $project_id = intval($_GET['project_id']);
		$projectInfo = D('Project')->find($project_id);
		$this->assign('project_id', $project_id);
		$this->assign('projectInfo',$projectInfo);
		if($projectInfo['type'] == 2){
			//先要获取类别
			$catList = D('KeywordsCategory')->getDataList();
			
			//获取所有的广告信息
			$map['size_id'] = 54;
			$adList = D('Ad')->where($map)->select();
			$lists = M('project_ad')->field('ad_id')->where('project_id = '.$project_id)->select();
			$proAdList= array();
			foreach($lists as $val){
				array_push($proAdList,$val['ad_id']);
			}

			$this->assign('adList',$adList);
			$this->assign('proAdList',$proAdList);
			$this->assign('catList',$catList);
			$this->display('pro_add');
		}else{
			$map['project_id'] = $project_id;
			$map['status'] = 1;
			$projectData = D("Project")->getDataList();
			$fieldData = D("ProjectField")->getDataList($map, '', 'sort_no');
			$this->assign('field_data', $fieldData);
			$this->assign('project_data', $projectData);
			$this->display();
		}
      
    }

    /**
     *@desc保存专题
     */
    public function saveAdd(){
		if($_POST['type'] == 2){
			//$count1 = M('project_ad')->count();
			$project_id = intval($_POST['project_id']);
			//先删除所有该专题的信息，在存入
			M('project_ad')->where('project_id = '.$project_id)->delete();
			foreach($_POST['ad_id'] as $val){
				$sl .= "(".$project_id.",".$val."),"; 
			}
			$sql = "insert into project_ad(project_id,ad_id) values".trim($sl,',');
			$res = M()->query($sql);
			$count2 = M('project_ad')->count();
			$id = $count2 ? true : false;
		}else{
			 $data['project_id'] = intval($_POST['project_id']);
			unset($_POST['project_id']);

			foreach($_POST as $key => $val){
				$newKey = substr($key, strrpos($key, '_')+1);
				$tmp[$newKey] = $val;
			}
			$data['info'] = serialize($tmp);
			$id = D($this->className)->add($data);
		}
	
        if($id){
            $this->success("添加成功","/Admin/ProjectDetail/index?project_id=".$project_id);
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }

    /**
     * @desc 修改专题
     */
    public function edit(){
        $id = intval($_GET['id']);
        $project_id = intval($_GET['project_id']);
        $data = D( $this->className )->getDataById($id);
        $info = $data['info'];
        $this->assign('data', $data);
        $this->assign('id', $id);
        $this->assign('info', $info);
        $this->assign('project_id', $project_id);
        $this->display();
    }

    /**
     * @desc 保存修改的专题
     */
    public function saveEdit(){
        $id = intval($_POST['id']);
        $project_id = intval($_POST['project_id']);
        unset($_POST['id']);
        unset($_POST['project_id']);

        foreach($_POST as $key => $val){
            $newKey = substr($key, strrpos($key, '_')+1);
            $tmp[$newKey] = $val;
        }

        $data['info'] = serialize($tmp);

        $res = D( $this->className )->where("id={$id}")->save($data);

        if($res){
            $this->success("修改成功","/Admin/ProjectDetail/index?id=".$id . "&project_id=" . $project_id);
        }else{
            $this->error(D( $this->className )->getLastError());
        }
    }
	
	/**
     * @desc 更改专题状态 Ajax
     */
    public function changeStatusAjax(){
        $data['id'] = $_GET['id'];
        $data['status'] = $_GET['status'];
        $res = D($this->className)->saveDataById($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
	
	/**
	*	@desc后台专题详情展示新页面
	*
	*/
	public function showsDetail(){
		$id = $_GET['id'];
		if($_GET['type'] == 2){
			$pro_ad = M('project_ad')->find($id);
			$adInfo = D('Ad')->find($pro_ad['ad_id']);
			$this->assign('adInfo',$adInfo);
			$this->display('proInfo');
		}else{
			$detailInfo = D($this->className)->getDataById($id);
			$projectId = $detailInfo['project_id'];
			$projectInfo = D('Project')->getDataById($projectId);

			$info = $detailInfo['info'];

			foreach($info as $key => $val){
				$fieldInfo = D('ProjectField')->getDataById($key, 'is_individual, field_name, status, sort_no');

				if($fieldInfo['status'] == 1){      //字段状态为1
					if($fieldInfo['is_individual'] == 1){   //如果字段is_individual(是否单独显示)为1，组成一个数组
						$temp[$fieldInfo['field_name']] = $val;
					}else{                                  //其他的组成另外的数组
						$tempVal[$fieldInfo['sort_no']] = $val;
						$tempName[$fieldInfo['sort_no']] = $fieldInfo['field_name'];
					}
				}
			 }
			//按sort_no排序，保证信息展示也是按sort_no排序
			ksort($tempVal);
			ksort($tempName);
			$tempInfo = array_combine($tempName, $tempVal);
			$projectDetailInfo = $temp;
			$projectDetailInfo['info'] = $tempInfo;
			foreach($projectDetailInfo['info'] as $key => $val){
				if($projectDetailInfo['info'][$key] == ''){
					unset($projectDetailInfo['info'][$key]);
					continue;
				}
				if(mb_strlen($projectDetailInfo['info'][$key],'utf-8') >= 30){
					$projectDetailInfo['info'][$key] = msubstr($projectDetailInfo['info'][$key],0,30);
				}
			}
            $projectDetailInfo['url'] = base64_encode($projectDetailInfo['url']);
			$this->assign('projectDetailInfo',$projectDetailInfo);
			$this->display("Home@Template:Tpl404_{$projectInfo['template_id']}");
		}
	}
	
	/**
	 * @desc 设计师专题
	 *
	 */
	public function getAd(){
		$cid = intval($_POST['cid']);
		$where = " 1 = 1 ";
		$where .= " and  find_in_set(".$cid.",`keyword_ids`) ";
		$where .= " and status = 2 ";
		$where .= " and size_id = 54 ";
		$sql = "select * from ad where ".$where;
		$res = M()->query($sql);
		if($res){
			$html = '<table class="user_table" width="100%" cellspacing="0" cellpadding="0" border="0"><tr><th><input type="checkbox" id="checkbox_handle" onclick="checkAll(this)" value="all">全选</th><th>名称</th></tr>';
			foreach($res as $key=>$val){
				$html .= <<<EOF
					<tr><td><input type="checkbox" name="ad_id[]" id="" value="{$val['id']}" ></td><td>{$val['title']}</td></tr>
EOF;
			}
			$html .='</table>';
			$this->ajaxReturn($html,'success',1);
			
		}else{
			$html = "<p>没有相关作品信息</p>";
			$this->ajaxReturn($html,'failed',0);
		}
	}
	
	/**
	 * @desc 删除专题和作品关联表信息
	 */
	public function delProAd(){
		$map['id'] = intval($_GET['id']);
		$id = M('project_ad')->where($map)->delete();
		if($id){
			echo 1;
			exit;
		}
	}
	
	/**
	 * @desc 更改信息状态
	 */
	public function changeProAd(){
		$data['id'] = $_GET['id'];
        $data['status'] = $_GET['status'];
        $res = M('project_ad')->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
	}
	
}