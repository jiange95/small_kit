<?php

namespace JanGe\SmallKit;

class IdCard
{
    /**
     * 根据身份证获取当前年龄
     * GET CURRENT AGE BASED ON ID
     * @param $idCard
     * @return false|int|string
     */
    public static function age($idCard)
    {
        # 1.从身份证中获取出生日期
        $birth_Date = strtotime(self::birthday($idCard));

        # 2.格式化[出生日期]
        $Year = date('Y', $birth_Date);//yyyy
        $Month = date('m', $birth_Date);//mm
        $Day = date('d', $birth_Date);//dd

        # 3.格式化[当前日期]
        $time = time();
        $current_Y = date('Y', $time);//yyyy
        $current_M = date('m', $time);//mm
        $current_D = date('d', $time);//dd

        # 4.计算年龄()
        $age = $current_Y - $Year;//今年减去生日年
        if ($Month > $current_M || ($Month === $current_M && $Day > $current_D)) {//深层判断(日)
            $age--;//如果出生月大于当前月或出生月等于当前月但出生日大于当前日则减一岁
        }
        # 返回
        return $age;
    }

    /**
     * 验证身份证基本格式
     * VERIFY THE BASIC FORMAT OF THE ID CARD
     * @param $idCard
     * @return bool
     */
    public static function validateIdCard($idCard): bool
    {
        $City = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );

        // 身份证不是17+xX或18位数字或15位数字
        if (!preg_match('/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/', $idCard)) {
            return false;
        }
        // 身份证省份不在列表中
        if (!in_array(substr($idCard, 0, 2), $City, true)) {
            return false;
        }
        $vBirthday = self::birthday($idCard);
        // 生日验证,并且如果生日大于现在的时间，也报错
        $birthdayTime = strtotime($vBirthday);
        return !(date('Y-m-d', $birthdayTime) !== $vBirthday && $birthdayTime > time());
    }

    /**
     * 根据身份证返回生日
     * RETURN BIRTHDAY BASED ON ID
     * @param $idCard
     * @return string
     */
    public static function birthday($idCard): string
    {
        $idCard = preg_replace('/[Xx]$/i', 'a', $idCard);
        $length = strlen($idCard);
        if ($length === 18) {
            $vBirthday = substr($idCard, 6, 4) . '-' . substr($idCard, 10, 2) . '-' . substr($idCard, 12, 2);
        } else {
            $vBirthday = '19' . substr($idCard, 6, 2) . '-' . substr($idCard, 8, 2) . '-' . substr($idCard, 10, 2);
        }
        return $vBirthday;
    }

    /**
     * 根据身份证返回星座
     * Return to the constellation according to the ID card
     * @param $idCard
     * @return string
     */
    public static function getConstellationByIdCard($idCard): string
    {
        return self::getConstellation(strtotime(self::birthday($idCard)));
    }

    /**
     * 根据时间戳获取星座
     * GET CONSTELLATION BASED ON TIMESTAMP
     * @param $time
     * @return string
     */
    public static function getConstellation($time): string
    {
        $month = date('m', $time); //取出月份
        $day = date('d', $time); //取出日期
        switch ($month) {
            case "01":
                if ($day < 21) {
                    $res = '魔羯';
                } else {
                    $res = '水瓶';
                }
                break;
            case "02":
                if ($day < 20) {
                    $res = '水瓶';
                } else {
                    $res = '双魚';
                }
                break;
            case "03":
                if ($day < 21) {
                    $res = '双魚';
                } else {
                    $res = '白羊';
                }
                break;
            case "04":
                if ($day < 20) {
                    $res = '白羊';
                } else {
                    $res = '金牛';
                }
                break;
            case "05":
                if ($day < 21) {
                    $res = '金牛';
                } else {
                    $res = '双子';
                }
                break;
            case "06":
                if ($day < 22) {
                    $res = '双子';
                } else {
                    $res = '巨蟹';
                }
                break;
            case "07":
                if ($day < 23) {
                    $res = '巨蟹';
                } else {
                    $res = '狮子';
                }
                break;
            case "08":
                if ($day < 23) {
                    $res = '狮子';
                } else {
                    $res = '处女';
                }
                break;
            case "09":
                if ($day < 23) {
                    $res = '处女';
                } else {
                    $res = '天秤';
                }
                break;
            case "10":
                if ($day < 24) {
                    $res = '天秤';
                } else {
                    $res = '天蝎';
                }
                break;
            case "11":
                if ($day < 22) {
                    $res = '天蝎';
                } else {
                    $res = '射手';
                }
                break;
            case "12":
                if ($day < 22) {
                    $res = '射手';
                } else {
                    $res = '魔羯';
                }
                break;
            default:
                $res = '魔羯';
                break;
        }
        return $res;
    }

    /**
     * 根据年判断星座
     * JUDGING CONSTELLATION SIGNS BY YEAR
     * @param $birth
     * @return string
     */
    public static function birthed($birth): string
    {

        $m = (int)date("m", $birth);
        $d = (int)date("d", $birth);
        $dict = array('摩羯', '水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手');
        $zone = array(1222, 122, 222, 321, 421, 522, 622, 722, 822, 922, 1022, 1122, 1222);
        if ((100 * $m + $d) >= $zone[0] || (100 * $m + $d) < $zone[1]) {
            $i = 0;
        } else {
            for ($i = 1; $i < 12; $i++) {
                if ((100 * $m + $d) >= $zone[$i] && (100 * $m + $d) < $zone[$i + 1]) {
                    break;
                }
            }
        }

        return $dict[$i] . '座';
    }

    /**
     * 根据年判断天干地支
     * Judging the zodiac according to the year
     * @param $idCard
     * @param null $y
     * @return string
     */
    public static function zodiac($idCard, $y = null): string
    {
        if ($y === null) {
            $y = (int)date("Y", strtotime(self::birthday($idCard)));
        }
        $dict = array(array('甲', '乙', '丙', '丁', '戊', '己', '庚', '辛', '壬', '癸'), array('子', '丑', '寅', '卯', '辰', '巳', '午', '未', '申', '酉', '戌', '亥'));
        $i = $y - 1900 + 36;
        return $dict[0][($i % 10)] . $dict[1][($i % 12)];
    }

    /**
     * 根据年判断属相
     * Judging zodiac by year
     * @param $idCard
     * @param null $y
     * @return string
     */
    public static function signOfTheZodiac($idCard, $y = null): string
    {
        if ($y === null) {
            $y = (int)date("Y", strtotime(self::birthday($idCard)));
        }
        $dict = array('鼠', '牛', '虎', '兔', '龙', '蛇', '马', '羊', '猴', '鸡', '狗', '猪');
        return $dict[(($y - 4) % 12)];
    }
}