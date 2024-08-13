<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;


class SupplierCode extends AbstractConstants
{
    //文件名称
    const FILE_LIST = [
        "",
        '营业执照复印件',
        '收款证明/委托收款证明书',
        '收款人身份证复印件',
        '法人身份证复印件',
        '采购框架合同',
        '品牌授权书',
        '廉洁承诺书',
        '补充协议'
    ];

    //供应商状态
    const SUPPLIER_STATUS = [
        "",
        '草稿',
        '驳回',
        '主管审批中',
        '',
        '',
        '财务审批',
        '正常',
        '修改中',
        '黑名单',
        '已删除'
    ];

    //供应商类型
    const SUPPLIER_TYPE = [
        "",
        'A类',
        'B类',
        'C类',
        'D类'
    ];

    //采购类型
    const PURCHASE_TYPE = [
        "",
        '网络采购',
        '线下采购',
        '海外代发'
    ];

    //仓库
    const WAREHOUSE_LIST = [
        "",
        '惠州02仓库',
        '惠州独立站01仓',
        '惠州03仓(中转)',
        '惠州04仓(耗材)'
    ];

    //票据类型
    const TICKET_TYPE = [
        "",
        '增值税发票',
        '普通发票'
    ];

    //结算时间
    const COMPUTING_DATE = [
        "",
        '按入库时间',
        '按销单时间',
        '按下单时间',
        '按收货时间'
    ];

    //退换货周期
    const RETURN_CYCLE = [
        "",
        '30天',
        '60天',
        '90天',
        '120天',
        '180天',
        '365天'
    ];

    //退换货折扣
    const RETURN_DISCOUNT = [
        "",
        '五折',
        '六折',
        '七折',
        '八折',
        '九折',
        '原价'
    ];

    //公司类型
    const COMPANY_TYPE = [
        "",
        '贸易商',
        '工厂',
        '工贸一体'
    ];

    const DATE_TYPE = [
        "",
        '创建时间',
        '修改时间',
    ];
    const CERTIFICATE_TYPE = [
        "",
        "3C(中国)",
        "CE(欧洲)",
        "RoHS(欧洲)",
        "GS(德国)",
        "FCC(美国)",
        "FDA(美国)",
        "UL(美国)",
        "PSE(日本)",
        "其他(自填)"
    ];
    const COOPERATION_STATUS = [
        "",
        "普通",
        "采购框架合同"
    ];
    //付款方式
    const PAY_TYPE = [
        "",
        '转账支付',
        '网络在线付款',
        '阿里账期',
    ];
    const RECEIPT_LEVEL = [
        "",
        '非常急',
        '紧急',
        '一般'
    ];
    const NOTE_TYPE = [
        "",
        "谈判",
        "采购",
        "拜访",
        "其他"
    ];

    //账期
    const PAY_LIST = [
        "",
        '现结',
        '滚动7天',
        '滚动10天',
        '滚动20天',
        '滚动30天',
        '滚动45天',
        '滚动60天',
        '滚动90天',
        '按固定结算日结30天',
        '按固定结算日结60天',
        '按固定结算日结90天',
        '周结',
        '半月结',
        '月结30天',
        '月结60天',
        '月结90天'
    ];

    //供应商级别
    const LEVEL_LIST = [
        "",
        '优质',
        '账期',
        '普通'
    ];

    //日志类型
    const LOG_LIST = [
        "",
        '供应商信息',
        '供应商资质',
        '合作条款'
    ];

    const CHECK = [
        "N",
        'Y'
    ];


    const CN_CHECK = [
        '否',
        '是'
    ];

    const PRICE_STATUS = [
        "",
        '草稿',
        '主管审核',
        '经理审核',
        '完成',
        '驳回'
    ];

    const CHANGE_STATUS = [
        "",
        '主管审批',
        '经理审批',
        '已修改',
        '驳回'
    ];

    const PURCHASE_STATUS = [
        "",
        '草稿',
        '待审核',
        '待发出',
        '预付待付款',
        '无快递单号',
        '缺货订单',
        '到付待付款',
        '待质检',
        '待完成',
        '可对账',
        '已完成',
        '回收站'
    ];
    const PRODUCT_STATUS = [
        "",
        -5 => "草稿",
        0 => "新增",
        1 => "审核通过",
        3 => "正常",
        4 => "停产",
        5 => "清库",
        6 => "起批量",
        7 => "锁定",
        8 => "暂时缺货"
    ];

    const PAYMENT_METHOD = [
        '',
        '按月结算',
        '按收货时间结算'
    ];

    const IMPORT_TYPE = [
        '',
        '供应商报价',
        '批量调价',
        '批量添加供应商',
        '修改产品交期'
    ];
}
