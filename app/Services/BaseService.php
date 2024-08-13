<?php

declare(strict_types=1);

namespace App\Services;

use App\Amqp\Producer\SaiheProducer;
use App\Lib\Utils\RequestClient;
use Hyperf\Amqp\Producer;
use Hyperf\DbConnection\Db;
use Hyperf\Redis\Redis;
use Hyperf\Utils\ApplicationContext;
use Exception;
use Obs\ObsClient;
use App\Services\Auth\AuthService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;


class BaseService
{
    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    //根据表示获取当天流水号
    public function getCustomOrder($str): string
    {
        if (empty($str)) {
            throw new Exception('生成编号标识不能为空');
        }
        $container = ApplicationContext::getContainer();
        $date = date("Ymd");
        $key = strtoupper($str) . $date;
        $exists = $container->get(Redis::class)->exists($key);
        $autoID = $container->get(Redis::class)->incr($key);
        if (!$exists) {
            $container->get(Redis::class)->expire($key, 86400);
        }
        $num = sprintf("%04d", $autoID);
        return strtoupper($str) . $date . $num;
    }

    /**
     * 导出csv 返回文件路径
     * @param $columns
     * @param $data
     * @param $fileName
     * @param array $mark
     * @return string
     */
    public function export($columns, $data, $fileName, array $mark = []): string
    {
        set_time_limit(0);
        $cellNum = count($columns);
        $spreadsheet = new Spreadsheet();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->getStyle('A1:' . $cellName[$cellNum - 1] . '1')->getFont()->setBold(true);
        $i = 0;
        foreach ($columns as $cell) {
            $sheet->setCellValue($cellName[$i] . '1', $cell);
            $i++;
        }
        $baseRow = 2; //数据从N-1行开始往下输出 这里是避免头信息被覆盖
        foreach ($data as $value) {
            $i = 0;
            foreach ($columns as $k => $v) {
                $cellValue = $mark[$k][$value[$k]] ?? $value[$k];
                $sheet->setCellValue($cellName[$i] . $baseRow, $cellValue);
                $i++;
            }
            $baseRow++;
        }
        $writer = new Csv($spreadsheet);
        $basePath = BASE_PATH . '/storage/excel/'. date('Ymd').'/';
        if(!is_dir($basePath)){
            mkdir(iconv("UTF-8", "GBK", $basePath),0777,true);
        }
        $filename = $fileName . time() . '.csv';
        $writer->save($basePath . $filename);
        return $this->uploadObs($basePath . $filename);
    }

    public function uploadObs($path)
    {
        $suffix = explode(".",$path)[1];
        $filePathUrl = 'uploads/' . date('Ymd') . '/' . time() .'.'. $suffix;
        // 创建ObsClient实例
        $obsClient = new ObsClient ( [
            'key' => env('OBS_ACCESS_ID'),
            'secret' => env('OBS_ACCESS_SECRET'),
            'endpoint' => env('OBS_ENDPOINT')
        ] );
        $resp = $obsClient->putObject([
            'Bucket' => env('OBS_BUCKET'),
            'Key' => $filePathUrl,
            'Body' => fopen($path, 'rb')
        ]);
        $data = $this->objectToArray($resp);
        $url = "";
        foreach ($data as $item) {
            $url = $item["ObjectURL"];
        }
        return $url;
    }
    public function userPluck($id = [])
    {
        $obj = Db::table('users');
        if ($id) {
            $obj->whereIn('Id',$id);
        }
        return $obj->pluck('CnName','Id')->toArray();
    }

    public function getNewArr($arr)
    {
        $response = [];
        $i = 0;
        foreach ($arr as $key => $value) {
            if ($value) {
                $response[$i]['key'] = $key ;
                $response[$i]['value'] = $value;
                $i ++;
            }
        }
        return $response;
    }

    public function getCategory()
    {
        return Db::table('category')
            ->selectRaw("CONCAT(CategoryName,'【',CategoryCode,'】') as CategoryName ,CategoryId")
            ->where('level',1)->pluck('CategoryName','CategoryId')->toArray();
    }

    public function getWarehouse()
    {
        return Db::table('warehouse')
            ->where('warehouse_type',1)
            ->where('is_enable',1)
            ->pluck('warehouse_name','id')
            ->toArray();
    }

    public function getWarehouseType()
    {
        return Db::table('warehouse_type')
            ->pluck('name','id')
            ->toArray();
    }

    public function shopList()
    {
        return Db::table('shop')
            ->where('IsEnable',1)
            ->pluck('ShopName','Id')
            ->toArray();
    }

    public function transportList()
    {
        return Db::table('transport')
            ->where('status',1)
            ->pluck('transport_code','id')
            ->toArray();
    }



