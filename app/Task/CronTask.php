<?php
namespace App\Task;

use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Carbon\Carbon;
use App\Services\Deliver\DeliverPackageService;
/**
 * @Crontab(name="CronTask", rule="* * * * *", callback="execute", enable="isEnable", memo="自动调库配货定时生成任务")
 */
class CronTask
{
    public function isEnable(): bool
    {
        return true;
    }
    /**
     * @throws \Exception
     */
    public function execute()
    {
        $packageConfig = Db::table('package_config')
            ->where('status',1)
            ->get()
            ->toArray();
        foreach ($packageConfig as $config){
            $dateConfig = json_decode($config['condition'],true);
            if(empty($dateConfig)){
                continue;
            }
            //按照配置的json生成时间规则
            foreach ($dateConfig as $k => $dateItem){
                $currentDayOfWeek = date('N'); // 获取当前是周几，1表示周一，2表示周二，以此类推
                $currentTime = date('H:i'); // 获取当前时间，格式为小时:分钟
                //计算当前配置的星期几
                $configDayOfWeek = $k + 1;
                $exclude = array("00:00");
                $configDate = array_diff($dateItem['date'],$exclude); //排除掉00:00
                if ($dateItem['checked'] == true && in_array($currentTime, $configDate) && $currentDayOfWeek == $configDayOfWeek) {
                    $config['config_type'] = 2;
                    make(DeliverPackageService::class)->generateTask($config);
                }
            }
        }
    }
}