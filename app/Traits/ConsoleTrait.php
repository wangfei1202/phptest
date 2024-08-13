<?php
/**
 * Console Trait
 *
 */

namespace App\Traits;

trait ConsoleTrait
{
    /**
     * Print notice message.
     *
     * @param string $message
     *
     */
    protected function printNotice(string $message)
    {
        self::printTip('NOTICE', $message);
    }

    /**
     * Print success message.
     *
     * @param string $message
     *
     */
    protected function printSuccess(string $message)
    {
        self::printTip('SUCCESS', $message);
    }

    /**
     * Print warning message.
     *
     * @param string $message
     *
     */
    protected function printWarning(string $message)
    {
        self::printTip('WARNING', $message);
    }

    /**
     * Print error message.
     *
     * @param string $message
     *
     */
    protected function printError(string $message)
    {
        self::printTip('ERROR', $message);
    }

    /**
     * Print tip message.
     *
     * @param string $prefix
     * @param string $message
     *
     */
    protected function printTip(string $prefix, string $message)
    {
        echo sprintf("[%s] [%s] %s", date_format(date_create(), 'Y-m-d H:i:s.u'), $prefix, $message) . PHP_EOL;
    }

    /**
     * Print line.
     *
     * @param string $char
     * @param int    $number
     *
     */
    protected function printLine(string $char = "-", int $number = 150)
    {
        echo str_repeat($char, $number) . PHP_EOL;
    }

    /**
     * Print begin message.
     *
     * @param mixed ...$args
     *
     */
    protected function printBegin(...$args)
    {
        $char   = $args['char'] ?? '-';
        $number = $args['number'] ?? 150;

        $this->printLine($char, $number);
        echo str_repeat(' ', ($number - strlen($this->consoleName)) / 2) . $this->consoleName . PHP_EOL;
        $this->printLine($char, $number);
    }

    /**
     * Print end message.
     *
     * @param mixed ...$args
     *
     */
    protected function printEnd(...$args)
    {
        $char   = $args['char'] ?? '-';
        $number = $args['number'] ?? 150;

        $this->printLine($char, $number);
    }

    /**
     * @param $stime
     * @param $etime
     * @param $msg
     * @return string
     */
    public  function timeCost($stime, $etime, $msg)
    {
        return sprintf("执行%s,共耗时%s 秒" . PHP_EOL, $msg, round($etime - $stime, 3));
    }
}