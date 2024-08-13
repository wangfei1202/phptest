<?php
declare(strict_types=1);
use Hyperf\Crontab\Crontab;

return [
    // 是否开启定时任务
    'enable' => env('CRON_ENABLE', false),
    // 通过配置文件定义的定时任务
    'crontab' => []
];
