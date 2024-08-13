<?php

namespace App\Services\Menu;


use App\Helper\Helper;
use App\Model\Menu;
use App\Model\Permission;
use App\Services\Auth\AuthService;
use App\Services\Log\LogService;
use avadim\FastExcelWriter\Exception\Exception;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;

class MenuService
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
     * 返回菜单目录
     * @return mixed[]
     */
    public function getAllMenu()
    {
        return Menu::query()->selectRaw('id,name')
            ->where(['t_status' => Menu::STATUS_ENABLE])->where(['is_hidden' => 0])->where(['is_cate' => 1])
            ->orderBy('sort_no')->orderBy('id')->get()->toArray();
    }

    /**
     * 获取用户菜单
     * @return array
     */
    public function getUserMenu()
    {
        $tokenInfo = AuthService::getTokenInfo();
        if (empty($tokenInfo)) {
            return [];
        }
        $permissionIds = [];
        $permissions = Permission::query()->where(['t_status' => Permission::STATUS_ENABLE]);
        if ($tokenInfo['user_name'] != config('manager.administrator')) {
            $permissionIds = Db::table('role_permission as a')
                ->selectRaw('a.permission_id')
                ->join('user_role as c','a.role_id','=','c.RoleId')
                ->where(['c.UserId'=>$tokenInfo['user_id']])->pluck('permission_id')->toArray();
            $permissionIds = array_flip(array_flip($permissionIds));
            if(empty($permissionIds)){
                $permissions = [];
            }else{
                $permissions = $permissions->whereIn('id', $permissionIds)->where('action', '<>', '')
                    ->selectRaw('id,name,tag,action,pid')->orderBy('id')->get()->toArray();
            }
        }else{
            $permissions = $permissions->where('action', '<>', '')->selectRaw('id,name,tag,action,pid')->orderBy('id')->get()->toArray();
        }
        $pIds = array_column($permissions, 'pid');
        $menuIdentities = Menu::query()->whereIn('permission_id', $pIds)->pluck('identity', 'permission_id')->toArray();
        foreach ($permissions as &$permission) {
            if (isset($menuIdentities[$permission['pid']])) {
                $permission['menu_identity'] = $menuIdentities[$permission['pid']];
            } else {
                $permission['menu_identity'] = '';
            }
        }

        if ($tokenInfo['user_name'] != config('manager.administrator')) {
            $permissionIds = array_unique(array_merge($permissionIds, $pIds));
            $menus = $this->getQueryMenus()->whereIn('permission_id', $permissionIds)->get()->toArray();
            $menuPids = array_unique(array_filter(array_column($menus, 'pid')));
            $menus = array_column($menus, null, 'id');
            while (!empty($menuPids)) {
                $levelMenus = $this->getQueryMenus()->whereIn('id', $menuPids)->get()->toArray();
                $menuPids = array_unique(array_filter(array_column($levelMenus, 'pid')));
                $menus = $menus + array_column($levelMenus, null, 'id');
            }
        } else {
            $menus = $this->getQueryMenus()->get()->toArray();
        }

        return ['menus' => Helper::generateTree($menus), 'permissions' => $permissions];
    }

    /**
     * 获取菜单查询sql
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder
     */
    private function getQueryMenus()
    {
        return Menu::query()->selectRaw('id,name,identity,permission_id,path,icon,pid,is_cate')
            ->where(['t_status' => Menu::STATUS_ENABLE])->where(['is_hidden' => 0])
            ->orderBy('sort_no')->orderBy('id');
    }


    /**
     * 菜单列表规则
     * @return string[]
     */
    public function menuListRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'name' => 'string',
        ];
    }

    /**
     * 菜单列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $pageSize = $params['pageSize'] ?? 20;
        $page = $params['pageIndex'] ?? 1;
        $pageSize = (int)$pageSize;
        $page = (int)$page;
        $query = Menu::query();
        if (!empty($params['name'])) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }
        $result = $query->selectRaw('id,identity,name,path,is_hidden,icon,pid,is_cate,sort_no,permission_id')->where(['t_status' => Menu::STATUS_ENABLE])
            ->orderBy('id', 'desc')
            ->paginate($pageSize, ['*'], 'page', $page);
        $list = $result->items();
        if (!empty($list)) {
            $pids = array_filter(array_column($list, 'pid'));
            $permissionIds = array_filter(array_column($list, 'permission_id'));
            $names = Menu::query()->whereIn('id', $pids)->pluck('name', 'id')->toArray();
            $permissions = Permission::query()->whereIn('id', $permissionIds)->pluck('name', 'id')->toArray();
            foreach ($list as &$value) {
                if (!empty($value['pid'])) {
                    $value['parent_name'] = $names[$value['pid']] ?? '';
                } else {
                    $value['parent_name'] = '';
                }
                if (!empty($value['permission_id'])) {
                    $value['permission_name'] = $permissions[$value['permission_id']] ?? '';
                } else {
                    $value['permission_name'] = '';
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
     * 添加菜单规则
     * @return string[]
     */
    public function menuAddRule()
    {
        return [
            'name' => 'required|string|max:150',
            'identity' => 'string|max:255',
            'path' => 'string|max:255',
            'icon' => 'string|max:255',
            'pid' => 'integer',
            'is_hidden' => 'integer',
            'is_cate' => 'integer',
            'sort_no' => 'integer',
            'permission_id' => 'integer',
        ];
    }

    /**
     * 添加菜单
     * @param $params
     * @return bool
     */
    public function addmenu($params)
    {
        $data = [
            'name' => $params['name'] ?? '',
            'identity' => $params['identity'] ?? '',
            'path' => $params['path'] ?? '',
            'icon' => $params['icon'] ?? '',
            'pid' => $params['pid'] ?? 0,
            'is_hidden' => $params['is_hidden'] ?? 0,
            'is_cate' => $params['is_cate'] ?? 0,
            'sort_no' => $params['sort_no'] ?? 0,
            'permission_id' => $params['permission_id'] ?? 0,
        ];
        $has = Menu::query()->where(['name' => $data['name']])->where(['t_status' => Menu::STATUS_ENABLE])->first();
        if ($has) {
            throw new Exception('菜单已存在');
        }
        if ($data['permission_id'] > 0) {
            $permission = Menu::query()->where(['permission_id' => $data['permission_id']])->where(['t_status' => Menu::STATUS_ENABLE])->first();
            if ($permission) {
                throw new Exception('权限已绑定其他菜单');
            }
        }

        $re = Menu::insertGetId($data);
        if (!$re) {
            throw new Exception('菜单添加失败');
        }
        $operateName = '添加菜单';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['add' => $data])
        ], 'menu', $re);
        return true;
    }

    /**
     * 编辑菜单规则
     * @return string[]
     */
    public function menuEditRule()
    {
        return [
            'menu_id' => 'required|integer',
            'name' => 'required|string|max:150',
            'identity' => 'string|max:255',
            'path' => 'string|max:255',
            'icon' => 'string|max:255',
            'pid' => 'integer',
            'is_hidden' => 'integer',
            'is_cate' => 'integer',
            'sort_no' => 'integer',
            'permission_id' => 'integer',
        ];
    }

    /**
     * 修改菜单
     * @param $params
     * @return bool
     */
    public function editMenu($params)
    {
        $data = [
            'name' => $params['name'] ?? '',
            'identity' => $params['identity'] ?? '',
            'path' => $params['path'] ?? '',
            'icon' => $params['icon'] ?? '',
            'pid' => $params['pid'] ?? 0,
            'is_hidden' => $params['is_hidden'] ?? 0,
            'is_cate' => $params['is_cate'] ?? 0,
            'sort_no' => $params['sort_no'] ?? 0,
            'permission_id' => $params['permission_id'] ?? 0
        ];
        $has = Menu::query()->where(['name' => $data['name']])->where(['t_status' => Menu::STATUS_ENABLE])->first();
        if ($has && $has['id'] != $params['menu_id']) {
            throw new Exception('菜单已存在');
        }
        if ($data['permission_id'] > 0) {
            $permission = Menu::query()->where(['permission_id' => $data['permission_id']])->where(['t_status' => Menu::STATUS_ENABLE])->first();
            if ($permission && $permission['id'] != $params['menu_id']) {
                throw new Exception('权限已绑定其他菜单');
            }
        }
        Menu::where(['id' => $params['menu_id']])->update($data);
        $operateName = '编辑菜单';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['edit' => $data])
        ], 'menu', $params['menu_id']);
        return true;
    }

    /**
     * 删除菜单规则
     * @return string[]
     */
    public function menuDeleteRule()
    {
        return [
            'menu_id' => 'required|integer',
        ];
    }

    /**
     * 删除菜单
     * @param $params
     * @return bool
     */
    public function deleteMenu($params)
    {
        $menu = Menu::where(['id' => $params['menu_id']])->first();
        if (empty($menu) || $menu['t_status'] != 1) {
            throw new Exception('菜单已删除');
        }
        $menuChild = Menu::where(['pid' => $params['menu_id']])->where(['t_status' => Menu::STATUS_ENABLE])->first();
        if ($menuChild) {
            throw new Exception('有下级子菜单不能被删除');
        }
        $re = Menu::where(['id' => $params['menu_id']])->update(['t_status' => Menu::STATUS_DISABLE]);
        if (!$re) {
            throw new Exception('菜单删除失败');
        }
        $operateName = '删除菜单';
        $this->logService->addLog([
            'operate_name' => $operateName,
            'content' => $this->logService->buildContent($operateName, ['delete' => $menu['name']])
        ], 'menu', $params['menu_id']);
        return true;
    }

    /**
     * 菜单列表
     * @return string[]
     */
    public function menuLogRule()
    {
        return [
            'pageIndex' => 'required|integer', //当前页
            'pageSize' => 'required|integer', //当前页
            'menu_id' => 'required|integer', //菜单id
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
        return $this->logService->getLogList($params, 'menu', $params['menu_id']);
    }

    /**
     * 角色详情规则
     * @return string[]
     */
    public function menuDetailRule()
    {
        return [
            'menu_id' => 'required|integer',
        ];
    }

    /**
     * 获取角色详情
     * @param $params
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|\Hyperf\Database\Query\Builder|object|null
     */
    public function getDetail($params)
    {
        $menu = Menu::query()->selectRaw('id,identity,name,path,is_hidden,icon,pid,is_cate,sort_no,permission_id')->where(['id' => $params['menu_id']])->first();
        if (!$menu) {
            throw new \Exception('数据不存在');
        }
        if ($menu['pid'] == 0) {
            $menu['parent_name'] = '';
        } else {
            $parent = Menu::query()->selectRaw('name')->where(['id' => $menu['pid']])->first();
            $menu['parent_name'] = $parent['name'] ?? '';
        }
        if ($menu['permission_id'] == 0) {
            $menu['permission_name'] = '';
        } else {
            $parent = Permission::query()->selectRaw('name')->where(['id' => $menu['permission_id']])->first();
            $menu['permission_name'] = $parent['name'] ?? '';
        }
        return $menu;
    }

    public function getVersion()
    {
        return Db::table('print_version_auto_update')->orderBy('create_time','desc')->first();
    }
}