<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-28 0028
 * Time: 03:25:08
 */

namespace app\common\controller;


class MessageApi
{
    /**
     * 发送初始密码短信
     * @param $phone
     * @param $password
     * @return mixed
     */
    public function passwordMsg($phone, $password)
    {
        $nr = '您好，感谢您注册嘉瑞百合缘，您的初始密码为：' . $password . '，请及时前往个人中心修改您的密码。【嘉瑞百合缘】';
        $nr = mb_convert_encoding($nr, "GB2312", "UTF-8");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.106168.net/smsComputer/smsComputersend.asp?zh=cqjr&mm=55352177&hm=' . $phone . '&nr=' . $nr . '&dxlbid=15');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    /**
     * 发送验证码
     * @param $phone
     * @return bool
     */
    public function sendCode($phone)
    {
        $code = rand(100000, 999999);
        $output = $this->sendTemplateSMS("$phone", ["$code", '5分钟'], 117244);
        session('reg_code', $code);
//        $nr = '验证码为：'.$code.'，实名认证婚恋网【嘉瑞百合缘】';
        //\Yii::$app->session->set('reg_code', $code);
//        $nr = mb_convert_encoding($nr, "GB2312", "UTF-8");
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'http://www.106168.net/smsComputer/smsComputersend.asp?zh=cqjrhj&mm=666666&hm='.$phone.'&nr='.$nr.'&dxlbid=19');
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($ch, CURLOPT_HEADER, 0);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//        $output = curl_exec($ch);
//        curl_close($ch);
//        if($output == '0'){
//           return true;
//        }
        return $output;
    }

    /**
     * 判断验证码是否正确
     * @param $code
     * @return bool
     */
    public function validataCode($code)
    {
        if (\Yii::$app->session->get('reg_code') && intval($code) == intval(\Yii::$app->session->get('reg_code'))) {
            \Yii::$app->session->remove('reg_code');
            return true;
        }
        return false;
    }


    /**
     * 发送模板短信
     * @param to 手机号码集合,用英文逗号分开
     * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id
     */
    function sendTemplateSMS($to, $datas, $tempId)
    {
        //主帐号
        $accountSid = '8aaf0708570871f80157212d6a1b0d26';

        //主帐号Token
        $accountToken = '001a1126e699436faa84939fdd4fa5ca';

        //应用Id
        $appId = '8aaf0708570871f80157212d6aa90d2b';

        //请求地址，格式如下，不需要写https://
        $serverIP = 'app.cloopen.com';

        //请求端口
        $serverPort = '8883';

        //REST版本号
        $softVersion = '2013-12-26';

        // 初始化REST SDK
//        global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
        $rest = new REST($serverIP, $serverPort, $softVersion);
        $rest->setAccount($accountSid, $accountToken);
        $rest->setAppId($appId);

        // 发送模板短信
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
        if ($result == NULL) {
            return false;
        }
        if ($result->statusCode != 0) {
            return false;
        } else {
            return true;
        }
    }

//Demo调用,参数填入正确后，放开注释可以调用
//sendTemplateSMS("手机号码","内容数据","模板Id");

}