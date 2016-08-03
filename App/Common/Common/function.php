<?php
/**
 * 过滤字符
 */
function filter_str($text,$parse_br = false,$quote_style = ENT_NOQUOTES)
{
	if(is_numeric($text))
		$text = (string)$text;

	if(!is_string($text))
		return null;

	if(!$parse_br){
		$text = str_replace(array("\r","\n","\t","'"),' ',$text);
	} else{
		$text = nl2br($text);
	}

	$text = htmlspecialchars($text,$quote_style,'UTF-8');
	return $text;
}

/**
 +----------------------------------------------------------
 * 字符串截取，支持中文和其它编码
 +----------------------------------------------------------
 * @static
 * @access public
 +----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
	if(function_exists("mb_substr"))
		$slice = mb_substr($str, $start, $length, $charset);
	elseif(function_exists('iconv_substr')) {
		$slice = iconv_substr($str,$start,$length,$charset);
	}else{
		$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']	  = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']	  = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("",array_slice($match[0], $start, $length));
	}
	if($suffix && $str != $slice) return $slice."...";
	return $slice;
}

/**
 * php 中文截取 utf-8
 * @param $sourcestr字符串
 * @param $cutlength 长度
 * @return string
 */
function cutStr($sourcestr,$cutlength)
{
    $returnstr='';
    $i=0;
    $n=0;
    $str_length=strlen($sourcestr);//字符串的字节数
    while (($n<$cutlength) and ($i<=$str_length))
    {
        $temp_str=substr($sourcestr,$i,1);
        $ascnum=Ord($temp_str);//得到字符串中第$i位字符的ascii码
        if ($ascnum>=224) //如果ASCII位高与224，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,3);
    //根据UTF-8编码规范，将3个连续的字符计为单个字符
            $i=$i+3; //实际Byte计为3
            $n++; //字串长度计1
        }
        elseif ($ascnum>=192) //如果ASCII位高与192，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,2);
            //根据UTF-8编码规范，将2个连续的字符计为单个字符
            $i=$i+2; //实际Byte计为2
            $n++; //字串长度计1
        }
        elseif ($ascnum>=65 && $ascnum<=90)
    //如果是大写字母，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1; //实际的Byte数仍计1个
            $n++; //但考虑整体美观，大写字母计成一个高位字符
        }
        else //其他情况下，包括小写字母和半角标点符号，
        {
            $returnstr=$returnstr.substr($sourcestr,$i,1);
            $i=$i+1; //实际的Byte数计1个
            $n=$n+0.5; //小写字母和半角标点等与半个高位字符宽…
        }
    }
    if ($str_length>($cutlength+10)){
         $returnstr = $returnstr . "…";
    //超过长度时在尾处加上省略号
    }
    return $returnstr;
}

/**********
 * 发送邮件 *
 **********/
function sendMail($address,$title,$message)
{
    vendor('PHPMailer.class#phpmailer');

    $mail=new phpmailer();
    // 设置PHPMailer使用SMTP服务器发送Email
    $mail->IsSMTP();

    // 设置邮件的字符编码，若不指定，则为'UTF-8'
    $mail->CharSet='UTF-8';

    // 添加收件人地址，可以多次使用来添加多个收件人
    $mail->AddAddress($address);
    $mail->FromName = "益云公益";                        //设置发件人的姓名

    // 设置邮件正文
    $mail->Body=$message;

    // 设置邮件头的From字段。
    $mail->From=C('MAIL_ADDRESS');


    // 设置邮件标题
    $mail->Subject=$title;

    // 设置SMTP服务器。
    $mail->Host=C('MAIL_SMTP');

    // 设置为"需要验证"
    $mail->SMTPAuth=true;
    //发送html邮件
    $mail->IsHTML(true);
    // 设置用户名和密码。
    $mail->Username=C('MAIL_LOGINNAME');
    $mail->Password=C('MAIL_PASSWORD');

    // 发送邮件。
    //return($mail->Send());
    if($mail->Send()){
		return true;
    }else{
    	return false;
    }

}


/**
 * 短信发送
 * @param $email  用户邮箱 
 */
function sendPhone($phone,$number){
		import("@.ORG.HttpClient");
		$Client = new HttpClient("mssms.cn:8000");
		$url = "http://mssms.cn:8000/msm/sdk/http/sendsms.jsp";   
		//ＰＯＳＴ的参数      
		$params = array('username'=>C('PHONE_USERNAME'),'scode'=>C('PHONE_SCODE'),'mobile'=>"$phone",'content'=>"@1@=".$number,'tempid'=>"MB-2013102300");      
		$pageContents = HttpClient::quickPost($url,$params);  
		/*
		0#数字#数字	提交成功，格式：返回值#提交计费条数#提交成功号码数
		100	发送失败
		101	用户账号不存在或密码错误
		102	账号已禁用
		103	参数不正确
		105	短信内容超过500字、或为空、或内容编码格式不正确
		106	手机号码超过100个或合法的手机号码为空
		108	余额不足
		109	指定访问ip地址错误
		110#(敏感词A,敏感词B)	短信内容存在系统保留关键词，如有多个词，使用逗号分隔：110#(李老师,XX,成人)
		114	模板短信序号不存在
		115	短信签名标签序号不存在
		*/
		return intval($pageContents);
}
function getPhoneNum(){
  return rand(1000,9999);
}

/**
 * 区分账户类型，手机号码或者邮箱
 * @param unknown_type $userName
 */
function partUserName( $userName ){
	if( preg_match("/1[3458]{1}\d{9}$/",$userName) ){
		return 2;
	}elseif( preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/",$userName) ){
		return 1;
	}else{
		return 3;
	}
}


    /**
     * post 请求
     * @param $url  请求地址
     * @param $post_data  请求数据
     * @return mixed
     */
    function curl_post($url,$post_data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        ob_start();
        curl_exec($ch);
        $result = ob_get_contents();
        ob_end_clean();
        curl_close($ch);
        return  $result;
    }
//get方式提交获取数据
function curl_get($url='', $options=array()){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if (!empty($options)){
        curl_setopt_array($ch, $options);
    }
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * 获取积分唯一性的KEY
 * @param int $uid
 * @param string $method
 * @param int $id
 * @param date $date
 * @return string
 */
function getScoreHistoryKey($uid,$method,$id,$date=null){
	if($date){
		return md5($uid.$method.$id.$date);
	}else{
		return md5($uid.$method.$id);
	}
}