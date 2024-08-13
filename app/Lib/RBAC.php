<?php

declare(strict_types=1);

namespace App\Lib;

use App\Model\Permission;
use App\Model\Role;
use App\Model\User;
use Hyperf\DbConnection\Db;

/**
 * RBAC 扩展类
 * Class RBAC
 * @package App\Lib
 */
class RBAC
{

    /**
     * @var string 错误信息
     */
    private $error = '';

    /**
     * @var int 错误代码
     */
    public $code = 0;

    /**
     * 判断是否是无需校验的路由
     * @param $route
     * @return array|bool[]|false[]
     */
    public function isNotCheckRoute($route = null){
        try {
            if (empty($route)) {
                $this->code = 40002;
                $this->error = '路由地址或用户名不能为空';
                return [false,false];
            }
            // 处理路由规则
            $route = $this->formatRoute($route);
            $notCheck = config('manager.no_check_route');
            if(!empty($notCheck)){
                array_walk($notCheck, function (&$value) {
                    $value = $this->formatRoute($value);
                });
            }
            if (in_array($route, $notCheck)) {
                return [true,true];
            }
            return [true,false];
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return [false,false];
        }
    }


    /**
     * 判断是否有权限
     * @param null $route
     * @param int $uid
     * @return bool
     */
    public function check($route = null, $uid = 0)
    {
        try {
            if (empty($route) || empty($uid)) {
                $this->code = 40002;
                $this->error = '路由地址或用户名不能为空';
                return false;
            }
            // 处理路由规则
            $route = $this->formatRoute($route);
            // 获取用户信息
            $userInfo = $this->getUserInfo($uid);
            if ($userInfo === false) {
                return false;
            }

            // 管理员拥有所有权限
            $administrator = config('manager.administrator');
            if ($userInfo['UserName'] == $administrator) {
                return true;
            }
            // 获取权限白名单
            $notCheck = config('manager.no_auth_route');
            if (!empty($notCheck)) {
                array_walk($notCheck, function (&$value) {
                    $value = $this->formatRoute($value);
                });
                if (in_array($route, $notCheck)) {
                    return true;
                }
            }
            // 获取用户权限
            $menus = $this->getUserPermission($userInfo['Id']);
            if (empty($menus)) {
                return false;
            }

            $levelMenu = array_reduce($menus, function ($result, $item) {
                if (!empty($item['action'])) {
                    $item['action'] = $this->formatRoute($item['action']);
                    $result[$item['action']] = $item['name'];
                }
                return $result;
            });
            if (!array_key_exists($route, $levelMenu)) {
                $this->code  = 40001;
                $this->error = '没有操作权限';
                return false;
            }
            return true;
        } catch (\Exception $e) {
            $this->code = 40001;
            $this->error = '验证权限失败';
            return false;
        }
    }

    /**
     * 格式化路由
     * @param $route
     * @return string
     */
    public function formatRoute($route)
    {
        $explodeRoute = explode('/', $route);
        $routeParse   = array_map(function ($name) {
            $temp = preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY);
            return strtolower(implode('-', $temp));
        }, $explodeRoute);

        return implode('/', $routeParse);
    }

    /**
     * 获取用户数据
     * @param $uid
     * @return false|\Hyperf\Database\Model\Builder|\Hyperf\Database\Model\Model|object
     */
    public function getUserInfo($uid)
    {
        $info = User::query()->where('Id',$uid)->where('IsDeleted',User::UNDELETE)->first();

        if (empty($info)) {
            $this->code  = 20004;
            $this->error = '未找到用户信息';
            return false;
        }elseif ($info['Status'] == User::STATUS_DISABLE){
            $this->code  = 20004;
            $this->error = '用户已被禁用';
            return false;
        }

        return $info;
    }

    /**
     * 获取用户角色权限
     * @param $roleId
     * @return array|bool
     */
    public function getUserPermission($userId)
    {
        $permissionIds = Db::table('role_permission as a')
            ->selectRaw('a.permission_id')
            ->join('user_role as c','a.role_id','=','c.RoleId')
            ->where(['c.UserId'=>$userId])->pluck('permission_id')->toArray();

       if(empty($permissionIds)){
            $this->code  = 40004;
            $this->error = '未找到用户权限信息';
            return false;
        }
        $permissionList = Permission::query()->whereIn('id',array_unique($permissionIds))->where('action','<>','')
            ->selectRaw('action,name')->get()->toArray();;
        if (empty($permissionList)) {
            $this->code  = 40004;
            $this->error = '未找到用户权限信息';
            return false;
        }

        return $permissionList;
    }

    /**
     * 获取错误信息
     * @return array
     */
    public function getError()
    {
        return ['code' => $this->code, 'error' => $this->error];
    }

}