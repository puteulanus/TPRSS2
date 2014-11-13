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
// var_dump格式化
function dump($varVal, $isExit = FALSE){
    ob_start();
    var_dump($varVal);
    $varVal = ob_get_clean();
    $varVal = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $varVal);
    echo '<pre>'.$varVal.'</pre>';
    $isExit && exit();
}
// 字符串截断
function sub_str($string, $length, $encoding  = 'utf-8') {
    $string = strip_tags($string);
    if($length && strlen($string) > $length) {
        //截断字符
        $wordscut = '';
        if(strtolower($encoding) == 'utf-8') {
            //utf8编码
            $n = 0;
            $tn = 0;
            $noc = 0;
            while ($n < strlen($string)) {
                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1;
                    $n++;
                    $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $tn = 2;
                    $n += 2;
                    $noc += 2;
                } elseif(224 <= $t && $t < 239) {
                    $tn = 3;
                    $n += 3;
                    $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $tn = 4;
                    $n += 4;
                    $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $tn = 5;
                    $n += 5;
                    $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $tn = 6;
                    $n += 6;
                    $noc += 2;
                } else {
                    $n++;
                }
                if ($noc >= $length) {
                    break;
                }
            }
            if ($noc > $length) {
                $n -= $tn;
            }
            $wordscut = substr($string, 0, $n);
        } else {
            for($i = 0; $i < $length - 1; $i++) {
                if(ord($string[$i]) > 127) {
                    $wordscut .= $string[$i].$string[$i + 1];
                    $i++;
                } else {
                    $wordscut .= $string[$i];
                }
            }
        }
        $string = $wordscut;
    }
    return trim($string);
}