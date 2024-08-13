<?php

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Server\ServerFactory;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ServerRequestInterface;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

/**
 * 容器实例
 */
if (!function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}

/**
 * redis 客户端实例
 */
if (!function_exists('redis')) {
    function redis()
    {
        return container()->get(Redis::class);
    }
}

/**
 * server 实例 基于 swoole server
 */
if (!function_exists('server')) {
    function server()
    {
        return container()->get(ServerFactory::class)->getServer()->getServer();
    }
}

/**
 * websocket frame 实例
 */
if (!function_exists('frame')) {
    function frame()
    {
        return container()->get(Frame::class);
    }
}

/**
 * websocket 实例
 */
if (!function_exists('websocket')) {
    function websocket()
    {
        return container()->get(WebSocketServer::class);
    }
}

/**
 * 缓存实例 简单的缓存
 */
if (!function_exists('cache')) {
    function cache()
    {
        return container()->get(Psr\SimpleCache\CacheInterface::class);
    }
}

/**
 * 控制台日志
 */
if (!function_exists('stdLog')) {
    function stdLog()
    {
        return container()->get(StdoutLoggerInterface::class);
    }
}

/**
 * 文件日志
 */
if (!function_exists('logger')) {
    function logger()
    {
        return container()->get(LoggerFactory::class)->make();
    }
}

if (!function_exists('request')) {
    function request()
    {
        return container()->get(ServerRequestInterface::class);
    }
}

if (!function_exists('response')) {
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}
if (!function_exists('dingTalk')) {
    function dingTalk($msg, $title = '', $con = 'default')
    {
        if (empty($title)) {
            $title = '来自【Caigou】的PHP报错信息';
        }
        $message = sprintf("%s\n\nHOST: %s\n\nDATE: %s\n\nMSG: %s", $title, env('HOST'), date('Y-m-d H:i:s'), $msg);
        sendOpenApiMsg($message);
    }
}


if (!function_exists('sendOpenApiMsg')) {
    function sendOpenApiMsg($msg)
    {
        $postData = [
            'platform' => 'walmart',
            'method' => 'advt.sendmessage',
            'access_token' => env('OPEN_API_DING_ACCESS_TOKEN'),
            'content' => $msg
        ];
        // 请求数据
        container()->get(\App\Lib\Utils\RequestClient::class)->setPostType('form_params')->post(env('OPEN_API_URI'), $postData, [], 0);
        
    }
}
if (!function_exists('arrayDiffRecursive')) {
    function arrayDiffRecursive($array1, $array2)
    {
        $diff = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!array_key_exists($key, $array2) || !is_array($array2[$key])) {
                    $diff[$key] = $value;
                } else {
                    $recursive_diff = arrayDiffRecursive($value, $array2[$key]);
                    if (!empty($recursive_diff)) {
                        $diff[$key] = $recursive_diff;
                    }
                }
            } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                $diff[$key] = $value;
            }
        }
        
        return $diff;
    }
}

if (!function_exists('compareArrays')) {
    /**
     * 如果是顺序索引直接比较值
     */
    function compareArrays($arr1, $arr2)
    {
        $diff = array();
        foreach ($arr1 as $key => $value) {
            if (is_array($value) && is_array($arr2[$key])) {
                if (array_values($value) === $value && array_values($arr2[$key]) === $arr2[$key]) {
                    if ($value !== $arr2[$key]) {
                        $diff[$key] = $value;
                    }
                } else {
                    $recursiveDiff = compareArrays($value, $arr2[$key]);
                    if (!empty($recursiveDiff)) {
                        $diff[$key] = $recursiveDiff;
                    }
                }
            } else {
                if (!isset($arr2[$key]) || $value !== $arr2[$key]) {
                    $diff[$key] = $value;
                }
            }
        }
        return $diff;
    }
}