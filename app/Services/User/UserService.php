<?php

namespace App\Services\User;


use App\Helper\Helper;
use App\Model\Role;
use App\Model\User;
use App\Services\Auth\AuthService;
use App\Services\Log\LogService;
use Firebase\JWT\JWT;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;

class UserService
{

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var LogService
     */
    protected $logService;


    /**
     * 用户列表规则
     * @return string[]
     */
    public function userListRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'user_name' => 'string',
            'cn_name' => 'string',
            't_status' => 'in:1,2',
        ];
    }

    /**
     * 用户列表
     * @return array
     */
    public function getAllUser()
    {
        return User::query()->selectRaw('Id id,UserName user_name,CnName cn_name,Mobile phone')->where(['is_delete' => User::UNDELETE])
            ->orderBy('Id')->get()->toArray();
    }

    /**
     * 用户列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $pageSize = $params['pageSize'] ?? 20;
        $page = $params['pageIndex'] ?? 1;
        $pageSize = (int)$pageSize;
        $page = (int)$page;
        $query = User::query();
        if (!empty($params['user_name'])) {
            $query->where('UserName', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['cn_name'])) {
            $query->where('CnName', 'like', '%' . $params['cn_name'] . '%');
        }
        if (!empty($params['t_status'])) {
            $query->where('Status', $params['t_status']);
        }
        $result = $query->selectRaw('Id id,UserName user_name,CnName cn_name,Mobile phone,Status t_status')->where(['IsDeleted' => User::UNDELETE])
            ->orderBy('Id', 'desc')->paginate($pageSize, ['*'], 'page', $page);
        $list = $result->items();
        return [
            'list' => $list ?? [],
            'total' => $result->total() ?? 0,
            'current_page' => $result->currentPage(),
            'page_size' => $result->perPage(),
            'page_total' => $result->lastPage(),
        ];
    }

    /**
     * 用户详情规则
     * @return string[]
     */
    public function userDetailRule()
    {
        return [
            'user_id' => 'required|integer',
        ];
    }

    /**
     * 获取用户详情
     * @param $params
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function getDetail($params)
    {
        $user = User::query()->selectRaw('Id id,UserName user_name,CnName cn_name,Mobile phone,Status t_status')->where(['Id' => $params['user_id']])->first();
        if (empty($user)) {
            throw new \Exception("数据不存在");
        }
        $user['roles'] = Db::table('user_role as a')
            ->selectRaw('b.Id role_id,b.Name name')
            ->join('role as b','b.Id','=','a.RoleId')
            ->where(['a.UserId'=>$user['id']])->get()->toArray();
        return $user;
    }



    /**
     * 登录规则
     * @return string[]
     */
    public function getLoginRule()
    {
        return [
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'string',
            'code_key' => 'string',
            'platform' => 'string'
        ];
    }



    /**
     * 获取登录ip
     * @return mixed|string
     */
    public function getLoginIp()
    {
        $res = $this->request->getServerParams();
        if (isset($res['http_client_ip'])) {
            return $res['http_client_ip'];
        } elseif (isset($res['http_x_real_ip'])) {
            return $res['http_x_real_ip'];
        } elseif (isset($res['http_x_forwarded_for'])) {
            //部分CDN会获取多层代理IP，所以转成数组取第一个值
            $arr = explode(',', $res['http_x_forwarded_for']);
            return $arr[0];
        } else {
            return $res['remote_addr'];
        }
    }

    /**
     * 登录
     * @param $params
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function login($params)
    {
        $username = $params['username'];
        $password = $params['password'];
        $codeKey = '';
        $params['platform'] = empty($params['platform'])?'web':$params['platform'];
        $redis = ApplicationContext::getContainer()->get(Redis::class);
        $isWeb = $params['platform'] == 'web' ? true:false;
        if($isWeb){
            $codeKey = $params['code_key'];
            $captcha = $params['captcha'];
            $codeKey = 'warehouse:login_code:' . $codeKey;
            $redisCode = $redis->get($codeKey);
            if (empty($redisCode)) {
                throw new \Exception('验证码已过期', 1);
            } elseif (strtolower($redisCode) != strtolower($captcha)) {
                throw new \Exception('验证码不正确', 1);
            }
        }
        $user = User::query()->selectRaw('Id user_id,UserName user_name,Mobile phone,CnName cn_name,Password password,Status t_status,PassEncryptionId')->where(['IsDeleted' => User::UNDELETE])
            ->where(function ($query) use ($username) {
                $query->orWhere(['UserName' => $username]);
                $query->orWhere(['Mobile' => $username]);
            })->first();
        if(empty($user)){
            throw new \Exception('登录账户不存在', 000402);
        }
        $password = $this->dealPass($password, $user['PassEncryptionId']);
        if ($password != $user['password']) {
            throw new \Exception('密码错误', 000402);
        }
        if ($user['t_status'] == User::STATUS_DISABLE) {
            throw new \Exception("登录账户{$username}已禁用", 000402);
        }
        unset($user['password'], $user['t_status'], $user['PassEncryptionId']);
        $user = $user->toArray();
        $expireTime = config('token_expire_time');
        $user['expire_time'] = time() + $expireTime;
        $token = JWT::encode($user, config('jwt_token_key'), 'HS256');
        //存数据库
        $time = time();
        $redis->set($this->getRedisTokenKey($token), json_encode($user), ['EX' => $expireTime]);
        Db::table('user_latest_login')->insert([
            'user_id' => $user['user_id'],
            'ip_address' => $this->getLoginIp(),
            'login_time' => date('Y-m-d H:i:s', $time),
            'token' => $token,
            'expire_time' => date('Y-m-d H:i:s', $time + $expireTime),
            'platform' =>$params['platform'] ?? ''
        ]);
        if($codeKey){
            $redis->del($codeKey);
        }
        return ['token' => $token];
    }

    /**
     * redis 存放token key
     * @param $token
     * @return string
     */
    private function getRedisTokenKey($token)
    {
        return 'warehouse:token:' . strtolower(md5($token));
    }


    /**
     * 处理密码
     * @param $pass
     * @param $salt
     * @return string
     */
    private function dealPass($pass, $salt)
    {
        $pass = strtoupper(md5($pass));
        $point = 1;
        for($i = 1;$i< 32; $i = $i+ $i){
            $index = strpos($pass,"{0}");
            if($index === false){
                $pass = substr($pass, 0, 1) . "{0}" . substr($pass, 1);
            }else{
                $pos = 2 * $i + $point * (2+mb_strlen(strval($point+1)));
                $pass = substr($pass, 0, $pos) . "{0}" . substr($pass, $pos);
                $point++;
            }
        }
        $pass = str_replace("{0}",$salt,$pass);
        return strtoupper(md5($pass));
    }


    /**
     * 退出登录
     * @return bool
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function logout()
    {
        $token = AuthService::getToken();
        ApplicationContext::getContainer()->get(Redis::class)->del($this->getRedisTokenKey($token));
        return true;
    }

    /**
     * 获取登录用户
     * @return array|mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \RedisException
     */
    public function getLoginUser()
    {
        $userId = AuthService::getUserInfo()['user_id'];
        return User::query()->where('Id', $userId)->selectRaw('Id user_id,UserName user_name,CnName cn_name,Mobile phone')->first();
    }
}