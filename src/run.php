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
	'Admin/Login/queue',      //记录最后执行时间
	'Home/Queue/chart',       //计算行情
	'Home/Queue/tendency',    //计算趋势,每天运行一次即可
	'Home/Queue/houprice',    //更新市场价格
	'Home/Queue/paicuo',      //自动匹配交易
	'Home/Queue/qianbao',     //同步钱包转入记录
	
/*	'Home/Queue/usdt',      //USDT同步钱包转入记录
	'Home/Queue/tokensonlinea88b77c11d0a9d/coin/suf',      //同步钱包转入记录
	'Home/Queue/tokensonlinea88b77c11d0a9d/coin/cw',       //同步钱包转入记录
	'Home/Queue/tokensonlinea88b77c11d0a9d/coin/fff',      //同步钱包转入记录
*/
	
	'Home/Queue/move',         //处理交易状态:正常
	'Home/Queue/yichang',      //处理交易状态:异常
);

$fp = fopen("lockrun.txt", "w+");
if (flock($fp,LOCK_EX | LOCK_NB)) {
	foreach ($queues as $v) {
		http_gets("{$domain}/index.php/{$v}");
		sleep(1);
	}
	flock($fp,LOCK_UN);
}
fclose($fp);
echo "run successfully";
?>