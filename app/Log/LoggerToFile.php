<?php
namespace App\Log;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerToFile
{
    /**
     * @param string $logName 创建的Channel的名字
     * @param string $logFile 文件名(路径)
     * @param int $logLv 消息等级
     * @param string $dateFormat 时间格式
     * @param string $outputFormat 输出格式
     * @return Logger logger实例
     */
    public function getLogger(
        string $logName = 'log',
        string $logFile = 'test.log',
        int $logLv = Logger::DEBUG,
        string $dateFormat = "Y-m-d H:i:s",
        string $outputFormat = "[%datetime%][%channel%][%level_name%] %message%||%context%||%extra%\n"
    ) :Logger {
        if (substr($logFile, 0, 1)=='/') {
            $path = $logFile;
        } else {
            $path = LOG_PATH."/".$logFile;
        }
        // 创建一个 Channel，参数 $logName 即为 Channel 的名字
        $logger = new Logger($logName);
        // 创建两个 Handler，对应变量 $stream 和 $fire
        $stream = new StreamHandler($path, $logLv);
        $fire = new FirePHPHandler();

        // 根据 时间格式 和 日志格式，创建一个 Formatter
        $formatter = new LineFormatter($outputFormat, $dateFormat);

        // 将 Formatter 设置到 Handler 里面
        $stream->setFormatter($formatter);

        // 将 Handler 推入到 Channel 的 Handler 队列内
        $logger->pushHandler($stream);
        $logger->pushHandler($fire);
        return $logger;
    }

    public function getLogger_common(
        string $logName = 'Common',
        string $logFile = 'Common/runtime.log'
    ){
        $params = [
            $logName,
            $logFile,
            Logger::DEBUG,
            "Y-m-d H:i:s",
            "[%datetime%][%level_name%] %message% |%context%\n",
        ];
        return $this->getLogger(...$params);
    }

    public function getLogger_consumer(
        string $logName = 'Consumer',
        string $logFile = 'Consumer/runtime.log',
        $index = null
    ){
        $indexFormat = isset($index)?"[$index]":"";
        $params = [
            $logName,
            $logFile,
            Logger::DEBUG,
            "Y-m-d H:i:s",
            "[%datetime%]{$indexFormat} %message% |%context%\n",
        ];
        return $this->getLogger(...$params);
    }


}
