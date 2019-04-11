<?php

header("Content-Type: text;charset=utf-8");
/**
 * 提供百度地图相关功能函数
 */

/**
 * 功能: 使用Memcache类的面向过程接口，存储给定openid用户的经纬度，返回字符串
 *
 * @param $openid   string  用户的加密的微信号
 * @param $Location_X   string  纬度
 * @param $Location_Y   string  经度
 *
 * @return $result  string  成功缓存或失败缓存的字符串
 */
function setLocation($openid, $locationX, $locationY) {
	$mmc = new memcached();
	$bool = $mmc->addServer('127.0.0.1', 11211);

	if ($bool) {
		// 经纬度数组
		$locationArray = ["locationX" => $locationX, "locationY" => $locationY];
		// 添加缓存
		$re = $mmc->set($openid, json_encode($locationArray), time() + 300);
		if ($re) {
			return "您的位置已缓存. \n现在可以发送附近加目标的指令，例如：附近加油站，附近酒店";
		} else {
			return '位置缓存失败';
		}
	} else {
		return '没有开启缓存系统';
	}

}

/**
 * 功能: 给定用户openid, 获取内存中用户的经纬度
 *
 * @param $openid string 用户的加密的微信号
 * @return array|string 经纬度关联数组, 或者提示字符串
 */
function getLocation($openid) {
	$mmc = new memcached();
	$bool = $mmc->addServer('127.0.0.1', 11211);

	if ($bool) {
		// 1.读取
		$locationStr = $mmc->get($openid);
		// 2.判断
		if (empty($locationStr)) {
			// 缓存中没有
			return "请先发送位置给我. \n点击底部的'+'号，再点击'位置', 发送即可";
		} else {
			// json_decode转换数组
			return json_decode($locationStr);
		}
	} else {
		return "未启用缓存系统，请先开启缓存服务";
	}
}

/**
 * 功能: 根据实体词获取周边信息
 *
 * @param $entity 搜索实体关键词
 * @return array 图文信息数组
 */
function getNearByEntity($entity, $openid) {
	$locationInfo = getLocation($openid);
	if (is_string($locationInfo)) {
		return $locationInfo;
	}
	$key = 'UbrjIQKylG56MACGqaDjpTDcDhTaeSuR';
	$url = 'http://api.map.baidu.com/place/v2/search?query=' . $entity . '&location=' . $locationInfo->locationX . ',' . $locationInfo->locationY . '&radius=1000&output=json&scope=2&page_size=7&ak=' . $key;
	$infoJsonStr = httpsRequest($url);
	$infoJsonArr = json_decode($infoJsonStr, true);
	if ($infoJsonArr['status'] != 0) {
		return $infoJsonArr['message'];
	} elseif (empty($infoJsonArr['results'])) {
		return '附近没有关于' . $entity . '的信息';
	}

	$content = array();
	$content[] = array('Title' => '附近的' . $entity, 'Description' => '', 'PicUrl' => '', 'Url' => '');
	foreach ($infoJsonArr['results'] as $item) {
		$content[] = array('Title' => '【' . $item['name'] . '】 <' . $item['detail_info']['distance'] . "米>\n" . $item['address'],
			'Description' => '',
			'PicUrl' => '',
			'Url' => '',
		);
	}

	return $content;
}