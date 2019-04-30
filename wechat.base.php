<?php
header('Content-Type: text');

	/**
	 * 功能: 发送https get|post请求, 返回响应结果
	 * @param String $url
	 * @param String $data(针对post请求)
	 * @return string $result
	 */
function httpsRequest($url, $data=null) {
	//1. 初始化回话
	$ch = curl_init();
	//2. 设置参数
		//设置请求的url
        curl_setopt($ch, CURLOPT_URL, $url);
		//TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if(!empty($data)) {
			// 发送post请求
			curl_setopt($ch, CURLOPT_POST, 1);
			// 设置发送post请求参数数据
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}
	//3. 执行会话; $result是返回的json字符串
	$result = curl_exec($ch);

	//4. 关闭会话
	curl_close($ch);

	return $result;
}

	/**
	 * 功能: 获取令牌
	 * @param String $appID
	 * @param String $appSecret
	 * @return String $result
	 */
function getAccessToken($appID, $appSecret) {
	//时效性7200s检查
	$fileName = 'accessToken.txt';
	if (is_file($fileName)) {
	    $currentTime = time();
		$modifyTime = filemtime($fileName);
		if ($currentTime - $modifyTime < 7200) {
			$accessToken = file_get_contents($fileName);
			if ($accessToken) {
                return $accessToken;
            }
		}
	}
	//超时或存储文件不存在就获取
	$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appID . "&secret=" . $appSecret;
	$tokenJsonStr = httpsRequest($url);
	$tokenJsonArr = json_decode($tokenJsonStr, true);
	$accessToken = $tokenJsonArr['access_token'];
	file_put_contents($fileName, $accessToken);
	return $accessToken;
}

	/**
	 * 功能: 拼接返回用户信息字符串
	 * @param SimpleXMLElement $postObj
	 * @param String $accessToken
	 * @return String $xml
	 */
function getUserInfo($postObj, $accessToken) {
	// 1.openID
	$openID = $postObj->FromUserName;
	// 2.url
	$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$accessToken."&openid=".$openID."&lang=zh_CN";
	// 3.发送请求
	$jsonStr = httpsRequest($url);
	// 4.json->array
	$userInfoArray = json_decode($jsonStr, true);

    // 5.拼接字符串
	$nameTmpStr = "您好, ".$userInfoArray['nickname'];
	$sexTmpStr = "性别: ".(($userInfoArray['sex'] == 1) ? "男" : (($userInfoArray['sex'] == 2) ? "女" : "未知"));
	$locationTmpStr = "地区: ".$userInfoArray['country']." ".$userInfoArray['province']." ".$userInfoArray['city'];
	$languageTmpStr = "语言: ".(($userInfoArray['language'] == "zh_CN") ? "简体中文": "未知");
	$dateTmpStr = "关注: ".date("Y年m月d日", $userInfoArray['subscribe_time']);
	$message = "输入城市名, 获取该城市天气情况, 例如: 北京天气";
	$userInfo = $nameTmpStr."\n".$sexTmpStr."\n".$locationTmpStr."\n".$languageTmpStr."\n".$dateTmpStr."\n".$message;
	// 6.返回
	return $userInfo;
}

	/**
	 * 功能: 给定三个参数, 在error.log文件中写入相应的日志消息
	 * @param  [String] $level     [日志级别(从低到高): debug, info, alert, error, emerg]
	 * @param  [String] $errorCode [错误代码]
	 * @param  [String] $content   [日志消息]
	 */
function logger($level, $errorCode, $content) {
	// 不允许超过5M
	$maxBytes = 5 * 1024 * 1024; // bytes
	$logFileName = "error.log";
	// 判断文件大小
	if(file_exists($logFileName) && (filesize($logFileName) > $maxBytes)) {
		unlink($logFileName);
	}
	$content = "[".date("Y/m/d H:i:s", time())."] [:".$level."] ".$errorCode.": ".$content."\n";
	file_put_contents($logFileName, $content, FILE_APPEND);		//FILE_APPEND 追加写入
}

/**
 * @param  [string] $sceneType   [二维码图片类型]
 * @param  [string] $sceneID   	 [场景id]
 * @param  [string] $accessToken [访问令牌]
 * @return [string] $qrCodeUrl	 [二维码图片url]
 */
function createQRcode($sceneType, $sceneID, $accessToken) {
	//1. 根据二维码类型,选择不同post数据
	switch ($sceneType) {
		case 'QR_SCENE':	//临时二维码
			$postData = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$sceneID.'}}}';
			break;
		case 'QR_LIMIT_SCENE': 	//永久二维码
			$postData = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneID.'}}}';
			break;
	}

	//2. 拼接url, 发送post请求
	$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$accessToken;
	$result = httpsRequest($url, $postData);

	//3. json结果解析, 获取ticket
	$ticketArr = json_decode($result, true);
	$ticketStr = $ticketArr['ticket'];

	//4.返回拼接的换二维码图片的url
	$qrCodeUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticketStr);

	return $qrCodeUrl;
}
