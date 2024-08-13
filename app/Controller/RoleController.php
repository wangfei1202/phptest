<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\Role\RoleService;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

class RoleController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function getAllRole()
    {
        try {
            $permissionService = $this->container->get(RoleService::class);
            $result = $permissionService->getAllRole();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function index()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->roleListRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $result = $roleService->getList($params);
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }


    public function getLogList()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->roleLogRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->getLogList($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function setRolePermission()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->rolePermissionRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->setRolePermission($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function batchSetRolePermission()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->batchRolePermissionRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->batchSetRolePermission($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getRoleUser()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->roleUserRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->getRoleUser($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getDetail()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->roleDetailRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->getDetail($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getRolePermission()
    {
        try {
            $params = $this->request->all();
            $roleService = $this->container->get(RoleService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $roleService->getRolePermissionRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $roleService->getRolePermission($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }
}
