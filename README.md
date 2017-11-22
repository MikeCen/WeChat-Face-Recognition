##  WeChat Admin Platform face recognition. | 微信公众平台人脸识别.

#### 简介：

简单的微信公众平台人脸识别 ，通过调用相应的接口，使你可以进行人脸识别。

#### 安装：

1、Clone 或下载项目源码，上传至服务器；

2、创建 /upload 目录，此目录用于为用户或管理员上传照片并提供存储，确保拥有相应权限；

3、修改 /faceRec.php API Key / Secret，进入 /mysql.php 修改数据库相关参数；

4、进入微信公众平台，高级功能，开启开发模式，并设置接口配置信息。修改 URL 为 /wxr.php 的实际位置，并修改 Token （需与 /wx.php 中的Token一致）。

向你的微信公众号发送照片并测试吧！
