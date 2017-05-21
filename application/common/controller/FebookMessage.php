<?php
/**
 * 飞鸽传书短信api
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-29 0029
 * Time: 14:18:15
 */

namespace app\common\controller;


class FebookMessage
{
    private $account = 13452398854;
    private $pwd     = '35d437c762e03bc4ce0c1a4a9';
    private $signId  = 30736;

    /**
     * 发送普通文字短信
     * @param string $mobile
     * @param string $content
     */
    public function sendFontMessage($mobile = '', $content = '')
    {
        if($mobile) {
            $data['Account'] = $this->account;
            $data['Pwd']     = $this->pwd;
            $data['Content'] = $content;//'您的验证码为:654524，如非本人操作，请忽略此短信。';
            $data['Mobile']  = $mobile;
            $data['SignId']  = $this->signId;

            $url = "http://api.febook.cn/SmsService/Send";
            $res = $this->sendPost($url, $data);
            echo $res;
            exit;
        }
    }

    /**
     * 发送模板短信
     * @param string $mobile
     * @param string $content（这里的content为变量值，多个变量以||进行分隔，按顺序排列）
     * @param int $templateId
     */
    public function sendTemplateMessage($mobile = '', $content = '', $templateId = 30196)
    {
        if($mobile) {
            $data['Account']    = $this->account;
            $data['Pwd']        = $this->pwd;
            $data['Content']    = $content;
            $data['Mobile']     = $mobile;
            $data['TemplateId'] = $templateId;
            $data['SignId']     = $this->signId;

            $url = "http://api.febook.cn/SmsService/Template";
            $res = $this->sendPost($url, $data);
            echo $res;
            exit;
        }
    }

    /**
     * 获取账户短信余额
     */
    public function getMessageYue()
    {
        $data['Account']    = $this->account;
        $data['Pwd']        = $this->pwd;
        $url = "http://api.febook.cn/Account/Balance?Account={$this->account}&Pwd={$this->pwd}";
        $res = $this->sendPost($url, $data);
        echo $res;
        exit;
    }

    /**
     * curl短信发送
     * @param $url
     * @param $data
     * @param null $proxy
     * @param int $timeout
     * @return mixed
     */
    private function sendPost($url, $data, $proxy = null, $timeout = 20) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。
        curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。
        curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求
        curl_setopt($curl,  CURLOPT_POSTFIELDS, $data);//Post提交的数据包
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。
        $content = curl_exec($curl);
        curl_close($curl);
        unset($curl);
        return $content;
    }
}