<?php

declare(strict_types=1);

namespace App\Controller;

use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;

class CommonController extends AbstractController
{

    /**
     * 获取所有店铺
     * @return array
     */
    public function getAllShopName()
    {
        try {
            $shops = Db::table('shop')
                ->where('IsEnable',1)
                ->selectRaw('Id id,ShopName shop_name')
                ->get()
                ->toArray();
            return $this->apiSuccess('操作成功', ['list'=>$shops]);
        } catch (\Exception $e) {
            return $this->apiFail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 获取所有运输方式+物流商
     * @return array
     */
    public function getLogisticProviderAndTransport()
    {
        try {
            $providers = Db::table('logistic_provider')->selectRaw('id,logistic_name')->orderBy('id')->get()->toArray();
            $transports = Db::table('transport')->selectRaw('id,transport_code,logistic_id')->orderBy('id')->get()->toArray();
            return $this->apiSuccess('操作成功', ['logistic_provider'=>$providers,'transport'=>$transports]);
        } catch (\Exception $e) {
            return $this->apiFail($e->getCode(), $e->getMessage());
        }
    }

}
