<?php
define('URL_CALLBACK', 'http://yibo.iyiyun.com/UserLogin/login');
return array(
    //'配置项'=>'配置值'
    // 'SHOW_PAGE_TRACE'=>1,
    'MODULE_ALLOW_LIST'    =>    array('Home','Admin','Login','Api'),
    'DEFAULT_MODULE'  => 'Home', //默认分组

    //后缀名伪静态
   'URL_HTML_SUFFIX'=>'shtml|htm|hmtl|jsp|php',
    //隐藏掉index.php等入口文件
    'URL_MODEL'=>'2',
    //严格区分大小写aa
    'APP_FILE_CASE'=>'true',
    //数据库驱动，可选择mysql，pdo等，但要对应扩展  	
    'DB_TYPE'=>'mysql',
    // 服务器地址
    'DB_HOST'   => 'localhost',
    //数据库名称
    'DB_NAME'=>'yibo',
    //数据库账号
    'DB_USER'=>'root',
    //数据库密码
    'DB_PWD'=>'123456',
    //'DB_PWD'=>'',
    // 数据库表前缀
    'DB_PREFIX' => '',
    //数据库端口
    'DB_PORT'=>'3306',
	
	//设置手机端广告位ID
	//'PHONE_PLACE' => array(7702),

    //自动侦测模板主题
    'TMPL_DETECT_THEME'=>'true',
    //默认模板后缀
    'TMPL_TEMPLATE_SUFFIX'=>'.html',
    'OUTPUT_ENCODE' => false,
    //定义模板左定界符
    'TMPL_L_DELIM'=>'{',
    //定义模板右定界符
    'TMPL_R_DELIM'=>'}',

    // Memcache 配置
    'DATA_CACHE_TYPE' => 'file',  //默认是file方式进行缓存的，修改为memcache
    'MEMCACHE_HOST'   =>  '127.0.0.1:11211',  //memcache服务器地址和端口，这里为本机。
    'DATA_CACHE_TIME' => '172800',  //过期的秒数。

	// 支付宝 配置
	'ALIPAY_CONFIG'=>array(
    'PARTNER' =>'20********50',   //这里是你在成功申请支付宝接口后获取到的PID；
    'KEY'=>'9t***********ie',//这里是你在成功申请支付宝接口后获取到的Key
    'SIGN_TYPE'=>strtoupper('MD5'),
    'INPUT_CHARSET'=> strtolower('utf-8'),
    'CACERT'=> getcwd().'\\cacert.pem',
    'TRANSPORT'=> 'http',
     ),
     //以上配置项，是从接口包中alipay.config.php 文件中复制过来，进行配置；
	'ALIPAY'   =>array(
	 //这里是卖家的支付宝账号，也就是你申请接口时注册的支付宝账号
	'SELLER_EMAIL'=>'qiang891020@163.com',
	//这里是异步通知页面url，提交到项目的Pay控制器的notifyurl方法；
	'NOTIFY_URL'=>'http://zhifubao.cn/Pay/notifyUrl',
	//这里是页面跳转通知url，提交到项目的Pay控制器的returnurl方法；
	'RETURN_URL'=>'http://zhifubao.cn/Pay/returnUrl',
	//支付成功跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参payed（已支付列表）
	'SUCCESS_PAGE'=>'',
	//支付失败跳转到的页面，我这里跳转到项目的User控制器，myorder方法，并传参unpay（未支付列表）
	'ERROR_PAGE'=>'',
	),
);