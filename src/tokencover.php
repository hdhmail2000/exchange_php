#!/usr/bin/env php
<?php
set_time_limit(0);

function http_gets($url) {
	$timeout = 5;
	if (function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$result = curl_exec($ch);
		curl_close($ch);
	} else {
		$result = file_get_contents($url);
	}
	return $result;
}

$domain = "http://www.1993.mobi";//填写网站域名
$queues = array(
	'Home/Queue/qianbao',                                   //同步钱包转入记录
	'Home/Queue/ethonlinea88b77c11d0a9d'                      //eth同步钱包转入记录
	//'Home/Queue/etconlinea88b77c11d0a9d'                      //etc同步钱包转入记录
	//'Home/Queue/usdt',      //USDT同步钱包转入记录
	//'Home/Queue/tokensonlinea88b77c11d0a9d/coin/suf',      //suf同步钱包转入记录
	//'Home/Queue/tokensonlinea88b77c11d0a9d/coin/cw',       //cw同步钱包转入记录
	//'Home/Queue/tokensonlinea88b77c11d0a9d/coin/fff',      //fff同步钱包转入记录

);

$fp = fopen("locktokencover.txt", "w+");
if (flock($fp,LOCK_EX | LOCK_NB)) {
	foreach ($queues as $v) {
		http_gets("{$domain}/index.php/{$v}");
		sleep(1);
	}
	flock($fp,LOCK_UN);
}
fclose($fp);
echo "tokencover successfully";
?>