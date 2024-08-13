<?php

namespace App\Services\Role;


use App\Model\Permission;
use App\Model\Role;
use App\Model\User;
use App\Services\Log\LogService;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;

class RoleService
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
     * 角色列表
     * @return array
     */
    public function getAllRole()
    {
        return Role::query()->selectRaw('Id role_id,Name name')
            ->orderBy('CreateTime')->get()->toArray();
    }

    /**
     * 角色列表规则
     * @return string[]
     */
    public function roleListRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'name' => 'string',
        ];
    }

    /**
     * 角色列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $pageSize = $params['pageSize'] ?? 20;
        $page = $params['pageIndex'] ?? 1;
        $pageSize = (int)$pageSize;
        $page = (int)$page;
        $query = Role::query();
        if (!empty($params['name'])) {
            $query->where('Name', 'like', '%' . $params['name'] . '%');
        }
        $result = $query->selectRaw('Id role_id,Name name,Description description')->orderBy('CreateTime', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
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
     * 角色列表
     * @return string[]
     */
    public function roleLogRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'role_id' => 'required|string', //角色id
        ];
    }

    /**
     * 日志列表
     * @param $params
     * @return array
     * @throws \\Exception
     */
    public function getLogList($params)
    {
        return $this->logService->getLogList($params, 'role', $params['role_id']);
    }

    /**
     * 设置角色权限规则
     * @return string[]
     */
    public function rolePermissionRule()
    {
        return [
            'permission_ids' => 'required|array', //权限id
            'role_id' => 'required|string', //角色id
        ];
    }

    /**
     * 批量设置角色权限规则
     * @return string[]
     */
    public function batchRolePermissionRule()
    {
        return [
            'permission_ids' => 'required|array', //权限id
            'role_ids' => 'required|array', //角色id
        ];
    }

    /**
     * 设置角色权限
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function setRolePermission($params)
    {
        return $this->rolePermissionSet([
            'permission_ids' => $params['permission_ids'],
            'role_ids' => [$params['role_id']],
        ], 0);
    }

    /**
     * 批量设置角色权限
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function batchSetRolePermission($params)
    {
        return $this->rolePermissionSet([
            'permission_ids' => $params['permission_ids'],
            'role_ids' => $params['role_ids'],
        ], 1);
    }

    /**
     * 修改角色权限
     * @param $params
     * @param $isBatch 1批量 0单个
     * @return bool
     * @throws \Exception
     */
    public function rolePermissionSet($params, $isBatch = 0)
    {
        $permissionIds = $params['permission_ids'] ?? [];
        $roleIds = $params['role_ids'] ?? [];
        if (empty($permissionIds) || empty($roleIds)) {
            throw new \Exception("参数为空");
        }
        $activeRoleIds = Role::query()->whereIn('Id', $roleIds)->pluck('Id')->toArray();
        $activePermissionIds = Permission::query()->whereIn('id', $permissionIds)->where(['t_status' => Permission::STATUS_ENABLE])->pluck('id')->toArray();
        if (empty($activePermissionIds) || empty($activeRoleIds)) {
            throw new \Exception("数据错误，请刷新后重试");
        }
        $oldRolePermissions = Db::table('role_permission')->selectRaw('role_id,permission_id')
            ->whereIn('role_id', $activeRoleIds)->get()->groupBy(['role_id'])->toArray();
        $inserts = [];
        if ($isBatch) {
            $operateName = '批量修改角色权限';
        } else {
            $operateName = '修改角色权限';
        }

        $operates = [];
        $permissionMap = Permission::query()->where(['t_status' => Permission::STATUS_ENABLE])->pluck('name', 'id')->toArray();
        foreach ($activeRoleIds as $roleId) {
            $oldPermissionIds = array_column($oldRolePermissions[$roleId] ?? [], 'permission_id');
            $addPermissionIds = array_diff($activePermissionIds, $oldPermissionIds);
            $delPermissionIds = array_diff($oldPermissionIds, $activePermissionIds);
            $operate = [];
            if (!empty($delPermissionIds)) {
                foreach ($delPermissionIds as $delPermissionId) {
                    $operate['delete'][$delPermissionId] = $permissionMap[$delPermissionId] ?? '';
                }
                Db::table('role_permission')->where('role_id', $roleId)->whereIn('permission_id', $delPermissionIds)->delete();
            }
            foreach ($addPermissionIds as $addPermissionId) {
                $operate['add'][$addPermissionId] = $permissionMap[$addPermissionId] ?? '';
                $inserts[] = [
                    'role_id' => $roleId,
                    'permission_id' => $addPermissionId,
                ];
            }
            $operates[] = [
                'operate_name' => $operateName,
                'object_id' => $roleId,
                'content' => $this->logService->buildContent($operateName, $operate)
            ];
        }
        if (!empty($inserts)) {
            Db::table('role_permission')->insert($inserts);
        }
        $this->logService->addLogs($operates, 'role');
        return true;
    }

    /**
     * 角色用户规则
     * @return string[]
     */
    public function roleUserRule()
    {
        return [
            'role_id' => 'required|string', //角色id
        ];
    }

    /**
     * 获取角色用户
     * @param $params
     * @return array|mixed[]
     */
    public function getRoleUser($params)
    {
        return Db::table('user_role as a')
            ->selectRaw('b.Id id,b.UserName user_name,b.CnName cn_name,b.Mobile phone')
            ->join('users as b','b.Id','=','a.UserId')
            ->where(['a.RoleId'=>$params['role_id']])
            ->orderBy('b.Id')
            ->get()->toArray();
    }

    /**
     * 角色详情规则
     * @return string[]
     */
    public function roleDetailRule()
    {
        return [
            'role_id' => 'required|string',
        ];
    }

    /**
     * 获取角色详情
     * @param $params
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function getDetail($params)
    {
        return Role::query()->selectRaw('id,name,description')->where(['id' => $params['role_id']])->first();
    }

    /**
     * 获取角色权限规则
     * @return string[]
     */
    public function getRolePermissionRule()
    {
        return [
            'role_id' => 'required|string',
        ];
    }

    /**
     * 获取角色详情
     * @param $params
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function getRolePermission($params)
    {
        $permissions = Db::table('role_permission as a')
            ->join('permission as b', 'a.permission_id', '=', 'b.id')
            ->where(['b.t_status' => 1])->where('a.role_id', $params['role_id'])->where('b.action', '<>', '')
            ->selectRaw('b.id,b.name,b.tag,b.action,b.pid')
            ->orderBy('id')
            ->get()->toArray();
        return $permissions;
    }
}