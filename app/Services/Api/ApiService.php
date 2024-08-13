<?php

namespace App\Services\Api;

use Hyperf\DbConnection\Db;
use Exception;

class ApiService
{
    /**
     * 扣减库存返回成本价
     * @param $params
     * @return array
     * @throws Exception
     */
    public function deductInventory($params)
    {
        $price = Db::table('package_order_record')->where('order_code',$params['order_code'])->value('order_price');
        if ($price) {
            return json_decode($price,true);
        }
        $response = [];
        foreach ($params['product_list'] as $item) {
            $inventory = Db::table('product_inventory')
                ->where(['sku' => $item['sku'], 'warehouse_id' => $params['warehouse_id']])
                ->where('good_num' ,'>=' , $item['qty'])
                ->value('good_num');

            if ($inventory) {
                $inventoryBatch = Db::table('product_inventory_in_detail')
                    ->where(['sku' => $item['sku'], 'warehouse_id' => $params['warehouse_id']])
                    ->where('good_num','>', 0)
                    ->get()->toArray();
                $qty = $item['qty'];
                $price = 0;
                foreach ($inventoryBatch as $batch) {
                    $availableQuantity = min($batch['good_num'], $qty);
                    $qty -= $availableQuantity;

                    $price += $availableQuantity * $batch['order_price'];

                    Db::table('product_inventory_in_detail')->where('id', $batch['id'])->update([
                        'out_num' => Db::raw("out_num + " . $availableQuantity),
                        'good_num' => Db::raw("good_num - " . $availableQuantity),
                    ]);

                    Db::table('product_inventory_out_detail')->insert([
                        'out_type' => 1,
                        'order_code' => $params['order_code'],
                        'sku' => $item['sku'],
                        'out_num' => $availableQuantity,
                        'warehouse_id' => $batch['warehouse_id'],
                        'inventory_in_id' => $batch['id'],
                        'order_price' => $batch['order_price'],
                        'note' => '',
                        'create_user' => 1, //管理员
                        'create_time' => date("Y-m-d H:i:s")
                    ]);

                    if ($qty <= 0) {
                        break;
                    }
                }
                $response[] = [
                    'sku' => $item['sku'],
                    'qty' => $item['qty'],
                    'price' => $price,
                ];
                Db::table('product_inventory')->where(['sku' => $item['sku'], 'warehouse_id' => $params['warehouse_id']])->update([
                    'good_num' => Db::raw("good_num - " . $item['qty']),
                    'history_out_num' => Db::raw("history_out_num + " . $item['qty']),
                ]);

            } else {
                throw new Exception($item['sku']." 没有可用库存");
            }
        }

        Db::table('package_order_record')->insert([
            'order_code' => $params['order_code'],
            'order_price' => json_encode($response,JSON_UNESCAPED_SLASHES),
            'create_date' => date("Y-m-d H:i:s")
        ]);

        return $response;
    }

    /**
     * 取消订单
     * @param $params
     * @throws Exception
     */
    public function cancelOrder($params)
    {
        $exists = Db::table('package_order_record')->where('order_code',$params['order_code'])->exists();
        if ($exists) {
            $outList = Db::table('product_inventory_out_detail')
                ->where('order_code',$params['order_code'])
                ->groupBy(['sku','inventory_in_id'])
                ->orderBy('id','desc')
                ->get()->toArray();
            foreach ($outList as $outInfo) {
                $inInfo = Db::table('product_inventory_in_detail')->where('id',$outInfo['inventory_in_id'])->first();
                Db::table('product_inventory_in_detail')->insert([
                    'in_type' => 1,
                    'sku' => $inInfo['sku'],
                    'order_sku_code' => $inInfo['order_sku_code'],
                    'warehouse_id' => $inInfo['warehouse_id'],
                    'purchase_sn' => $inInfo['purchase_sn'],
                    'platform_code' => $inInfo['platform_code'],
                    'order_price' => $inInfo['order_price'],
                    'shipping_amount' => $inInfo['shipping_amount'],
                    'supplier_id' => $inInfo['supplier_id'],
                    'order_num' => $inInfo['order_num'],
                    'in_num' => $outInfo['out_num'],
                    'out_num' => 0,
                    'good_num' => $outInfo['out_num'],
                    'note' => '订单取消，返回当初扣减数量',
                    'create_user' => 1, //管理员
                    'create_time' => date("Y-m-d H:i:s")
                ]);

                Db::table('product_inventory')->where(['sku' => $inInfo['sku'], 'warehouse_id' => $inInfo['warehouse_id']])->update([
                    'good_num' => Db::raw("good_num + " . $outInfo['out_num']),
                    'history_in_num' => Db::raw("history_in_num + " . $outInfo['out_num']),
                ]);
            }

            //删除包裹记录表种此订单信息
            Db::table('package_order_record')->where('order_code',$params['order_code'])->delete();
        } else {
            throw new Exception($params['order_code']." 没有生成过订单");
        }

    }
}