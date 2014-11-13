<?php
// 用户检查
function check_user($user){
	global $user;
	$curl = curl_init();
	curl_setopt($curl,CURLOPT_URL,"https://twitter.com/{$user}");// 获取内容url
	curl_setopt($curl,CURLOPT_HEADER,1);// 获取http头信息
	curl_setopt($curl,CURLOPT_NOBODY,1);// 不返回html的body信息
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);// 返回数据流，不直接输出
	curl_setopt($curl,CURLOPT_TIMEOUT,10); // 超时时长，单位秒
	curl_exec($curl);
	if (curl_getinfo($curl,CURLINFO_HTTP_CODE) == '404'){
		$user_status = false;
	}else{
		$user_status = true;
	}
	curl_close($curl);
	return $user_status;
}
// 获取用户时间线
function get_twitter_timeline(){
	global $user;
	$tmhOAuth = new tmhOAuth(array(
		'consumer_key' => CONSUMER_KEY,
		'consumer_secret' => CONSUMER_SECRET,
		'token' => USER_TOKEN,
		'secret' => USER_SECRET,
	));
	if ($tmhOAuth -> request('GET',$tmhOAuth -> url('1.1/statuses/user_timeline.json'),array(
		'include_entities' => 'false',
		'include_rts' => 'true',
		'trim_user' => 'true',
		'screen_name' => $user,
		'exclude_replies' => 'false',
		'count' => RSS_COUNT), true) != 200){
		header("Content-Type: text/html; charset=utf-8");
		die('Could not connect to Twitter');
	}
	return json_decode($tmhOAuth -> response['response'],true);
}
// RSS头输出
function rss_head_out(){
	global $user,$twitter_timeline;
	header('Content-Type: text/xml; charset=utf-8');
	echo'<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL;
	echo '<rss version="2.0" xmlns:content="http://www.w3.org/2005/Atom">'.PHP_EOL;
	echo '<channel>'.PHP_EOL;
	echo '<title>Twitter feed @'.$user.'</title>'.PHP_EOL;
	echo '<description>Twitter feed @'.$user.' through TPRSS</description>'.PHP_EOL;
	echo '<link>https://twitter.com/'.$user.'</link>'.PHP_EOL;
	echo '<pubDate>' . date('r', strtotime($twitter_timeline[0]['created_at'])).'</pubDate>'.PHP_EOL;
	echo '<lastBuildDate>'.date('r').'</lastBuildDate>'.PHP_EOL;
}