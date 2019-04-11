<?php
header('Content-Type: text');

$appid = "wxa842a8fffffa4172";
$secret = "7e41b0abe509e58bc340f76e287774b8";
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;

//1. 初始化回话
$ch = curl_init();

//2. 设置参数
	//设置请求的url
	curl_setopt($ch, CURLOPT_URL, $url);

	//TRUE 将curl_exec()获取的信息以字符串返回，而不是直接输出
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	//禁止 cURL 验证对等证书
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    //设置为 1 是检查服务器SSL证书中是否存在一个公用名, 0 不检查
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

//3. 执行会话; $result是返回的json字符串
$result = curl_exec($ch);

//4. json解析, true返回关联数组
$jsonArr = json_decode($result, true);

//5. 关闭会话
curl_close($ch);


echo $jsonArr['access_token'];