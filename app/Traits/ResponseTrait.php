<?php
/**
 * 通用 trait, 适用于全局
 *
 */

namespace App\Traits;

/**
 * Trait BaseTrait
 * @package App\Traits
 */
trait ResponseTrait
{
    /**
     * 成功
     *
     * @param string $msg
     * @param array  $data
     *
     * @return array
     */
    public function success($msg = '', $data = [])
    {
        $response = [
            'code'    => 200,
            'message' => $msg,
            'data'    => $data
        ];

        return $response;
    }

    /**
     * 异常
     *
     * @param int    $code
     * @param string $msg
     *
     * @return array
     */
    public function fail($code = 0, $msg = '')
    {
        $code = ($code != 200) ? $code : 0;

        return ['code' => $code, 'message' => $msg];
    }

    /**
     * 错误返回
     *
     * @param int    $code
     * @param string $msg
     * @param array  $error
     *
     * @return array
     */
    public function error($code = 0, $msg = '', $error = [])
    {
        $code = ($code != 200) ? $code : 0;

        return ['code' => $code, 'message' => $msg, 'error' => $error];
    }

    /**
     * api 成功返回
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function apiSuccess($msg = '',$data = [])
    {
        return ['ack' => true, 'message' => $msg, 'code' => 200, 'errCode' => '', 'result' => $data];
    }

    /**
     * api异常返回
     * @param int $code
     * @param string $msg
     * @param array $data
     * @return array
     */
    public function apiFail($code = 0, $msg = '',$data = [])
    {
        $code = ($code != 200) ? $code : 0;
        return ['ack' => false, 'message' => $msg, 'code' => intval($code), 'errCode' => 402, 'data' => $data];
    }
}
