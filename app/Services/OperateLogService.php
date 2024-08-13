<?php

namespace App\Services;

use App\Services\Auth\AuthService;
use Hyperf\Utils\Str;

class OperateLogService
{
    public static function saveLog($tableName, $tableId, $type, $content)
    {
        $userInfo = AuthService::getUserInfo();
        \App\Model\OperateLog::create([
            'table_name' => $tableName,
            'table_id' => $tableId,
            'operate_type' => $type,
            'content' => Str::limit($content, 999),
            'user_id' => $userInfo['user_id'] ?? 0,
            'username' => $userInfo['username']
        ]);
    }
}