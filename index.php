<?php

require_once('tmhOAuth.php');// 导入OAuth库
require_once('config.inc.php');// 导入配置文件
require_once('functions.inc.php');// 导入自定义函数
// 设定编码
mb_internal_encoding("UTF-8");

// 获取用户名或图片地址
$user = trim($_GET['user']);
$pic_url = trim($_GET['pic']);
if ($pic_url){
	Header("Content-type: image/jpg");
	echo file_get_contents($pic_url);
	exit;
}elseif (!$user){// 随机推荐订阅
	$rand_user_list = array('nisopict_bot_kr','nisopict_bot_k2','kneehigh_bot','akogare_ryoiki','exposed_cranium');
	$user = $rand_user_list[rand(0,count($rand_user_list) - 1)];
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: ".CDN_API_URL."?user={$user}");
	exit;
}
// 检查是否存在用户
if (!check_user($user)){
	header("Content-Type: text/html; charset=utf-8");
	echo '用户不存在，请核对后输入正确的用户名';
	exit;
}
// 判断是否使用CDN
if ($_GET['cdn'] == 'on'){
	// 判断CDN是否正常工作
	if (file_get_contents(CDN_API_URL."?check=on") == 'OK'){
		$pic_out_url = CDN_API_URL;
	}else{// CDN故障时切到本体输出
		$pic_out_url = TPRSS_API_URL;
	}
}else{
	$pic_out_url = TPRSS_API_URL;
}
// 从推特获取用户相关信息
$twitter_timeline = get_twitter_timeline();
if ($_GET['debug'] == 'on'){
	header("Content-Type: text/html; charset=utf-8");
	echo '<h1>[DEBUG] Response from twitter server:</h1>';
	dump($twitter_timeline);
	die();
}
// 输出RSS头
rss_head_out();
// 输出用户信息
foreach ($twitter_timeline as $tweet) {
	echo '<item>'.PHP_EOL;
	preg_match('/(.+)http:\/\/t\.co\/\w+|(.+)/',$tweet['text'],$title);
	// 去除发图时最后的网址
	if ($title[1]){
		$title = trim($title[1]);
	}else{
		$title = trim($title[2]);
	}
	// 限制字数
	if (strlen($title) > 60){
		$title = sub_str($title, 60).'。。。';
	}
	echo '<title>'.$title.'</title>'.PHP_EOL;
	echo '<author>'.$user.'</author>'.PHP_EOL;
	echo '<pubDate>'.date('r',strtotime($tweet['created_at'])).'</pubDate>'.PHP_EOL;
	echo '<guid isPermaLink="true">https://twitter.com/'.$user.'/statuses/'.$tweet['id'].'</guid>'.PHP_EOL;
	echo '<link>https://twitter.com/'.$user.'/statuses/'.$tweet['id'].'</link>'.PHP_EOL;
	$text = $tweet['text'];
	// 转换推特链接
	$text = preg_replace('/(https?:\/\/t\.co\/\w+)(?=\s|$)/', '<a href=$1>$1</a>', $text);
	// 去掉发图时的本页链接
	$text = preg_replace('/<a href='.preg_quote($tweet['extended_entities']['media'][0]['url']).'>'.preg_quote($tweet['extended_entities']['media'][0]['url']).'<\/a>/', '', $text);
	echo '<description><![CDATA['.nl2br($text);
	if (isset($tweet['extended_entities']['media'])) {
		echo '<br />';
		foreach ($tweet['extended_entities']['media'] as $media) {
			echo '<img src="'.$pic_out_url.'?pic='.$media['media_url'].'">';
			echo '<br />';
		}
	}
	echo ']]></description>'.PHP_EOL;
	echo '</item>'.PHP_EOL;
}
echo '</channel>'.PHP_EOL;
echo '</rss>'.PHP_EOL;

