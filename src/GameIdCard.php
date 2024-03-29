<?php
# -*- coding: utf-8 -*-
# @Time    : 2022/1/19 下午3:11
# @Author  : jiange95
# @File    : GameIdCard.php
# @Software: PhpStorm

namespace JanGe\SmallKit;

use Exception;
use JsonException;
use JanGe\SmallKit\Helper;

class GameIdCard
{

    //网络游戏实名认证验证地址
    private $urlCheck = "https://api.wlc.nppa.gov.cn/idcard/authentication/check";
    //游戏备案号
    private $bizId = '游戏备案号';
    //appID
    private $appId = 'appID';
    //加密用的key
    private $secretKey = '加密用的key';
    //网络游戏实名认证查询地址：
    private $urlQuery = "https://api2.wlc.nppa.gov.cn/idcard/authentication/query";
    //网络游戏实名认证退出地址
    private $urlLoginOut = "https://api2.wlc.nppa.gov.cn/behavior/collection/loginout";


    /**
     * 发送get请求
     * @param $url
     * @param $appId
     * @param $bizId
     * @param $timestamps
     * @param $sign
     * @return bool|string
     */
    private function curl_get($url, $appId, $bizId, $timestamps, $sign)
    {
        $curl = $this->getCurl($appId, $bizId, $timestamps, $sign, $url);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //返回获得的数据
        return $data;
    }

    /**
     * 发送post请求
     * @param $url
     * @param $appId
     * @param $bizId
     * @param $timestamps
     * @param $sign
     * @param $postData
     * @return bool|string
     */
    private function curl_post($url, $appId, $bizId, $timestamps, $sign, $postData)
    {
        $curl = $this->getCurl($appId, $bizId, $timestamps, $sign, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //返回获得的数据
        return $data;
    }

    /**
     * 编码一查询身份证时候存在
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
     * 编码二主动查询是否成功
     * @param int $timestamps
     * @param string $data
     * @return string
     */
    private function signq(int $timestamps,string $data): string
    {
        $str = $this->secretKey . $data . 'appId' . $this->appId . 'bizId' . $this->bizId . 'timestamps' . $timestamps;
        return hash("sha256", $str);
    }

    /**
     * 实名认证入口
     * @param string $name //用户真实姓名
     * @param string $idCard //用户身份证号
     * @param string $user_name //用户标识
     * @return string
     * @throws JsonException
     */
    public function chickID(string $name,string $idCard,string $user_name) :string
    {
        $data2=$this->aesGcmEncrypt(json_encode(['ai' => $user_name, 'name' => $name, 'idNum' => $idCard], JSON_THROW_ON_ERROR));
        list($data, $dataa) = $this->extracted($data2);
        $data3 = json_decode($dataa, true);
        if ($data3['errcode'] != 0 || $dataa == null) {
            //认证失败
            return json_encode(['status' => $data3['errcode']]);
        } else {
            $a = $data3['data']['result']['status'];
            if ($a == 0 || $a == '0') {
                //认证成功修改用户
                return Helper::response(201,'实名认证成功',['pi' => $data3['data']['result']['pi']]);
            } elseif ($a == 1 || $a == '1') {
                //认证中修改用户实名状态
                return Helper::response(201,'实名认证中请稍等');
            } else {
                //认证失败
                return Helper::response(400,'实名认证失败',$data);
            }

        }
    }

    /**
     * 根据用户名查询对应的实名认证是否成功
     * @param $user_name
     * @return Exception|false|string
     */
    public function query($user_name)
    {
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->signq($timestamps[0], 'ai' . $user_name);
        $url = $this->urlQuery . "?ai=" . $user_name;
        $dataa = $this->curl_get($url, $this->appId, $this->bizId, $timestamps[0], $sign);
        try {
            $data3 = json_decode($dataa, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return Helper::response(500,'json解析失败');
        }
        if ($data3['errcode'] !== 0) {
            //认证失败
            return Helper::response(400,'实名认证失败');
        }
        $a = $data3['data']['result']['status'];
        if ($a === 0) {
            //认证成功修改用户
            return Helper::response(200,'实名认证成功',['pi' => $data3['data']['result']['pi']]);
        }
        if ($a === 1) {
            //认证中修改用户实名状态
            return Helper::response(201,'实名认证中请稍等');
        }
        //认证失败
        return Helper::response(400,'实名认证失败，错误代码：');
    }


    public function loginout($pi,$bt,$ct,$di,$user_name)
    {
            $data1 = array();
            $data1['no'] = 1;
            $data1['si'] = $user_name;
            $data1['bt'] = $bt;
            $data1['ot'] = time();
            $data1['ct'] = $ct;
            $data1['pi'] = $pi;
            $date = array('collections' => array($data1));
        $data2=$this->aesGcmEncrypt(json_encode($date, JSON_THROW_ON_ERROR));

        try {
            $data = json_encode(["data" => $data2], JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return Helper::response(500,'json生成失败');
        }
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->sign($timestamps[0], $data);
        $dataa = $this->curl_post($this->urlLoginOut, $this->appId, $this->bizId, $timestamps[0], $sign, $data);
        try {
            $data3 = json_decode($dataa, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return Helper::response(500,'json解析失败');
        }

        if ($data3['errcode'] !== 0) {
            //发送失败
            return Helper::response(400,'发送失败');
        }
        //发送成功
        return Helper::response(200,'发送成功');

    }


    /**
     * 基础CURL配置
     * @param $appId //游戏APPID
     * @param $bizId //游戏备案号
     * @param $timestamps //毫秒时间戳
     * @param $sign //加密后的字符串
     * @param $url //使用的url
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        return $curl;
    }

    /**
     * 根据配置发送报告到中宣部网络实名认证
     * @param string|null $data2
     * @return array
     * @throws JsonException
     */
    private function extracted(?string $data2): array
    {
        $data = json_encode(["data" => $data2], JSON_THROW_ON_ERROR);
        $timestamps = explode(".", microtime(true) * 1000);
        $sign = $this->sign($timestamps[0], $data);
        $dataa = $this->curl_post($this->urlCheck, $this->appId, $this->bizId, $timestamps[0], $sign, $data);
        return array($data, $dataa);
    }



    /**
     * 生成加密字符串
     * @param $string
     * @return string
     * @throws JsonException
     */
    private function aesGcmEncrypt($string): string
    {
        if (is_array($string)) {
            $string = json_encode($string, JSON_THROW_ON_ERROR);
        }
        //二进制key
        $key = hex2bin($this->secretKey);
        $cipher = "aes-128-gcm";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $encrypt = openssl_encrypt($string, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        return base64_encode(($iv . $encrypt . $tag));
    }


}