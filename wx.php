<?php
/**
 * 微信公众平台
 * @param TOKEN
 * @author: Mike Cen [MikeCen9@gmail.com]
 */
require_once './faceRec.php';
require_once './mysql.php';

//define your token
define("TOKEN", "YOUR TOKEN");
//$wechatObj->valid(); //接口配置时去掉注释!!!

$wechatObj = new Wechat();
$wechatObj->responseMsg();

class Wechat {
    /**
     * 认证相关
     */
    public function valid() {
        $echoStr = $_GET["echostr"];
        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * 认证相关
     * @return bool
     * @throws Exception
     */
    private function checkSignature() {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 回复用户消息
     */
    public function responseMsg() {
        //get post data, May be due to the different environments
        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        $postStr = file_get_contents("php://input");
        //extract post data
        if (!empty($postStr)) {
            /* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
            the best way is to check the validity of xml by yourself */
            libxml_disable_entity_loader(true);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>";

            /**
             * 用户关注时触发，回复「欢迎关注」
             *
             * @return void
             */
            if ($postObj->Event == 'subscribe') {
                $msgType = 'text';
                $contentStr = "欢迎关注！";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }

            /**
             * 用户点击二级菜单EventKey == 'FACE_RECOGNITION'时触发，回复操作提示
             *
             * @return void
             */
            if ($postObj->Event == 'CLICK' && $postObj->EventKey == 'FACE_RECOGNITION') {
                $msgType = 'text';
                $contentStr = "请直接发送需要进行人脸识别的图片即可。";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }

            /**
             * 用户点击二级菜单EventKey == 'RELEASE_INFO'时触发，回复操作提示
             *
             * @return void
             */
            if ($postObj->Event == 'CLICK' && $postObj->EventKey == 'RELEASE_INFO') {
                $msgType = 'text';
                $contentStr = "用户点击二级菜单EventKey == 'RELEASE_INFO'";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }

            /**
             * 收到图片消息时触发，回复由所收到图片人脸识别后的结果
             *
             * @return void
             */
            if ($postObj->MsgType == 'image') {
                $faceObj = new faceRec();
                $picUrl = $postObj->PicUrl;
                $res = $faceObj->faceDetect($picUrl);
                $msgType = 'text';
                if ($res['error_message']) {
                    $error = $res['error_message'];
                    if ($error == 'CONCURRENCY_LIMIT_EXCEEDED') {
                        $contentStr = '系统繁忙，休息一会再试吧！';
                    } else {
                        $contentStr = $error;
                    }
                } else {
                    if (!$res['faces'][0]['attributes']['gender']['value']) {
                        $contentStr = '尴尬，系统好像未识别到人脸。,,ԾㅂԾ,,';
                    } else {
                        $searchFace = $faceObj->searchFace($res['faces'][0]['face_token']);
                        $findFaceObj = new mysql();
                        $likeFace = $findFaceObj->findFace($searchFace['results'][0]['user_id']);
                        $contentStr = "人脸识别结果为：\n性别是：{$res['faces'][0]['attributes']['gender']['value']},\n年龄约为：{$res['faces'][0]['attributes']['age']['value']},\n人种为：{$res['faces'][0]['attributes']['ethnicity']['value']},\n--------------------------------------\n脸谱库中置信度值：{$searchFace['results'][0]['confidence']}%,\n相似脸谱的ID：{$searchFace['results'][0]['user_id']},\n姓名：{$likeFace[0]['name']},\n联系信息：{$likeFace[0]['contact']},\n其他信息：{$likeFace[0]['other']}";
                    }
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }

            /**
             * 收到文字消息时触发，回复操作提示
             *
             * @return void
             */
            if (!empty($keyword) || $keyword == '0') {
                $msgType = "text";
                $contentStr = "收到文字消息时触发，回复操作提示";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                echo "Input something...";
            }
        } else {
            echo "";
            exit;
        }
    }
}

?>
