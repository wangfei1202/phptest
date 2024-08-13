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
namespace App\Exception\Handler;

use App\Exception\ValidationException;
use App\Lib\Traits\ResponseTrait;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    use ResponseTrait;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());
        $this->stopPropagation();
        $error = [
            'ack' => false,
            'code'    => $throwable->getCode(),
            'errCode' => $throwable->getCode(),
            'message' => $throwable->getMessage(),
            'result'  => [],
            'total'   => 0,
        ];
        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->withBody(new SwooleStream(json_encode($error)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
