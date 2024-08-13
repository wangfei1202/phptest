<?php

declare(strict_types=1);

namespace App\Amqp\Consumer;

use App\Exception\ConsumeException;
use App\Exception\ConsumeRequeueException;
use App\Log\LoggerToFile;
use App\Traits\ConsoleTrait;
use Hyperf\Amqp\Producer;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\Di\Annotation\Inject;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * 消费者公共类
 */
class AdvtConsumer extends ConsumerMessage
{
    use ConsoleTrait;

    /**
     * @var Logger
     */
    protected $logger;
    protected $params;//传参
    protected $debug;//调试
    protected $context;//上下文


    public function consume($data): string
    {
        try {
            //初始化
            $this->init($data);
            //验证
            $this->validate();
            //处理
            $this->process();
            return Result::ACK;
        } catch (ConsumeRequeueException $e) {
            $this->logger->debug("ErrCRequeue:".$e->getMessage(), [$this->getQos()]);
            return Result::REQUEUE;
        } catch (ConsumeException $e) {
            $this->logger->debug("ErrC:".$e->getMessage(), [$this->getQos()]);
            return Result::NACK;
        } catch (\Throwable $e) {
            $this->logger->debug("Err:".$e->getMessage().$e->getTraceAsString(), [$this->getQos()]);
            return Result::NACK;
        }
    }


    protected function init($data)
    {
        $this->params = $data;
        //类名
        $className = explode('\\', get_class($this));
        $className = end($className);
        //日志
        $channelName = "Consumer";
        $logFile = "$channelName/{$className}.log";
        $this->logger = make(LoggerToFile::class)->getLogger_consumer($channelName, $logFile);

    }

    protected function validate()
    {

    }



    protected function process()
    {

    }
}
