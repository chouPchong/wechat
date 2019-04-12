<?php

define("KEY", "d1c00b06d0e745cb89c475e3b36d74b9");

/**
 * 功能: 根据城市名称获取天气信息
 *
 * @param $cityName 城市名称
 * @return array    天气信息
 */
function getWeatherInfo($cityName) {
    $url = "https://free-api.heweather.com/s6/weather/forecast?location=".$cityName."&key=".KEY;
    $weatherJsonStr = httpsRequest($url);
    $weatherJsonArr = json_decode($weatherJsonStr, true);
    $weatherInfo = $weatherJsonArr['HeWeather6'][0];

    if ($weatherInfo['status'] != ok) {
        return $weatherInfo['status'];
    }

    $content = array();
    $content[] = array('Title' => $cityName.'天气预报', 'Description' => '', 'PicUrl' => '', 'Url' => '');
    foreach ($weatherInfo['daily_forecast'] as $key => $value) {
        $content[] = array(
            'Title' => $value['date']." ".$value['tmp_max']."℃~".$value['tmp_min']."℃ ".$value['wind_dir']." ".$value['wind_sc']."级\n日出|日落:".$value['sr']."|".$value['ss'],
            'Description' => '',
            'PicUrl' => 'https://www.heweather.com/files/images/cond_icon/' . $value['cond_code_d'] . '.png',
            'Url' => 'https://www.yahoo.com/news/weather'
        );
    }

    return $content;
}