    public function objectToArray($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return false;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->objectToArray($v);
            }
        }
        return $obj;
    }

    /**
     * 多到、到错、不良、色差、与图不符、其他类型 少到
     * @return string
     */
    public function getOrderType($type)
    {
        $tmpMap  = [
             1    => "多到",
             2    => "到错",
             3    => "不良",
             4    => "色差",
             5    => "与图不符",
             6    => "其他类型",
             7    => "少到",
            -1    => "正常"
        ];
        return $tmpMap[$type] ?? '无关联';
    }

    /**
     * @param $packageType
     * @return string
     */
    public static function getPackageType($packageType)
    {
        $tmpMap  = [
            0    => "所有订单调库配货",
            1    => "只针对于客户订单调库配货",
            2    => "只针对于备库订单调库配货",
            3    => "只针对于选中订单调库配货",
            4    => "收货即发",
            5    => "有货先发",
            6    => "海外仓直采订单",
            7    => "工厂直发"
        ];
        return $tmpMap[$packageType] ?? '只针对于客户订单调库配货';
    }

    /**
     * @param $configStatus
     * @return string
     */
    public function getProductInformation($sku)
    {
        $tmpMap  = [
            1    => "队列中",
            2    => "进行中",
            3    => "已完成",
            4    => "操作失败",
            5    => "已配货",
        ];
        return $tmpMap[$configStatus] ?? '队列中';
    }


    /**
     * 获取仓储系统产品信息图片
     * product_check表包含cbp产品
     * $returnArr = true 返回一维数组 产品信息
     * @param $sku
     * @param false $returnArr
     * @return array|false|mixed
     */
    public function getWarehouseProductInformation($sku,$returnOne = false){
        $response = [];
        if(empty($sku)){
            return [];
        }

        $table = "product_check";
        $imgTable = "product_check_picture";

        //兼容一个sku或多个sku
        if(!is_array($sku)){
            $sku = [$sku];
        }
        //产品信息
        $productInfo = Db::table($table)->whereIn('sku',$sku)->get()->toArray();
        $pictureInfos = Db::table($imgTable)
            ->whereIn('sku',array_column($productInfo,'sku'))
            ->orderBy('id','asc')
            ->get()
            ->groupBy(['sku'])
            ->toArray();
        foreach ($productInfo as $item) {
            $response[$item['sku']] = $item;
            //产品图片
            $pictureInfo = $pictureInfos[$item['sku']] ?? [];
            foreach ($pictureInfo as $k => $value) {
                if(empty($value['picture_url'])){
                    unset($pictureInfo[$k]);
                    continue;
                }
                if (strpos($value['picture_url'], 'http') !== false) {
                    $pictureInfo[$k]['picture_url'] = str_replace('http://', 'https://', $value['picture_url']);
                } else {
                    $pictureInfo[$k]['picture_url'] = env('OBS_HOST').'/'. ltrim($value['picture_url'], '/');
                }
            }
            $pictureList =  array_column($pictureInfo,'picture_url');//全部图片
            $response[$item['sku']]['product_img'] = reset($pictureList);
            $response[$item['sku']]['product_img_list'] = $pictureList;
        }
        (count($response) == 1 && $returnOne == true) && $response = reset($response);
        return $response;
    }

    /**
     * 获取产品基础信息和图片/单个SKU
     * @param $sku
     * @return array
     */
    public function getOneProductInformation($sku)
    {
        if (mb_substr($sku, 0, 3) == 'CBP') {
            $table = "combination_product";
            $imgTable = "combination_product_picture";
        } else {
            $table = "product";
            $imgTable = "product_picture";
        }
        //产品信息
        $productInfo = Db::table($table)
            ->selectRaw('Color,Size,ParentSku,Sku,SystemSku,SaiHeSku,AuditingDateTime,CostPrice,PictureUrl,
            ProductStatus,CnName,EnName,Star_Level,Developer,IsDelete,Color,Size,Weight,GrossWeight,
            PckWeight,Length,Width,Height,PckLength,PckWidth,PckHeight,ProductPackingList,CustomAttributes')
            ->where('Sku',$sku)->first();

        $pictureInfo = Db::table($imgTable)->selectRaw('ThumbnailUrl,SystemSku,ProductPictureType')
            ->where('SystemSku',$productInfo['SystemSku'] ?? "")
            ->orderBy('ProductPictureType')
            ->get()->toArray();
        if(empty($productInfo)){
           return [];
        }
        $mainThumbnailUrl = "";
        foreach ($pictureInfo as $k => $value) {
            if(empty($value['ThumbnailUrl'])){
                unset($pictureInfo[$k]);
                continue;
            }
            if (strpos($value['ThumbnailUrl'], 'http') !== false) {
                $pictureInfo[$k]['ThumbnailUrl'] = str_replace('http://', 'https://', $value['ThumbnailUrl']);
            } else {
                $pictureInfo[$k]['ThumbnailUrl'] = 'https://elenxs1.obs.cn-north-4.myhuaweicloud.com/' . ltrim($value['ThumbnailUrl'], '/');
            }
            if($value['ProductPictureType'] == 0){
                $mainThumbnailUrl = $pictureInfo[$k]['ThumbnailUrl'];
            }
        }
        $productInfo['product_img'] = $mainThumbnailUrl;//首图
        $productInfo['product_img_list'] = array_column($pictureInfo,'ThumbnailUrl');//全部图片
        return $productInfo;
    }

    public function getPrintConfig($printName): array
    {
        $printConfig = Db::table('print_rdlc_configuration')->where('print_name', $printName)->first();
        return [
            'TemplateName' => $printConfig['print_cn_name'],
            'PrintRdlcName' => $printConfig['print_rdlc_name'],
            'PrintClassName' => $printConfig['print_class_name'],
            'PrintMethodName' => $printConfig['print_method_name'],
            'Width' => (int)$printConfig['print_width'],
            'Height' => (int)$printConfig['print_height'],
        ];
    }

    /**
     * 入库操作类型映射
     * @return string[]
     */
    public function productActTypeMap(){
        return [
            1=>'收货',
            2=>'收货作废',
            3=>'销单',
            4=>'质检',
            5=>'质检作废',
            6=>'打回质检',
            7=>'质检不良品退回',
            8=>'理货',
            9=>'入库扫描',
            10=>'入库',
        ];
    }
    /**
     * 入库日志
     * @param $data
     * @param $actType
     * @param $msg
     * @return void
     */
    public function addProductActionLog($data,$actType,$msg){
        $status = 1;
        //收货作废 质检作废 打回质检
        if($actType == 2 || $actType ==5 || $actType == 6){
            $status = 0;
        }
        $log = [
            'purchase_sn' => $data['purchase_sn'] ?? '',
            'product_sku' => $data['product_sku'] ?? '',
            'platform_code' => $data['platform_code'] ?? '',
            'inspect_code' => $data['inspect_code'] ?? '', //销单号
            'tracking_num' => $data['tracking_num'] ?? '',
            'create_user' => AuthService::getUserInfo()['user_id'],
            'act_type' => $actType,
            'message' => $msg,
            'create_date' => time(),
            'quality' => $data['quality'] ?? 0,
            'normal_quality' => $data['normal_quality'] ?? 0,
            'error_quality' => $data['error_quality'] ?? 0,
            'sku_quality' => $data['sku_quality'] ?? 0,
            't_status' => $status,
        ];
        Db::table('product_action_log')->insert($log);
    }

    /**
     * 根据包裹id返回包裹面单
     * @param $packageId
     * @return array
     */
    public function printPackageLabel($packageId): array
    {
        $packageInfo = Db::table('package_order')->where('id', $packageId)->first();
        return [
            "TemplateName" => "",
            "PrintRdlcName" => "",
            "PrintClassName" => "",
            "PrintMethodName" => "",
            "IsSizePrint" => true,
            "PrintNumber" => 1,
            "PrintType" => 1,
            "Width" => (int)$packageInfo['width'],
            "Height" => (int)$packageInfo['length'],
            "Model" => [
                "UrlName" => $packageInfo['label_url']
            ]
        ];
    }

    public function printBackupCopyOfPackageLabel($packageInfo,$counterfoilType = 1)
    {
        $printConfig = $this->getPrintConfig('Counterfoil');
        $template = [

        ];

    }

    /**
     * 打印剩余包裹拣货单
     * @param $packageList
     * @return array
     */
    public function printUnpickedPackagesPicklist($packageList): array
    {
        $productType = "";
        if ($packageList[0]['ProductType'] == 1) {
            $productType = "单品单件";
        }
        if ($packageList[0]['ProductType'] == 2) {
            $productType = "单品多件";
        }
        if ($packageList[0]['ProductType'] == 3) {
            $productType = "多品多件";
        }
        $printConfig = $this->getPrintConfig('SurplusPackagePicking');
        $printConfig['IsSizePrint'] = true;
        $printConfig['PrintNumber'] = 1;
        $printConfig['PrintType'] = 2;
        $printConfig['Model'] = [
            'BatchCode' => $packageList[0]['pick_code'],
            'TypeName' => $productType,
            'SurplusPackagePickingList' => $packageList,
        ];
        return $printConfig;
    }

    /**
     * 打印带有格口号的剩余包裹拣货单
     * @param $outOfStock
     * @return array
     */
    public function printUnpickedPackagesBoxPicklist($outOfStock): array
    {
        $printConfig = $this->getPrintConfig('OutOfStockPicking');
        $printConfig['IsSizePrint'] = true;
        $printConfig['PrintNumber'] = 1;
        $printConfig['PrintType'] = 2;
        $printConfig['Model'] = $outOfStock;
        return $printConfig;
    }

    /**
     * 从0000001开始，依次+1，下单号不可重复
     * @param $code
     * @return false|int
     */
    function isPlatformCode($code) {
        $pattern = '/^0*([1-9]\d*)$/';
        return preg_match($pattern, $code);
    }
}