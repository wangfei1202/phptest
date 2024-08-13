<?php

declare(strict_types=1);

namespace App\Lib\Traits;

/**
 * 响应返回结果格式化
 * Trait ResponseTrait
 * @package App\Lib\Traits
 */
trait ResponseTrait
{
    /**
     * 成功参数
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    public function success(string $message = '', array $data = []): array
    {
        return [
            'success' => true,
            'code'    => 200,
            'message' => $message,
            'data'    => $data,
        ];
    }

    /**
     * 失败参数
     * @param int    $code
     * @param string $message
     * @param array  $data
     *
     * @return array
     */
    public function error(int $code = 0, string $message = '', array $data = []): array
    {
        return [
            'success' => false,
            'code'    => $code,
            'message' => $message,
            'data' => $data,
        ];
    }
}
