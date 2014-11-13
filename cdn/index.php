<?php
// 设定PHP层地址
$api_url = 'https://tpress-xiguaiyong.rhcloud.com/index.php';
// 获取图片地址
$pic = $_GET['pic'];
if ($pic){
	preg_match('/\/([^\.\/]+\.\w{3})(:large)?$/', $pic,$pic_file_name);
	$pic_file_name = $pic_file_name[1];
	preg_match('/\.(\w{3})$/', $pic_file_name,$file_type);
	$file_type = $file_type[1];
	$pic_file_name = md5($pic_file_name);
	if($cache_file = glob('pic-1/'.$pic_file_name.'*')){
		Header("Content-type: image/".$file_type);
    	echo file_get_contents($cache_file[0]);
    	exit;
	}elseif ($cache_file = glob('pic-2/'.$pic_file_name.'*')){
		Header("Content-type: image/".$file_type);
    	echo file_get_contents($cache_file[0]);
    	exit;
	}else{
    	$pic_file = file_get_contents($api_url.'?pic='.$pic);
    	file_put_contents('pic-1/'.$pic_file_name.'-'.strlen($pic_file).'.'.$file_type, $pic_file);
    	Header("Content-type: image/".$file_type);
    	echo $pic_file;
    	exit;
	}
}
// 检查文件夹大小
if ($_GET['check'] == 'on'){
	$pic_dir = opendir("pic-1");
	$pic_dir_size = 0;
	while (($pic_file_name = readdir($pic_dir)) !== false){
		if ($pic_file_name != '.' and $pic_file_name != '..' and $pic_file_name != 'keepdir.md' ){
			preg_match('/-(\d+)\.\w{3}$/',$pic_file_name,$pic_size);
			$pic_size = (int)$pic_size[1];
			$pic_dir_size += $pic_size;
		}
	}
	closedir($pic_dir);
	if ($pic_dir_size > 209715200){
		$pic_dir = opendir("pic-2");
		while (($pic_file_name = readdir($pic_dir)) !== false){
			if ($pic_file_name != '.' or $pic_file_name != '..'){
				unlink('pic-2/'.$pic_file_name);
			}
		}
		closedir($pic_dir);
		rmdir('pic-2');
		rename('pic-1', 'pic-2');
		mkdir('pic-1');
	}
	echo 'OK';
	exit;
}
// 获取用户名
$user = $_GET['user'];
if (!$user){
	header("HTTP/1.1 301 Moved Permanently");
    header("Location: http://tprss.puteulanus.com/");
	exit;
}
// 输出RSS信息
if ($_GET['cdn'] == 'on'){
    echo file_get_contents($api_url.'?user='.$user.'&cdn=on');
}else{
	echo file_get_contents($api_url.'?user='.$user);
}
