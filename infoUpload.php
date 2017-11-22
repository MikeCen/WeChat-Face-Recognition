<?php
/**
 * 信息上传
 *
 * @author: Mike Cen [MikeCen9@gmail.com]
 */
require_once './mysql.php';
require_once './faceRec.php';
$obj = new infoUpload();
$obj->check();

class infoUpload {

    /**
     * 检测用户所提交信息是否合法
     */
    public function check() {
        header("Content-Type: text/html;charset=utf8");
        if (!$_POST['name'] || !$_POST['phone']) {
            exit('请完善填写基本信息，以便后续与您取得联系。');
        }
        $info['post'] = $_POST;
        $info['tmpName'] = $_FILES['pic']['tmp_name'];
        $info['pic'] = @getimagesize($info['tmpName']);
        if ($info['pic'] == false) {
            echo '请提交正确的照片！';
        } else {
            $this->upload($info);
        }
    }

    /**
     * 上传图片，并提交信息到数据库
     * @param $data
     */
    private function upload($data) {
        try {
            $sqlObj = new mysql();
            $name = $sqlObj->_db->real_escape_string(strip_tags($_POST['name']));
            $contact = $sqlObj->_db->real_escape_string(strip_tags($_POST['phone']));
            $message = $sqlObj->_db->real_escape_string(strip_tags($_POST['message']));
            $mime = explode('/', $data['pic']['mime']);
            $ext = '.' . $mime[1];
            $dir = './upload/' . time() . '-' . mt_rand(1000, 9999) . $ext;
            if (move_uploaded_file($data['tmpName'], $dir)) {
                if (!empty($sqlObj)) {
                    $userId = $this->face($dir);
                    $sql = "INSERT INTO person (name,contact,other,pic,userId) VALUES 
                                    ('$name','$contact','$message','$dir','$userId')";
                    if ($sqlObj->que($sql)) {
                        echo '提交成功！';
                    } else {
                        exit('照片提交成功，但其他信息提交到数据库失败！');
                    }
                }
            } else {
                echo '提交照片与信息均失败！';
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 人脸识别相关
     * @param $pic 需识别图片URL地址
     * @return mixed
     */
    private function face($pic) {
        $faceObj = new faceRec();
        $host = $_SERVER['HTTP_HOST'];
        $picUrl = 'https://' . $host . ltrim($pic, '.');
        $faceDetRes = $faceObj->faceDetect($picUrl);
        $faceToken = $faceDetRes['faces'][0]['face_token'];
        $addFaceSetObj = $faceObj->addFaceSet($faceToken);
        $setUserIdObj = $faceObj->setUserID($faceToken);
        if ($faceDetRes['error_message']) {
            exit('人脸识别出现错误，详细信息如上，请联系开发者。' . var_dump($faceDetRes));
        }
        if ($addFaceSetObj['error_message']) {
            exit('添加脸谱库出现错误，详细信息如上，请联系开发者。' . var_dump($addFaceSetObj));
        }
        if ($setUserIdObj['error_message']) {
            exit('添加脸谱库出成功，但添加用户脸谱ID时失败，详细信息如上，请联系开发者。' . var_dump($setUserIdObj));
        }
        echo '成功加入人脸识别库。用时：' . $setUserIdObj['time_used'] . '毫秒。';
        return $setUserIdObj['user_id'];
    }
}
