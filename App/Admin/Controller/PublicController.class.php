<?php
namespace Admin\Controller;
/**
 * 公共方法上传相片
 * @author 小强
 * create_date 2014-2-20
 */
class PublicController extends Action{
	
	/**
	 * 图片上传
	 */
	public function uploadLogo(){
		$folderName = $_POST['folderName'] ? $_POST['folderName'] : $_GET['folderName'];
		if($_FILES['adPic']['name']){
			if($_FILES["adPic"]["error"] > 0){
				switch($_FILES["adPic"]["error"]) {
					case 1:
						echo "上传文件过大！";
						break;
					case 2:
						$this->error("上传文件过大！");
						break;

					case 3:
						$this->error("文件只有部分被上传！");
						break;

					case 4:
						$this->error("没有文件被上传！");
						break;

					default:
						$this->error("文件上传末知错误！");
				}
			}
			$maxsize=5000000;  //50k
			//step 2 使用$_FILES["pic"]["size"] 限制大小 单位字节 2M=2000000
			if($_FILES["adPic"]["size"] > $maxsize ) {
				$this->error("上传的文件太大，不能超过{$maxsize}字节！");
			}
			$allowtype=array("png", "gif", "jpg", "jpeg","bmp","PNG","GIF", "JPG", "JPEG","BMP");
			$arr=explode(".", $_FILES["adPic"]["name"]);
			$hz=strtolower($arr[count($arr)-1]);
			if(!in_array($hz, $allowtype)){
				$this->error("文件格式错误！");
			}
			//$date=date('Y/m/d',time());
			$filepath="/public/upload/".$folderName."/";
			$randname=date("H").date("i").date("s").rand(100, 999).".".$hz;
			if(is_uploaded_file($_FILES["adPic"]["tmp_name"])){
				if(move_uploaded_file($_FILES["adPic"]["tmp_name"], $filepath.$randname)){
					echo trim($filepath.$randname,'.');
				}else{
					echo 0;
				}
			}else{
				echo 0;
			}
		}
	}
	
	/**
	 *改变Ueditor 默认图片上传路径
	 */
	public function checkPic(){
		import('ORG.Net.UploadFile');
		$upload = new UploadFile();// 实例化上传类
		$upload->allowExts  = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
		$upload->autoSub =true ;
		$upload->subType ='date' ;
		$upload->dateFormat ='ym' ;
		$upload->savePath =  './Public/upload/activity/';// 设置附件上传目录
		if($upload->upload()){
			$info =  $upload->getUploadFileInfo();
			echo json_encode(array(
					'url'=>$info[0]['savename'],
					'title'=>htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
					'original'=>$info[0]['name'],
					'state'=>'SUCCESS'
			));
		}else{
			echo json_encode(array(
					'state'=>$upload->getErrorMsg()
			));
		}
	}
}