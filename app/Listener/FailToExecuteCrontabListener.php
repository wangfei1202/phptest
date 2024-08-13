<?php

declare(strict_types=1);

namespace App\Listener;

use Hyperf\Crontab\Event\FailToExecute;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use App\Log\LoggerToFile;
use App\Traits\ConsoleTrait;

#[Listener]
class FailToExecuteCrontabListener implements ListenerInterface
{
    use ConsoleTrait;
    public function listen(): array
    {
        return [
            FailToExecute::class,
        ];
    }

    /**
     *
     * @param FailToExecute $event
     */
    public function process(object $event)
    {
        $cronName = $event->crontab->getName();
        $message = $event->throwable->getMessage();
        $this->printNotice($cronName);
        $this->printNotice($message);
        make(LoggerToFile::class)->getLogger_consumer($cronName, $message);
    }
}
