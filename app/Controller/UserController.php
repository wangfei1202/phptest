<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\User\UserService;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

class UserController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function getAllUser()
    {
        try {
            $userService = $this->container->get(UserService::class);
            $result = $userService->getAllUser();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function index()
    {
        try {
            $params = $this->request->all();
            $userService = $this->container->get(UserService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $userService->userListRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $result = $userService->getList($params);
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getDetail()
    {
        try {
            $params = $this->request->all();
            $userService = $this->container->get(UserService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $userService->userDetailRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $userService->getDetail($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getLoginUser()
    {
        try {
            $userService = $this->container->get(UserService::class);
            $re = $userService->getLoginUser();
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }
}
