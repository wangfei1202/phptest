<?php
/**
 * 辅助工具
 *
 */

namespace App\Traits;

trait HelpersTrait
{
    /**
     * 格式化结果集： 将结果转换为数组
     *
     * @param array $data
     *
     * @return mixed
     */
    public function formatResultToArray(array $data = [])
    {
        $data = json_decode(json_encode($data), true);
        return ($data ?? []) ?: [];
    }

    /**
     * 截取指定长长字符串，如果最后一个单词不完整，则去除整个单词
     *
     * @param $str
     * @param $len
     *
     * @return string
     */
    public function utf8StrcutByword($str, $len)
    {
        $str = trim($str);
        if (strlen($str) <= $len) {
            return $str;
        }
        $index = 0;
        $strNew = '';
        while ($index < $len) {
            $char = substr($str, $index, 1);
            $num = 0;
            switch (true) {
                case (ord($char) >= 252):
                    $num = 6;
                    break;
                case (ord($char) >= 248):
                    $num = 5;
                    break;
                case (ord($char) >= 240):
                    $num = 4;
                    break;
                case (ord($char) >= 224):
                    $num = 3;
                    break;
                case (ord($char) >= 192):
                    $num = 2;
                    break;
                default:
                    $num = 1;
                    break;
            }
            if ($index + $num > $len) {
                break;
            }
            $strNew .= substr($str, $index, $num);
            $index += $num;
        }
        $strNewSuffix = substr($str, $index, 1);
        $strNewLast = substr($strNew, -1);
        if ($strNewSuffix == ' ' || $strNewLast == ' ') {//后一个字符为空时,或者最后一个字符不也为空时，就为本身
            $strNew = trim($strNew);
        } else {//后一个字符不为空时并且，最后一个字符不也不为空时，去掉最后一个单词
            $strNewarr = explode(' ', $strNew);
            array_pop($strNewarr);
            $strNew = implode(' ', $strNewarr);
        }
        return $strNew;
    }

    /**
     * 单词格式化
     *
     * @param $wordStr
     *
     * @return string
     */
    protected function ucWords($wordStr)
    {
        $str = '';
        $words = explode('_', $wordStr);
        foreach ($words as $word) {
            $str .= ucwords($word);
        }

        return $str;
    }

    /**
     * 拆分单词
     *
     * @param $words
     *
     * @return array
     */
    protected function unUcWords($words)
    {
        $params = preg_split('/(?=[A-Z])/', $words);
        $prefix = array_shift($params);
        $suffix = join('', $params);

        return ['prefix' => $prefix, 'suffix' => $suffix, 'fields' => $params];
    }

    /**
     * 数组转字段
     *
     * @param $array
     *
     * @return string
     */
    protected function arrayToField($array)
    {
        foreach ($array as $key => $val) {
            $array[$key] = strtolower($val);
        }

        return join('_', $array);
    }

    /**
     * 判断response是否成功
     *
     * @param       $response
     * @param array $successCodes
     *
     * @return bool
     */
    public function isSuccessResponse($response, $successCodes = [200])
    {
        if (!is_array($response)) {
            $response = (array)$response;
        }

        $code = $response['code'] ?? -1;

        return in_array($code, $successCodes);
    }

    /**
     * 根据指定字符串均衡生成指定范围内的hash数字
     * @param $str
     * @param int $maxNum
     * @return int
     */
    public function createHashNumCode($str,$maxNum = 30) {
        $u = strtoupper($str);
        $h = sprintf("%u", crc32($u));
        $h1 = intval(fmod($h, $maxNum));
        if ($h1 == 0) {
            $h1 = $maxNum;
        }
        return $h1;
    }

    /**
     * 通过num获取hash code
     * @param $num
     * @param int $max
     * @return int
     */
    public function getHashNumCodeByNum($num,$max=30)
    {
        $num = intval($num);
        $num = $num <= 0 ? $max : $num;
        $hash = $num % $max;
        $hash == 0 && $hash = $max;
        return $hash;
    }

    /**
     * 对象转数组
     *
     * @param $obj
     *
     * @return array|bool
     */
    function objectToArray($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return false;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->objectToArray($v);
            }
        }

        return $obj;
    }

}
