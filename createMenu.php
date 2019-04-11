<?php

header("Content-Type: text");

// 测试账号
define("APP_ID", "wx3f51800720810ab5");
define("APP_SECRECT", "c94814f967e58c7bb047f761acba1efd");
require "wechat.base.php";

// 1.access_token
$accessToken = getAccessToken(APP_ID, APP_SECRECT);

// 2.JSON 数据
$jsonData = '{
  	"button": [
  		{
  			"name": "吃喝玩乐",
  			"sub_button": [
  				{
  					"name": "天上人间",
  					"type": "click",
  					"key": "TRKEY_01_01"
  				},
  				{
  					"name": "各地特色",
  					"type": "click",
  					"key": "TRKEY_01_02"
  				},
  				{
  					"name": "烧烤大全",
  					"type": "click",
  					"key": "TRKEY_01_03"
  				}
  			]
  		},
  		{
  			"name": "保护环境",
  			"sub_button": [
  				{
  					"name": "水",
  					"type": "click",
  					"key": "TRKEY_02_01"
  				},
  				{
  					"name": "空气",
  					"type": "click",
  					"key": "TRKEY_02_02"
  				},
  				{
  					"name": "森林",
  					"type": "click",
  					"key": "TRKEY_02_03"
  				}
  			]
  		},
  		{
  			"name": "关于我们",
  			"sub_button": [
  				{
  					"name": "公司简介",
  					"type": "click",
  					"key": "TRKEY_03_01"
  				},
  				{
  					"name": "社会责任",
  					"type": "click",
  					"key": "TRKEY_03_02"
  				},
  				{
  					"name": "联系方式",
  					"type": "click",
  					"key": "TRKEY_03_03"
  				}
  			]
  		}
  	]
}';
// 3.发送HTTPS POST请求
$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$accessToken;
$result = httpsRequest($url, $jsonData);
// 4.验证
echo $result;

?>