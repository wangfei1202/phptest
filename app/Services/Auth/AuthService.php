<?php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ServerRequestInterface;

class AuthService
{
    /**
     * 获取header token
     * @return false|mixed
     */
    public static function getToken(){
        $request = Context::get(ServerRequestInterface::class);
        $token = $request->getHeaders()['authorization'] ?? [];
        $token = reset($token);
        return $token;
    }
    /**获取token信息
     * @return array
     */
    public static function getTokenInfo(){
        $token = self::getToken();
        if(empty($token)){
            return [];
        }
        $tokenInfo = JWT::decode($token,new Key(config('jwt_token_key'),'HS256'));
        return get_object_vars($tokenInfo);
    }

    /**
     * 从协程上下文中获取 erpUserInfo
     * @return array
     */
    public static function getUserInfo()
    {
        $tokenInfo = self::getTokenInfo();
        if(empty($tokenInfo)){
            return [
                'user_id' => 0,
                'username' => '',
            ];
        }
        return [
            'user_id'  => $tokenInfo['user_id'] ?? 0,
            'username' => $tokenInfo['cn_name'] ?? '',
        ];
    }
    
}
