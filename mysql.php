<?php

/**
 * MySQL相关
 *
 * @author: Mike Cen [MikeCen9@gmail.com]
 */
class mysql {
    /**
     * @var mysqli|string
     */
    public $_db = '';

    /**
     * 初始化
     * mysql constructor.
     */
    public function __construct() {
        $this->_db = new mysqli('YOUR MYSQL HOST', 'YOU MYSQL USERNAME', 'YOUR MYSQL PASSWD', 'YOUR DBNAME');
        if ($this->_db->connect_error) {
            die('Connect Error NO:' . $this->_db->errno . 'ERROR:' . $this->_db->connect_error);
        }
        $this->_db->query("SET NAMES 'utf8'");
    }

    /**
     * 执行sql语句
     * @param $sql sql语句
     * @return bool|mysqli_result
     * @throws exception
     */
    public function que($sql) {
        if ($res = $this->_db->query($sql)) {
            return $res;
        } else {
            throw new exception('提交信息到数据库失败！详细信息：' . $this->_db->error);
        }
    }

    /**
     * 查找数据库中符合userId要求的信息
     * @param $userId 用户的userId
     * @return array
     */
    public function findFace($userId) {
        $sql = "select * from person where userId = $userId";
        $resObj = $this->que($sql);
        $res = array();
        while ($row = $resObj->fetch_assoc()) {
            $res[] = $row;
        }
        return $res;
    }
}