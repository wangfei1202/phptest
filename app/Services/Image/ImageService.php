<?php

namespace App\Services\Image;

use App\Services\BaseService;
use App\Lib\Platform\OpenApi\UploadObs;
use Exception;

class ImageService extends BaseService
{
    /**
     * @param $file
     * @param $preUrl
     * @return array|string
     */
    public function uploadImg($files,$preUrl){
        $storagePath = "/storage";
        $imgPath = '/image/'.date('Ymd').'/';
        $path = BASE_PATH.$storagePath.$imgPath;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $imgUrl = [];
        foreach ($files as $file) {
            if (!$file->isValid()) {
                // 处理文件无效的情况
                continue;
            }
            $extension = $file->getExtension();
            $filename = uniqid() . '.' . $extension;
            //全路径
            $filePath = $path . $filename;
            $file->moveTo($filePath);
            // 通过 isMoved(): bool 方法判断方法是否已移动
            if (!$file->isMoved()) {
                return ['code' => 3001, 'message' => '上传失败'];
            }
            // 设置文件权限为 0644
            chmod($filePath, 0644);
            $imgUrl[] = $preUrl.$imgPath.$filename;
        }
        return $imgUrl;
    }

    /**
     * @param $file
     * @return array|mixed|string
     * @throws Exception
     */
    public function uploadToOBS($file){
        $dateUrl = date('Ymd')."/";
        $path = BASE_PATH."/storage/image/".$dateUrl;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (!$file->isValid()) {
            throw new \Exception('图片无效');
        }
        $extension = $file->getExtension();
        $filename = uniqid() . '.' . $extension;
        //全路径
        $filePath = $path . $filename;
        $file->moveTo($filePath);
        // 通过 isMoved(): bool 方法判断方法是否已移动
        if (!$file->isMoved()) {
            return ['code' => 3001, 'message' => '上传失败'];
        }
        // 读取文件内容，并进行Base64编码
        $res = make(UploadObs::class)->exec([
            "base64_image" => base64_encode(file_get_contents($filePath)),
            "object" => $dateUrl.$filename,
            "image_url" => ""
        ]);
        return $res['data']['address_url'] ?? "";
    }
}