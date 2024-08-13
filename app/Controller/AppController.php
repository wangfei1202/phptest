<?php

declare(strict_types=1);

namespace App\Controller;

use App\Helper\Helper;
use App\Services\User\CaptchaService;
use App\Services\User\UserService;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

class AppController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function login()
    {
        try {
            $params = $this->request->all();
            $userService = $this->container->get(UserService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $userService->getLoginRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $result = $userService->login($params);
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail($e->getCode(), $e->getMessage());
        }
    }

    public function captcha()
    {
        $key = $this->request->input('code_key', '');
        $output = (new CaptchaService())->get($key);
        return $this->response
            ->withAddedHeader('content-type', 'image/jpeg')
            ->withBody(new SwooleStream($output));
    }


    public function logout()
    {
        try {
            $userService = $this->container->get(UserService::class);
            $result = $userService->logout();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail($e->getCode(), $e->getMessage());
        }
    }
}
