<?php
namespace JanGe\SmallKit;

use Exception;
use JanGe\SmallKit\IdCard;
use JanGe\SmallKit\IP;

class Helper
{
    /**
     * 返回类
     * @param int $code
     * @param $msg
     * @param string $data
     * @return Exception|false|string
     */
    public static function response(int $code,string $msg, $data='')
    {
        try {
            return json_encode(['code' => $code, 'msg' => $msg, $data], JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            return $e;
        }
    }

}