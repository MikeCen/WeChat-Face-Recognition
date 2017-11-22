<?php
/**
 * 人脸识别API
 *
 * @author: Mike Cen [MikeCen9@gmail.com]
 */
class faceRec {
    /**
     * 设置 API Key
     * @var string
     */
    private $_apiKey = 'YOUR API KEY';
    /**
     * 设置 API Secret
     * @var string
     */
    private $_apiSecret = 'YOUR API SECRET';
    /**
     * 设置FaceSet的标识 faceset_token,可通过下方的createFaceSet()获取
     * @var string
     */
    private $_facesetToken = 'YOUR FACESET TOKEN';

    /**
     * facePlusPLus接口调用方法
     * @string $apiUrl 需要向API请求的URL
     * @array $apiPara 需要向API请求的参数
     * @return array 返回人脸识别结果
     */
    public function facePost($apiUrl, $apiPara) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $apiUrl,     //输入URL
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => $apiPara,
            CURLOPT_HTTPHEADER     => array("cache-control: no-cache",),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return json_decode($response, true);
        }
    }

    /**
     * 传入图片进行人脸检测和人脸分析
     * @param $picUrl 图片的 URL
     * @return array
     */
    public function faceDetect($picUrl) {
        $para = array('image_url'         => $picUrl,
                      //输入api_key和api_secret
                      'api_key'           => $this->_apiKey,
                      'api_secret'        => $this->_apiSecret,
                      'return_landmark'   => 0,
                      'return_attributes' => 'gender,age,smiling,eyestatus,emotion,beauty,ethnicity',
        );
        return $this->facePost('https://api-cn.faceplusplus.com/facepp/v3/detect', $para);
    }

    /**
     * 在一个已有的 FaceSet 中找出与目标人脸最相似的一张或多张人脸，返回置信度和不同误识率下的阈值
     * @param $faceToken 进行搜索的目标人脸的 face_token
     * @return array
     */
    public function searchFace($faceToken) {
        $para = array(
            'api_key'       => $this->_apiKey,
            'api_secret'    => $this->_apiSecret,
            'face_token'    => $faceToken,
            'faceset_token' => $this->_facesetToken,
        );
        return $this->facePost('https://api-cn.faceplusplus.com/facepp/v3/search', $para);
    }

    /**
     * 创建一个人脸的集合 FaceSet，用于存储人脸标识 face_token
     * @return array
     */
    public function createFaceSet() {
        $para = array(
            'api_key'    => $this->_apiKey,
            'api_secret' => $this->_apiSecret,
        );
        $res = $this->facePost('https://api-cn.faceplusplus.com/facepp/v3/faceset/create', $para);
        return $res;
    }

    /**
     * 为一个已经创建的 FaceSet 添加人脸标识 face_token
     * @param $faceToken 人脸标识face_token
     * @return array
     */
    public function addFaceSet($faceToken) {
        $para = array(
            'api_key'       => $this->_apiKey,
            'api_secret'    => $this->_apiSecret,
            'faceset_token' => $this->_facesetToken,
            'face_tokens'   => $faceToken,
        );
        return $this->facePost('https://api-cn.faceplusplus.com/facepp/v3/faceset/addface', $para);
    }

    /**
     * 为检测出的某一个人脸添加标识信息，该信息会在Search接口结果中返回，用来确定用户身份
     * @param $faceToken 人脸标识face_token
     * @return array
     */
    public function setUserID($faceToken) {
        $para = array(
            'api_key'    => $this->_apiKey,
            'api_secret' => $this->_apiSecret,
            'face_token' => $faceToken,
            'user_id'    => time() . mt_rand(1000, 9999),
        );
        return $this->facePost('https://api-cn.faceplusplus.com/facepp/v3/face/setuserid', $para);
    }
}