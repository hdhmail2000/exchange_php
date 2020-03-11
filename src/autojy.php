<?php
header("Content-Type: text/html; charset=UTF-8");
error_reporting(0);
date_default_timezone_set('PRC');
ignore_user_abort(true); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
set_time_limit(0); // 执行时间为无限制，php默认的执行时间是30秒，通过set_time_limit(0)可以让程序无限制的执行下去
// $interval=20; // 每隔20秒运行

function http_gets($url){
	$timeout = 5;
	if(function_exists('curl_init')){
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
	}else{
		$result = file_get_contents($url);
	}
	return $result;
}

$domain = "http://www.1993.mobi/";//填写网站域名

$fp = fopen("autojy-lock.txt", "w+");//文件锁
if (flock($fp, LOCK_EX | LOCK_NB)) {
	include('ccg.php'); // 引入文件
	if($cfg['hq']==0) break; // 如果$cron_config['run']为false,就跳出循环，执行下面的语句 echo "跳出循环";
	
	$url = $domain.'/Home/Queue/getmarketlist';
	$content = file_get_contents($url);
	$content = json_decode($content, true);

	$market = array();
	$fi = 0;
	foreach ($content as $k => $v) {
		$market[$fi] = $domain.'/Home/Queue/autojy2/market/'.$v['name'];
		$fi++;
	}
	// var_dump($market);
	for ($i = 0; $i < count($market); $i++) {
		$aaa = http_gets($market[$i]);
		// var_dump($market);
		if ($aaa) {
			$note = ' autotrade success!';
		} else {
			$note = ' autotrade fail!';
		}
		echo $market[$i].$note."<br>";
		// var_dump($aaa);
	}
	// echo "success!autotrade!";
	file_put_contents("autotradetime.html", date("Y-m-d H:i:s",time()));
    flock($fp, LOCK_UN); // 释放锁定
}else {
   echo "文件被锁定";
}
fclose($fp);
?>