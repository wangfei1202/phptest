<?php

namespace App\Services\Permission;


use App\Helper\Helper;
use App\Model\Permission;
use App\Services\Log\LogService;
use avadim\FastExcelWriter\Exception\Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;

class PermissionService
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
    private $logService;

    /**
     * 权限列表
     * @return array
     */
    public function getAllPermission()
    {
        $list = Permission::query()->selectRaw('id,name,pid')->where(['t_status' => Permission::STATUS_ENABLE])
            ->orderBy('pid')->orderBy('id')->get()->toArray();
        return Helper::generateTree($list);
    }


    /**
     * 权限列表规则
     * @return string[]
     */
    public function permissionListRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'name' => 'string',
            'permission_action' => 'string',
        ];
    }

    /**
     * 权限列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $pageSize = $params['pageSize'] ?? 20;
        $page = $params['pageIndex'] ?? 1;
        $pageSize = (int)$pageSize;
        $page = (int)$page;
        $query = Permission::query();
        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (!empty($params['permission_action'])) {
            $query->where('action', 'like', '%' . $params['permission_action'] . '%');
        }
        $result = $query->selectRaw('id,name,action,tag,pid')->where(['t_status' => Permission::STATUS_ENABLE])
            ->orderBy('id', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
        $list = $result->items();
        $pids = array_filter(array_column($list, 'pid'));
        if (!empty($pids)) {
            $names = Permission::query()->whereIn('id', $pids)->pluck('name', 'id')->toArray();
            foreach ($list as &$value) {
                if (!empty($value['pid'])) {
                    $value['parent_name'] = $names[$value['pid']] ?? '';
                } else {
                    $value['parent_name'] = '';
                }
            }
        }
        return [
            'list' => $list ?? [],
            'total' => $result->total() ?? 0,
            'current_page' => $result->currentPage(),
            'page_size' => $result->perPage(),
            'page_total' => $result->lastPage(),
        ];
    }


    /**
     * 添加权限规则
     * @return string[]
     */
    public function permissionAddRule()
    {
        return [
            'name' => 'required|string|max:150',
            'permission_action' => 'string|max:255',
            'tag' => 'string|max:255',
            'pid' => 'integer'
        ];
    }

    /**
     * 添加权限
     * @param $params
     * @return bool
     */
    public function addPermission($params)
    {
        $data = [
            'name' => $params['name'] ?? '',
            'action' => $params['permission_action'] ?? '',
            'tag' => $params['tag'] ?? '',
            'pid' => $params['pid'] ?? 0,
        ];
        if (!empty($data['action'])) {
            $has = Permission::query()->where(['action' => $data['action']]);
        } else {
            $has = Permission::query()->where(['pid' => $data['pid'], 'name' => $data['name']]);
        }
        $has = $has->where(['t_status' => Permission::STATUS_ENABLE])->first();
        if ($has) {
            throw new Exception('权限已存在');
        }
        $re = Permission::insertGetId($data);
        if (!$re) {
            throw new Exception('权限添加失败');
        }
        $operateName = '添加权限';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['add' => $data])
        ], 'permission', $re);
        return true;
    }

    /**
     * 编辑权限规则
     * @return string[]
     */
    public function permissionEditRule()
    {
        return [
            'permission_id' => 'required|integer',
            'name' => 'required|string|max:150',
            'permission_action' => 'string|max:255',
            'tag' => 'string|max:255',
            'pid' => 'integer'
        ];
    }

    /**
     * 修改权限
     * @param $params
     * @return bool
     */
    public function editPermission($params)
    {
        $data = [
            'name' => $params['name'] ?? '',
            'action' => $params['permission_action'] ?? '',
            'tag' => $params['tag'] ?? '',
            'pid' => $params['pid'] ?? 0,
        ];
        if (!empty($data['action'])) {
            $has = Permission::query()->where(['action' => $data['action']]);
        } else {
            $has = Permission::query()->where(['pid' => $data['pid'], 'name' => $data['name']]);
        }
        $has = $has->where(['t_status' => Permission::STATUS_ENABLE])->first();
        if ($has && $has['id'] != $params['permission_id']) {
            throw new Exception('权限已存在');
        }
        Permission::where(['id' => $params['permission_id']])->update($data);
        $operateName = '编辑权限';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['edit' => $data])
        ], 'permission', $params['permission_id']);
        return true;
    }

    /**
     * 删除权限规则
     * @return string[]
     */
    public function permissionDeleteRule()
    {
        return [
            'permission_id' => 'required|integer',
        ];
    }

    /**
     * 删除权限
     * @param $params
     * @return bool
     */
    public function deletePermission($params)
    {
        $permission = Permission::where(['id' => $params['permission_id']])->first();
        if (empty($permission) || $permission['t_status'] != 1) {
            throw new Exception('权限已删除');
        }
        $permissionChild = Permission::where(['pid' => $params['permission_id']])->where(['t_status' => Permission::STATUS_ENABLE])->first();
        if ($permissionChild) {
            throw new Exception('有下级子权限不能被删除');
        }
        $re = Permission::where(['id' => $params['permission_id']])->update(['t_status' => Permission::STATUS_DISABLE]);
        if (!$re) {
            throw new Exception('权限删除失败');
        }
        $operateName = '删除权限';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['delete' => $permission['name']])
        ], 'permission', $params['permission_id']);
        return true;
    }

    /**
     * 权限列表
     * @return string[]
     */
    public function permissionLogRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'permission_id' => 'required|integer', //权限id
        ];
    }

    /**
     * 日志列表
     * @param $params
     * @return array
     * @throws \Exception
     */
    public function getLogList($params)
    {
        return $this->logService->getLogList($params, 'permission', $params['permission_id']);
    }

    /**
     * 角色详情规则
     * @return string[]
     */
    public function permissionDetailRule()
    {
        return [
            'permission_id' => 'required|integer',
        ];
    }

    /**
     * 获取角色详情
     * @param $params
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function getDetail($params)
    {
        $permission = Permission::query()->selectRaw('id,name,tag,pid,action')->where(['id' => $params['permission_id']])->first();
        if (!$permission) {
            throw new \Exception('数据不存在');
        }
        if ($permission['pid'] == 0) {
            $permission['parent_name'] = '';
        } else {
            $parent = Permission::query()->selectRaw('name')->where(['id' => $permission['pid']])->first();
            $permission['parent_name'] = $parent['name'] ?? '';
        }
        return $permission;
    }
}