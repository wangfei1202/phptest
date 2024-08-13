<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\Menu\MenuService;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Di\Annotation\Inject;

class MenuController extends AbstractController
{

    /**
     * @Inject
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    public function getAllMenu()
    {
        try {

            $menuService = $this->container->get(MenuService::class);
            $result = $menuService->getAllMenu();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getUserMenu()
    {
        try {

            $menuService = $this->container->get(MenuService::class);
            $result = $menuService->getUserMenu();
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function index()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuListRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $result = $menuService->getList($params);
            return $this->apiSuccess('操作成功', $result);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function add()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuAddRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $menuService->addmenu($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function edit()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuEditRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $menuService->editMenu($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function delete()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuDeleteRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $menuService->deleteMenu($params);
            return $this->apiSuccess('操作成功');
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getLogList()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuLogRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $menuService->getLogList($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getDetail()
    {
        try {
            $params = $this->request->all();
            $menuService = $this->container->get(MenuService::class);
            $params = array_filter($params);
            $validator = $this->validationFactory->make(
                $params,
                $menuService->menuDetailRule()
            );
            if ($validator->fails()) {
                $errorMessage = $validator->errors()->all();
                return $this->apiFail('000402', implode('<br>', $errorMessage));
            }
            $re = $menuService->getDetail($params);
            return $this->apiSuccess('操作成功', $re);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

    public function getVersion()
    {
        try {
            $menuService = $this->container->get(MenuService::class);
            $list = $menuService->getVersion();
            return $this->apiSuccess('操作成功', $list);
        } catch (\Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }
}
