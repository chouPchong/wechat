<?php

header('Content-Type: text');
require "wechat.base.php";
require "weather.php";

define('TOKEN', 'renxinjing');
define('APP_ID', 'wxc2c0b8939c0f0680');
define('APP_SECRECT', '251e918de3f431ab911537e39e2d6e56');

// 1.实例化对象; Obj是object公认的缩写
$wechatObj = new WechatAPI();

// 2.调用方法
if (isset($_GET['echostr'])) {
    $wechatObj->validSignature();
} else {
    // 3.接收返回消息方法; Msg是message公认缩写
    $wechatObj->reponseMsg();
}

class WechatAPI
{
    /**
     * 验证请求来自于微信服务器
     *
     * @return String echostr字符串(成功)返回给微信服务器
     */
    public function validSignature()
    {
        $echoStr = $_GET['echostr'];

        if ($this->isCheckSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * 生成加密签名字符串, 并进行验证, 返回true/false
     *
     * @return boolean
     */
    private function isCheckSignature()
    {
        //1）将token、timestamp、nonce三个参数进行字典序排序
        $token     = TOKEN;
        $timestamp = $_GET['timestamp'];
        $nonce     = $_GET['nonce'];
        $signature = $_GET['signature'];

        $tmpArray = [$token, $timestamp, $nonce];
        sort($tmpArray);

        //2）将三个参数字符串拼接成一个字符串进行sha1加密
        // tmp是temporary临时公认缩写
        $tmpStr = implode($tmpArray);
        $tmpStr = sha1($tmpStr);

        //3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 接收用户消息, 返回消息(XML字符串)
     *
     * @return String XML字符串
     */
    public function reponseMsg()
    {
        // 一.接收消息部分
        // 1.接收
        $xmlStr = file_get_contents('php://input');

        if (!empty($xmlStr)) {
            // 2.转换成对象
            $xmlObj = simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            $type = $xmlObj->MsgType;
            switch ($type) {
                case 'text': // 文本
                    $result = $this->receiveTextMsg($xmlObj);
                    break;
                case 'image': // 图片
                    $result = $this->receiveImageMsg($xmlObj);
                    break;
                case 'event': // 事件
                    $result = $this->receiveEventMsg($xmlObj);
                    break;
                default: // 剩余消息类型(关注消息事件)
                    $result = $this->transmitText($xmlObj, "更多内容请查看<a href='http://m.jd.com'>详情页面</a>");
                    break;
            }
            echo $result;
        }
    }

    /**
     * 判断用户事件, 返回拼接XML字符串
     *
     * @param SimpleXMLElment $xmlObj
     *
     * @return String
     */
    private function receiveEventMsg($xmlObj)
    {
        switch ($xmlObj->Event) {
            case 'subscribe': //关注事件
                $accessToken = getAccessToken(APP_ID, APP_SECRECT);
                $userInfo    = getUserInfo($xmlObj, $accessToken);
                $result      = $this->transmitText($xmlObj, $userInfo);
                break;
            case 'CLICK': // click类型按钮事件
                $result = $this->handleClickEvent($xmlObj);
                break;
            default:
                # code...
                break;
        }

        return $result;
    }

    /**
     * 处理所有click类型按钮点击事件
     *
     * @param SimpleXMLElement
     * @return String
     */
    private function handleClickEvent($xmlObj)
    {
        switch ($xmlObj->EventKey) {
            case 'TRKEY_01_01':
//                 1.准备二维数组(数据来源多样化)
                $newsArray = [
                    ['Title' => '萨达姆做好战斗准备', 'Description' => '2019年1月25日,美军波斯湾登录...', 'PicUrl' => 'http://www.renxinjing.com/picture/ms001.jpeg', 'Url' => 'http://m.dianping.com'],
                    ['Title' => '母猪的产后护理', 'Description' => '2019年1月25日,宋晓峰老丈人宋富贵...', 'PicUrl' => 'http://www.renxinjing.com/picture/ms002.jpeg', 'Url' => 'http://bing.com']
                ];
//                $newsArray = getWeatherInfo('保定');
                // 2.拼接XML字符串
                $result = $this->transmitNews($xmlObj, $newsArray);
                break;
            case 'TRKEY_01_02':
                $newsArray = getWeatherInfo('保定');
                $result = $this->transmitNews($xmlObj, $newsArray);
                break;
            case 'TRKEY_01_03':
                $newsArray = getWeatherInfo('北京');
                $result = $this->transmitNews($xmlObj, $newsArray);
                break;
            default: // 剩余所有按钮, 返回文本消息
                $result = $this->transmitText($xmlObj, '更多精彩内容，还没写...');
                break;
        }

        return $result;
    }

    /**
     * 判断用户输入文本消息内容, 返回拼接XML字符串
     *
     * @param SimpleXMLElment $xmlObj
     * @return String
     */
    private function receiveTextMsg($xmlObj)
    {
        $keyword = trim($xmlObj->Content);
        if ($keyword == '图文') {
            // 1.准备二维数组(数据来源多样化)
            $content = [
                ['Title' => '萨达姆做好战斗准备', 'Description' => '2019年1月25日,美军波斯湾登录...', 'PicUrl' => 'http://www.renxinjing.com/picture/ms001.jpeg', 'Url' => 'http://m.dianping.com'],
            ];

        } elseif (strstr($keyword, '天气')) {
            // 1.获取用户输入城市名
            $cityName = str_replace('天气', '', $keyword);
            // 2.调用方法/函数, 返回未来三天天气情况(二维数组)
            $content  = getWeatherInfo($cityName);
        } else {
            $content = '你发送的是文本消息, 返回你输入的内容:' . $keyword;
        }

        if (is_array($content)) {
            // 数组: 返回图文消息
            $result = $this->transmitNews($xmlObj, $content);
        } else {
            // 字符串: 返回文本消息
            $result = $this->transmitText($xmlObj, $content);
        }

        return $result;
    }

    /**
     * 给定二维数组, 根据数组元素的个数, 拼接返回图文消息XML字符串
     *
     * @param  SimpleXMLElement $xmlObj
     * @param  Array $newsArray
     *
     * @return String XML字符串
     */
    private function transmitNews($xmlObj, $content)
    {
        if (!is_array($content)) {
            return;
        }

        // 1.循环拼接item部分
        $itemStr = '<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>';
        $tmpStr = '';
        foreach ($content as $item) {
            $tmpStr .= sprintf($itemStr, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }

        // 2.剩下
        $leftStr = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>$tmpStr</Articles>
</xml>";
        $resultStr = sprintf($leftStr, $xmlObj->FromUserName, $xmlObj->ToUserName, time(), count($content));
file_put_contents('result.txt', $resultStr);
        // 3.返回
        return $resultStr;
    }

    /**
     * 判断用户输入文本消息内容, 返回拼接XML字符串
     *
     * @param SimpleXMLElment $xmlObj
     *
     * @return String
     */
    private function receiveImageMsg($xmlObj)
    {
        $content = '你发送的是图片消息, 图片url地址是: ' . $xmlObj->PicUrl;
        $result  = $this->transmitText($xmlObj, $content);

        return $result;
    }

    /**
     * 拼接返回的文本消息XML字符串
     *
     * @return String
     */
    private function transmitText($xmlObj, $content)
    {
        $resultStr = '
            <xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
            </xml>';

        $resultStr = sprintf($resultStr, $xmlObj->FromUserName, $xmlObj->ToUserName, time(), $content);

        return $resultStr;
    }
}

/**
 * 上班代码一般提交流程:
 * 老大分需求 ==> 分析需求 ==> 实现需求(代码) ==> 自动代码分析脚本(公司自己代码规范) ==> 人工code review(抽象类/接口/语句...) ==> 提交git服务器(所有人代码记录)
 */

/**
 *
============== 用户发送文本消息XML数据包结构 ============
ToUserName:   消息接收方 ==>  公众号
FromUserName: 消息发送方 ==> OpenID用户加密微信号
CreateTime:   消息发送时间戳
MsgType: 消息类型; text(关键词)表示发送的是文本消息类型
Content: 用户发送消息内容
MsgId:   消息ID标识
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[this is a test]]></Content>
<MsgId>1234567890123456</MsgId>
</xml>


==================用户发送图片消息XML字符串=====
ToUserName:   消息接收方 ==>  公众号
FromUserName: 消息发送方 ==> OpenID用户加密微信号
CreateTime:   消息发送时间戳
MsgType: image表示发送图片消息类型
PicUrl: 图片的url地址
MediaId: 多媒体ID(图片ID)
MsgId: 消息的ID标识 $xmlObj
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<PicUrl><![CDATA[this is a url]]></PicUrl>
<MediaId><![CDATA[media_id]]></MediaId>
<MsgId>1234567890123456</MsgId>
</xml>


===============用户关注/取消关注, XML结构=========
ToUserName:   消息接收方 ==>  公众号
FromUserName: 消息发送方 ==> OpenID用户加密微信号
CreateTime:   消息发送时间戳
MsgType:      event事件
Event:        subscribe表示关注事件(关键词)

<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[subscribe]]></Event>
</xml>

============== 用户点击菜单, XML结构=======
MsgType: event事件类型
Event: CLICK点击事件
EventKey: click类型按钮创建的时候, 设置key字段
<xml>
<ToUserName><![CDATA[toUser]]></ToUserName>
<FromUserName><![CDATA[FromUser]]></FromUserName>
<CreateTime>123456789</CreateTime>
<MsgType><![CDATA[event]]></MsgType>
<Event><![CDATA[CLICK]]></Event>
<EventKey><![CDATA[EVENTKEY]]></EventKey>
</xml>




======================返回XML================

=============== 公众号返回文本消息XML数据包结构 =============
ToUserName: 消息接收方 ==> OpenID用户加密微信号
FromUserName: 消息发送方 ==> 公众号
CreateTime: 发送消息时间戳
MsgType: 返回消息类型; text(关键词)表示返回的是文本消息类型
Content: 返回消息内容
<xml>
<ToUserName><![CDATA[???]]></ToUserName>
<FromUserName><![CDATA[???]]></FromUserName>
<CreateTime>???</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[???]]></Content>
</xml>

======== 公众号返回一条图文消息XML数据包结构======
ToUserName: 消息接收方 ==> OpenID用户加密微信号
FromUserName: 消息发送方 ==> 公众号
CreateTime: 发送消息时间戳
MsgType: 返回消息类型; news(关键词)表示返回的是图文消息类型
ArticleCount: 图文消息个数; 接收消息, 返回/显示只能一条; 其他场景最多8条
Title: 图文消息标题
Description: 图文消息描述
PicUrl: 图文消息图片远程url地址
Url: 点击图文消息跳转到html页面url地址

<xml>
<ToUserName><![CDATA[??]]></ToUserName>
<FromUserName><![CDATA[??]]></FromUserName>
<CreateTime>??</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>1</ArticleCount>
<Articles>
<item>
<Title><![CDATA[??]]></Title>
<Description><![CDATA[??]]></Description>
<PicUrl><![CDATA[??]]></PicUrl>
<Url><![CDATA[??]]></Url>
</item>
</Articles>
</xml>

=============== 返回三条图文消息XML结构=======
<xml>
<ToUserName><![CDATA[??]]></ToUserName>
<FromUserName><![CDATA[??]]></FromUserName>
<CreateTime>??</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>??</ArticleCount>
<Articles>
<item>
<Title><![CDATA[??]]></Title>
<Description><![CDATA[??]]></Description>
<PicUrl><![CDATA[??]]></PicUrl>
<Url><![CDATA[??]]></Url>
</item>
<item>
<Title><![CDATA[??]]></Title>
<Description><![CDATA[??]]></Description>
<PicUrl><![CDATA[??]]></PicUrl>
<Url><![CDATA[??]]></Url>
</item>
<item>
<Title><![CDATA[??]]></Title>
<Description><![CDATA[??]]></Description>
<PicUrl><![CDATA[??]]></PicUrl>
<Url><![CDATA[??]]></Url>
</item>
</Articles>
</xml>

 */
