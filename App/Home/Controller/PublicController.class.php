<?php
namespace Home\Controller;
use Think\Controller;
use Org\Util\Image;
class PublicController extends Controller {
		/*
		 * 验证码
		 */
        Public function verify(){
            import('Org.Util.Image');
            Image::buildImageVerify();
        }

		/**
		 * 用户建议提交保存到数据库
		 */
		public function ajaxAddIdea(){
			if ($_SESSION['verify'] != md5($_POST['verify'])) {
				echo -1;
			}else{
				$data = array(
					'desc'   => htmlspecialchars($_POST['desc']),
					'email'  => $_POST['email'],
					'verify' => $_POST['verify'],
				);

				if (D('Idea')->addData($data)){
					echo 1;
				}else{
					echo 0;
				}
			}
		}
        
        public function uploadFile(){
            $res = array('status'=>0,'msg'=>'上传失败！');
            $folderName = $_POST['folderName'] ? $_POST['folderName'] : $_GET['folderName'];
            if($_FILES['file']['name']){
                if($_FILES["file"]["error"] > 0){
                    switch($_FILES["file"]["error"]) {
                        case 1:
                            $res['msg'] = '上传文件过大，超过最大上传限制！';
                            break;
                        case 2:
                            $res['msg'] = "上传文件过大，超过最大上传限制！";
                            break;
                        case 3:
                            $res['msg'] = "文件只有部分被上传！";
                            break;

                        case 4:
                            $res['msg'] = "没有文件被上传！";
                            break;
                        default:
                            $res['msg'] = "文件上传末知错误！";
                    }
                    exit(json_encode($res));
                }
                $maxsize=10000000;  //10M
                //step 2 使用$_FILES["pic"]["size"] 限制大小 单位字节 2M=2000000
                if($_FILES["file"]["size"] > $maxsize ) {
                    $res['msg'] = "文件大于10M，超过最大上传限制！";
                    exit(json_encode($res));
                }
                $allowtype = array("png", "gif", "jpg", "jpeg","bmp","psd","rar","zip","tar");
                $arr = explode(".", $_FILES["file"]["name"]);
                $hz = strtolower(array_pop($arr));
                if(!in_array($hz, $allowtype)){
                    $res['msg'] = '文件格式错误！';
                    exit(json_encode($res));
                }

                $filepath = "./Public/upload/".$folderName."/";
                $randname = date("H").date("i").date("s").rand(100, 999).".".$hz;
                if(is_uploaded_file($_FILES["file"]["tmp_name"])){
                    if(move_uploaded_file($_FILES["file"]["tmp_name"], $filepath.$randname)){
                        $res['msg'] = trim($filepath.$randname,'.');
                        $res['status'] = 1; //上传成功
                        exit(json_encode($res));
                    }
                }
            }
            exit(json_encode($res));
        }
		
		//琳琳专用
		public function getClick(){
			$clicks = null;
			$array = range(7069,7129);
			foreach($array as $val){
				$sql = "SELECT sum(sum) as sums FROM `st_ad_statistics_click_day_2014` WHERE ad_id = {$val}";
	 			$res =M()->query($sql);
				if($res[0]['sums']){
					$clicks[$val]['clicks'] = $res[0]['sums'];
				}else{
					$clicks[$val]['clicks'] = 0;
				} 
			}
			echo "<pre>";
			print_r($clicks);
			echo "</pre>";
			exit;
		}
		
		//琳琳专用
		public function getShow(){
			$shows = null;
			$array = range(7069,7129);
			foreach($array as $val){
				$sql = "SELECT sum(sum) as sums FROM `st_ad_statistics_show_day_2014` WHERE ad_id = {$val}";
	 			$res =M()->query($sql);
				if($res[0]['sums']){
					$shows[$val]['shows'] = $res[0]['sums'];
				}else{
					$shows[$val]['shows'] = 0;
				} 
			}
			echo "<pre>";
			print_r($shows);
			echo "</pre>";
			exit;
		
		}
        
        
    }