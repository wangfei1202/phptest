<?php
declare(strict_types=1);

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

/**
 * @Listener
 */
class ErrorExceptionHandler implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }
    
    public function process(object $event)
    {
        return set_error_handler(static function ($level, $message, $file = '', $line = 0): bool {
            if (error_reporting() & $level) {
                //发送钉钉
                ob_start();
                debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                $trace = htmlspecialchars(ob_get_clean());
                $msg   =  $message . "\n" . $file . ":" . $line . "\n" . $trace . "\n";
                echo $msg;
//                dingTalk($msg);
                //throw new ErrorException($message, 0, $level, $file, $line);
            }
            return true;
        });
    }
}
