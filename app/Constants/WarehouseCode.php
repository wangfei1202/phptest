<?php

declare(strict_types=1);

namespace App\Constants;
use Hyperf\Constants\AbstractConstants;


class WarehouseCode extends AbstractConstants
{
    const LOCATION_STATUS = [
        '',
        '空置',
        '占用'
    ];

    const LOCATION_TYPE = [
        '',
        '正常库位',
        '呆滞库位'
    ];

    const CHECK = [
        "",
        'Y',
        'N'
    ];

    const CN_CHECK = [
        "",
        '是',
        '否'
    ];

    const TRANSFER_STATUS = [
        "",
        "待审核",
        "在途中",
        "部分入库",
        "已入库",
        "作废",
        "审核不通过",
    ];

    const LOCATION_SIZE = [
        "",
        "迷你",
        "小",
        "中",
        "大",
        "加大",
        "加加大",
        "加加加大",
        "特大",
        "特特大",
        "托盘",
        "无限制尺寸",
        "零件盒",
    ];

    const PRODUCT_ACTIVITY = [
        "",
        "新款",
        "爆款",
        "平款",
        "旺款",
        "滞销款",
    ];

    const PACKAGE_STATUS = [
        "",
        "未配货",
        "已配货",
        "",
        "已打印",
        "已拣货",
        "已发货",
    ];

    const PACKAGE_ORDER_STATUS = [
        '',
        '正常',
        '待审核',
        '锁定'
    ];

    //调库配货状态
    const DELIVERY_TYPE = [
        "",
        "所有订单调库配货",
        "只针对于客户订单调库配货",
        "只针对于备库订单调库配货",
        "只针对于选中订单调库配货",
        "收货即发",
        "有货先发",
        "海外仓直采订单",
        "工厂直发"
    ];

    //调库配货状态
    const DELIVERY_STATUS = [
        "",
        "队列中",
        "进行中",
        "已完成",
        "操作失败",
        "已配货"
    ];
    const PRINT_TYPE = [
        "",
        "单品单件",
        "单品多件",
        "多品多件"
    ];
    //包裹类型
    const PACKAGE_TYPE = [
        "",
        "单品单件",
        "单品多件",
        "多品多件",
        "备货批次"
    ];
    const ERROR_TYPE = [
        "",
        "收货异常",
        "销单异常",
        "质检异常",
    ];

    const ERROR_STATUS = [
        "",
        "待上架",
        "待处理",
        "待下架",
        "待补充信息",
        "完成"
    ];

    const SOLUTION_TYPE = [
        "",
        "已关联",
        "销毁",
        "退回",
        "正常入库",
        "补充信息",
        "补寄"
    ];

    //拣货来源
    const PICK_SOURCE_TYPE = [
        "",
        "手动生批",
        "自动生批"
    ];

    //拣货方式
    const PICK_TYPE = [
        "",
        "拣货单",
        "终端机"
    ];
    //拣货任务状态(搜索下拉框)
    const PICK_SEARCH_TASK_STATUS = [
        "",
        "未领取/未打印",
        "已领取/已打印",
        "已完成",
        "已作废",
    ];
    //拣货任务状态(列表展示)
    const PICK_SHOW_TASK_STATUS = [
        "",
        "未打印",
        "已打印",
        "已完成",
        "已作废",
    ];
    //拣货备注
    const PICK_NOTE_TYPE = [
        "",
        "有备注",
        "无备注",
        "无条码产品",
        "缺货",
        "自行备注",
    ];
}
