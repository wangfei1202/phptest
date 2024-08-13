<?php

namespace App\Services;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Exception;
use Hyperf\Di\Annotation\Inject;
class ValidatorService
{
    /**
     * @inject ()
     * @var ValidatorFactoryInterface
     */
    protected $validatorFactory;
    protected $supplierRules = [
        'supplier_name' => 'required',
        'province' => 'required',
        'city' => 'required',
        'address' => 'required',
        'purchase_type' => 'required',
        'homepage' => 'required',
        'kaifa_user' => 'required',
        'purchase_user' => 'required',
        'linkman' => 'required',
        'phone' => 'required',
        'main_category' => 'required',
        'pay_type' => 'required',
        'bill' => 'required'
    ];
    protected $supplierMessages = [
        'supplier_name.required' => '供应商名称必填',
        'province.required' => '省份必填',
        'city.required' => '城市必填',
        'address.required' => '地址必填',
        'purchase_type.required' => '采购类型必填',
        'homepage.required' => '主页必填',
        'kaifa_user.required' => '开发人员必填',
        'purchase_user.required' => '采购人员必填',
        'linkman.required' => '联系人必填',
        'phone.required' => '手机号必填',
        'main_category.required' => '主营品类必填',
        'pay_type.required' => '付款方式必填',
        'bill.required' => '账期必填'
    ];

    public function validateSupplier(array $params)
    {
        $validator = $this->validatorFactory->make($params, $this->supplierRules, $this->supplierMessages);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    protected $changePriceRules = [
        'supplier_id'=>'required',
        'product_sku'=>'required',
        'product_price'=>'required',
        'update_price'=>'required',
    ];

    protected $changePriceMessage = [
        'supplier_id.required'=>'供应商必填',
        'product_sku.required'=>'产品SKU必填',
        'product_price.required'=>'产品价格不填',
        'update_price.required'=>'调整后价格必填',
    ];
    public function validateChangePrice(array $params)
    {
        $validator = $this->validatorFactory->make($params, $this->changePriceRules, $this->changePriceMessage);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    protected $supplierProductRules = [
        'supplier_id' => 'required',
        'product_sku' => 'required',
        'price' => 'required',
        'wholesale_num' => 'required',
    ];

    protected $supplierProductMessage = [
        'supplier_id.required' => '供应商必填',
        'product_sku.required' => '产品SKU必填',
        'price.required' => '产品价格必填',
        'wholesale_num.required' => '起批量必填',
    ];
    public function validateSupplierProduct(array $params)
    {
        $validator = $this->validatorFactory->make($params, $this->supplierProductRules, $this->supplierProductMessage);
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

}