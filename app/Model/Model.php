<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    public function setSiteCodeAttribute($value)
    {
        return $this->attributes['site_code'] = $value == 'GB' ? 'UK' : $value;
    }
    public static $status = [
        0 => '新增',
        1 => '正常',
        2 => '禁用'
    ];

    public static $delete = [
        0=>'正常',
        1=>'删除'
    ];
    const UNDELETE = 0; // 为1表示正常
    const DELETED = 1; // 为1表示删除
    const STATUS_NEW = 0; // 为0表示新增
    const STATUS_ENABLE = 1; // 为1表示正常
    const STATUS_DISABLE = 2; // 为2表示禁用
    public function getFormatState($key = 0, $enum = array(), $default = '')
    {
        return array_key_exists($key, $enum) ? $enum[$key] : $default;
    }
}
