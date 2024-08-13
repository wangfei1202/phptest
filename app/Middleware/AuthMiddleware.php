<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Lib\RBAC;
use Firebase\JWT\Key;
use Hyperf\Context\Context;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\HttpServer\Router\Dispatched;
use Firebase\JWT\JWT;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var string Token
     */
    public $accessToken = '';


    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $route = $request->getUri()->getPath();
            // 判断用户权限
            $auth = make(RBAC::class);
            // 验证
            list($res, $pass) = $auth->isNotCheckRoute($route);
            if ($res === false) {
                throw new \Exception($auth->getError()['error'], $auth->getError()['code']);
            }
            if ($pass) {
                return $handler->handle($request);
            }

            // 是否携带Token
            if (empty($this->checkAccessToken($this->request->getHeaders()))) {
                throw new \Exception('请登陆', 2);
            }

            // 判断Token
            $tokenInfo = JWT::decode($this->accessToken, new Key(config('jwt_token_key'), 'HS256'));
            $tokenInfo = get_object_vars($tokenInfo);
            if (empty($tokenInfo['user_id']) || $tokenInfo['user_id'] < 1) {
                throw new \Exception('token不正确', 2);
            }
            // 判断 Token 是否伪造
            $userId = $tokenInfo['user_id'];
            $redisToken = ApplicationContext::getContainer()->get(Redis::class)->get('warehouse:token:' . strtolower(md5($this->accessToken)));
            if (empty($redisToken)) {
                throw new \Exception('登录已过期，请重新登录', 2);
            }
            $redisToken = json_decode($redisToken, true);
            if ($redisToken['user_id'] != $userId) {
                throw new \Exception('token不正确', 2);
            }

            // 验证
            $result = $auth->check($route, $userId);
            if ($result === false) {
                throw new \Exception($auth->getError()['error'], $auth->getError()['code']);
            }

            // 缓存用户信息


        } catch (\Throwable $e) {
            return $this->response->json(
                [
                    'ack' => false,
                    'code' => $e->getCode(),
                    'errCode' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'result' => [],
                    'total' => 0,
                ]
            );
        }

        return $handler->handle($request);
    }

    private function checkAccessToken(array $header = [])
    {
        $accessToken = $header['authorization'] ?? [];
        $accessToken = reset($accessToken);
        if (!empty($accessToken)) {
            $this->accessToken = $accessToken;
            return true;
        }

        return false;
    }


}
