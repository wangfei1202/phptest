<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\Permission\PermissionService;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

class PermissionController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function getAllPermission()
    {
        try {
            $permissionService = $this->container->get(PermissionService::class);
            $result = $permissionService->getAllPermission();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function index()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionListRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $result = $permissionService->getList($params);
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function add()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionAddRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $permissionService->addPermission($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function edit()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionEditRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $permissionService->editPermission($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionDeleteRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $permissionService->deletePermission($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getLogList()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionLogRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $permissionService->getLogList($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getDetail()
    {
        try {
            $params = $this->request->all();
            $permissionService = $this->container->get(PermissionService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $permissionService->permissionDetailRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $permissionService->getDetail($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }
}
