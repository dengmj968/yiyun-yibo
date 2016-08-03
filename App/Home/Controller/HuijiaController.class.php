<?php
namespace Home\Controller;
use Common\Controller\BaseController;
use Common\ORG\Page;
class HuijiaController extends BaseAction{
    /**
     * 首页
     */
    function index(){
        $this->display();
    }

    /**
     * 404孩子详情页
     */
    function webmaster(){
        $map['project_id'] = 1;
        $map['status']     = 1;
        import("Common.ORG.Page");
        $count = D('ProjectDetail')->getCount($map);
        $Page = new Page($count, 10);
        $show = $Page->show();
        $projectList =D('ProjectDetail')->getDataList($map, '' , '', $Page->firstRow . ',' . $Page->listRows);
        foreach($projectList as $key => $val){
            $info = unserialize($val['info']);
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
            $newInfo = $temp;
            $newInfo['info'] = $tempInfo;

            $infoArr[] = $newInfo;
        }
        $this->assign('show', $show);
        $this->assign('count', $count);
        $this->assign('porject_list', $infoArr);
        $this->display();
    }

}