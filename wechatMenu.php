<?php
/**
 * 公众号自定义菜单创建
 * 需按提示修改下列英文全大写部分参数
 * @param $appId
 * @param $appSecret
 *
 * @author: Mike Cen [MikeCen9@gmail.com]
 */
$json = '{
	"button":[
		{
			"name":"自助工具",
			"sub_button":[
				{
					"type":"click",
					"name":"发布信息",
					"key":"RELEASE_INFO"
				},
				{
					"type":"click",
					"name":"人脸识别",
					"key":"FACE_RECOGNITION"
				}
			]
		},
		{
			"type":"view",
			"name":"关于我们",
			"url":"https://github.com/MikeCen/WeChat-Face-Recognition"
		}
	]
}';

//获取access_token
$appId = 'YOUR APPID';
$appSecret = 'YOUR APPSECRET';
$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSecret";
$access = json_decode(file_get_contents($url), true)['access_token'];

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_URL            => "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access", //输入URL
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING       => "",
    CURLOPT_MAXREDIRS      => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST  => "POST",
    CURLOPT_POSTFIELDS     => $json,
    CURLOPT_HTTPHEADER     => array("cache-control: no-cache",),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
if ($err) {
    echo "cURL Error #:" . $err;
} else {
    $res = json_decode($response, true);
    var_dump($res);
}
