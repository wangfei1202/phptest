<?php

namespace App\Helper;
use Hyperf\DbConnection\Db;

class Helper
{
    /**
     * 生成树
     * @param $list
     * @param $pk
     * @param $pid
     * @param $child
     * @param $root
     * @return array
     */
    public static function generateTree($list, $pk = 'id', $pid = 'pid', $child = 'child', $root = 0)
    {
        $tree = array();
        $packData = array();
        foreach ($list as $data) {
            $packData[$data[$pk]] = $data;
        }
        foreach ($packData as $key => $val) {
            if ($val[$pid] == $root) {
                $tree[] = &$packData[$key];
            } else {
                //找到其父类,重点二
                $packData[$val[$pid]][$child][] = &$packData[$key];
            }
        }
        return $tree;
    }

    /**
     * 生成随机字符串
     * @param $len
     * @return string
     */
    public static function getRandomStr($len = 8)
    {
        $str = '';
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $len; $i++) {
            $str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * @param string $table
     * @param array $values
     * @param string $index
     * @return array
     */
    public static function updateBatch(string $table, array $values, string $index)
    {
        try {
            $final = array();
            $ids = array();
            if (!count($values)) {
                return [false, 'empty values'];
            }
            if (empty($index)) {
                return [false, 'empty index'];
            }
            foreach ($values as $val) {
                $ids[] = $val[$index];
                foreach (array_keys($val) as $field) {
                    // 排除索引字段
                    if ($field !== $index) {
                        if ($val[$field] === null) {
                            $final[$field][] = "WHEN `" . $index . "` = '" . $val[$index] . "' THEN null ";
                        } else {
                            $final[$field][] = "WHEN `" . $index . "` = '" . $val[$index] . "' THEN '" . $val[$field] . "' ";
                        }
                    }
                }
            }
            $cases = '';
            foreach ($final as $k => $v) {
                $cases .= $k . ' = (CASE ' . implode("\n", $v) . "\n" . 'ELSE ' . $k . ' END), ';
            }
            $state = false;
            if (!empty($ids)) {
                $query = 'UPDATE ' . $table . ' SET ' . substr($cases, 0, -2) . ' WHERE ' . $index . ' IN(' . implode(',', $ids) . ')';
                $state = Db::statement($query);
            }
            return [$state, ''];
        } catch (\Exception $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * 生成标识
     * @param $identity
     * @param $sku
     * @return string
     */
    public static function generateBatchCode($identity, $sku = '')
    {
        return $identity . $sku . date("YmdHis") . str_pad(rand(1, 999), 3, "0", STR_PAD_LEFT);
    }
}