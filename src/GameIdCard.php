<?php
# -*- coding: utf-8 -*-
# @Time    : 2022/1/19 下午3:11
# @Author  : jiange95
# @File    : GameIdCard.php
# @Software: PhpStorm

namespace JanGe\SmallKit;

class GameIdCard
{

    //实名认证验证地址
    private $urlCheck = "https://api.wlc.nppa.gov.cn/idcard/authentication/check";
    //游戏备案号
    private $bizId = '游戏备案号';
    //appID
    private $appId = 'appID';
    //加密用的key
    private $secretKey = '加密用的key';
    //实名认证查询地址：
    private $urlQuery = "https://api2.wlc.nppa.gov.cn/idcard/authentication/query";
    //网络游戏实名认证退出地址
    private $urlLoginOut = "https://api2.wlc.nppa.gov.cn/behavior/collection/loginout";

    //send GET request
    private function curl_get($url, $appId, $bizId, $timestamps, $sign)
    {
        $curl = $this->getCurl($appId, $bizId, $timestamps, $sign, $url);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        //echo $data;
        return $data;
    }
    //send POST request
    private function curl_post($url, $appId, $bizId, $timestamps, $sign, $postData)
    {
        $curl = $this->getCurl($appId, $bizId, $timestamps, $sign, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    /**
     * @param int $timestamps
     * @param array $data
     * @return string
     */
    private function sign(int $timestamps,array $data): string
    {
        $str = $this->secretKey . 'appId' . $this->appId . 'bizId' . $this->bizId . 'timestamps' . $timestamps . $data;
        return hash("sha256", $str);
    }

    /**
     * @param int $timestamps
     * @param array $data
     * @return string
     */
    private function signq(int $timestamps,array $data)
    {
        $str = $this->secretKey . $data . 'appId' . $this->appId . 'bizId' . $this->bizId . 'timestamps' . $timestamps;
        return hash("sha256", $str);
    }

    /**
     * @param $name
     * @param $idCard
     * @param $user_name
     * @return void
     */
    public function chickID($name,$idCard,$user_name)
    {
        $data2 = shell_exec("php /www/wwwroot/shouyou/control/gcm.php a=1 ai=" . md5($user_name) . " name=" . $name . " idNum=" . $idCard);
        list($data, $dataa) = $this->extracted($data2);
        $data3 = json_decode($dataa, true);

        if ($data3['errcode'] != 0 || $dataa == null) {
            //认证失败
            echo json_encode(['status' => $data3['errcode']]);
            return;
        } else {
            $a = $data3['data']['result']['status'];

            if ($a == 0 || $a == '0') {
                //认证成功修改用户
                echo json_encode(['pi' => $data3['data']['result']['pi'], 'status' => 1]);
                return;
            } elseif ($a == 1 || $a == '1') {
                //认证中修改用户实名状态
                echo json_encode(['status' => 2]);
                return;
            } else {
                //认证失败
                file_put_contents("err.txt", 'data:' . microtime(true) . var_export($data, true) . PHP_EOL, FILE_APPEND);
                echo json_encode(['status' => 4]);
                return;
            }

        }
    }

    /**
     * @return void
     */
    public function query()
    {
        $user_name = req::item('username');
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->signq($timestamps[0], 'ai' . $user_name);
        $url = $this->urlQuery . "?ai=" . $user_name;
        $dataa = $this->curl_get($url, $this->appId, $this->bizId, $timestamps[0], $sign);
        $data3 = json_decode($dataa, true);


        if ($data3['errcode'] != 0) {
            //认证失败
            echo json_encode(['status' => 4]);
            return;
        } else {
            $a = $data3['data']['result']['status'];

            if ($a == 0 && $a != null) {
                //认证成功修改用户
                echo json_encode(['pi' => $data3['data']['result']['pi'], 'status' => 1]);
                return;
            } elseif ($a == 1) {
                //认证中修改用户实名状态
                echo json_encode(['status' => 2]);
                return;
            } else {
                //认证失败
                echo json_encode(['status' => 4]);
                return;
            }

        }
    }


    public function loginout()
    {
        $pi = req::item('pi');
        $bt = req::item('bt');
        $ct = req::item('cta');
        $di = req::item('di');
        $user_name = req::item('username');
        $data2 = shell_exec("php /www/wwwroot/shouyou/control/gcm.php a=0 no=1 si=" . md5($user_name) . " bt=" . $bt . " ot=" . time() . " ct=" . $ct . " di=" . md5($user_name . $di) . " pi=" . $pi);
        $data = json_encode(["data" => $data2]);
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->sign($timestamps[0], $data);
        $dataa = $this->curl_post($this->urlLoginOut, $this->appId, $this->bizId, $timestamps[0], $sign, $data);
        $data3 = json_decode($dataa, true);

        if ($data3['errcode'] != 0) {
            //发送失败
            echo json_encode(['code' => 0]);
            return;
        } else {
            //发送成功
            echo json_encode(['code' => 1]);
            return;

        }

    }

    public function aesGcmEncrypt($string)
    {
        $cipher = strtolower('AES-128-GCM');
        if (is_array($string)) $string = json_encode($string);
        //二进制key
        $skey = hex2bin($this->secretKey);
        //二进制iv
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

        list($content, $tag) = \AESGCM\AESGCM::encrypt($skey, $iv, $string);
        //如果环境是php7.1+,直接使用下面的方式
        //  $tag = NULL;
        //  $content = openssl_encrypt($string, $cipher, $skey,OPENSSL_RAW_DATA,$iv,$tag);
        $str = bin2hex($iv) . bin2hex($content) . bin2hex($tag);
        return base64_encode(hex2bin($str));
    }

    /**
     * 基础CURL配置
     * @param $appId
     * @param $bizId
     * @param $timestamps
     * @param $sign
     * @param $url
     * @return false|resource
     */
    private function getCurl($appId, $bizId, $timestamps, $sign, $url): bool
    {
        $headers = array();
        $headers[] = "Content-Type:application/json;charset=utf-8";
        $headers[] = "appId:" . $appId;
        $headers[] = "bizId:" . $bizId;
        $headers[] = "timestamps:" . $timestamps;
        $headers[] = "sign:" . $sign;
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        return $curl;
    }

    /**
     * 根据配置发送报告到中宣部网络实名认证
     * @param string|null $data2
     * @return array
     */
    private function extracted(?string $data2): array
    {
        $data = json_encode(["data" => $data2]);
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->sign($timestamps[0], $data);
        $dataa = $this->curl_post($this->urlCheck, $this->appId, $this->bizId, $timestamps[0], $sign, $data);
        return array($data, $dataa);
    }


}