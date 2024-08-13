<?php

declare(strict_types=1);

namespace App\Lib\OSS;

use App\Traits\ResponseTrait;
use GuzzleHttp\Client;
use Hyperf\Guzzle\HandlerStackFactory;
use OSS\OssClient;

/**
 * Class OssService
 * @package App\Lib\OSS
 */
class OssService
{
    use ResponseTrait;

    /**
     * @var string
     */
    public $accessKeyId;

    /**
     * @var string
     */
    public $accessKeySecret;

    /**
     * @var string
     */
    public $endpoint;

    /**
     * @var string
     */
    public $bucketName;

    /**
     * OssService constructor.
     */
    public function __construct()
    {
        $this->accessKeyId     = config('file.storage.oss.accessId');
        $this->accessKeySecret = config('file.storage.oss.accessSecret');
        $this->endpoint        = config('file.storage.oss.endpoint');
        $this->bucketName      = config('file.storage.oss.bucket');
    }

    /**
     * 获取OSS上传目录
     *
     * @param bool $https
     *
     * @return string
     */
    public function getOssDir($https = true)
    {
        return ($https ? "https://" : "http://") . $this->bucketName . "." . $this->endpoint;
    }

    /**
     * 图片加水印
     * @param $imageUrl
     * @param $text
     * @param $prefix
     * @return array
     */
    public function waterMark($imageUrl, $text, $prefix)
    {
        try {
//            $imageUrl = 'https://elenxs-runbu.oss-cn-shenzhen.aliyuncs.com/39f85c61-7eb9-e564-c662-35736b213d30.jpg';
            $imageUrlArr = explode('/', $imageUrl);
            $imageName = end($imageUrlArr);
            $imgArr = explode('.', $imageName);
            $newImageName = sprintf('%s.%s', $imgArr[0], $imgArr[1]);
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            $object = 'amazon/' . $imageName;
            //文字水印
            $textWatermark = $this->getWatermarkDecode($text);
            //图片水印
            $imageWatermark = $this->getWatermarkDecode('amazon/white_background.jpg');
            // 指定处理后的图片名称。
            $saveObject = sprintf('amazon/%s/%s', $prefix, $newImageName);
            $color = '0000b7';
            $t = '100';
            $color = 'FFFFFF';
            $t = '0';
            $size = mt_rand(10, 200);//(0,1000]
            $g = ['nw', 'north', 'ne', 'west', 'center', 'east', 'sw', 'south', 'se'];
            $gImage = $g[array_rand($g)];
            $gText = $g[array_rand($g)];
            $xImage = mt_rand(5, 300);
            $yImage = mt_rand(5, 300);
            $xText = mt_rand(5, 300);
            $yText = mt_rand(5, 300);
            $watermark = "image/watermark,image_{$imageWatermark},g_{$gImage},x_{$xImage},y_{$yImage},t_{$t}/watermark,text_{$textWatermark},size_{$size},g_{$gText},x_{$xText},y_{$yText},color_{$color},t_{$t}";
            // 将处理后的图片转存到当前Bucket。
            $process = $watermark . '|sys/saveas,o_' . $this->getWatermarkDecode($saveObject) . ',b_' . $this->getWatermarkDecode($this->bucketName);
            $result = $ossClient->processObject($this->bucketName, $object, $process);
            $result = json_decode($result, true);
            // 打印处理结果。
            // 图片处理完成后，若Bucket内的原图不再需要，可以删除原图。
             $ossClient->deleteObject($this->bucketName, $object);
            if (!isset($result['status']) || $result['status'] != 'OK') {
                throw new \Exception('[OSS]添加图片水印失败！', 30001);
            }

            return $this->success('[OSS]添加图片水印成功！', ['object' => $saveObject]);
        } catch (\Exception $e) {
            return $this->fail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 水印编码
     * @param $string
     * @return mixed|string|string[]
     */
    public function getWatermarkDecode($string)
    {
        $string = base64_encode($string);
        $string = str_replace('+', '-', $string);
        $string = str_replace('/', '_', $string);
        $string = rtrim($string, '=');

        return $string;
    }

    /**
     * 上传图片到阿里云OSS
     * @param $imagePath
     * @return array
     */
    public function uploadFile($imagePath)
    {
        try {
            $ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
            // 若目标图片不在指定Bucket内，需上传图片到目标Bucket。
            $object = str_replace(BASE_PATH . '/upload/', '', $imagePath);
            $result = $ossClient->uploadFile($this->bucketName, $object, $imagePath);
            if (empty($result['info']) || $result['info']['http_code'] != 200) {
                throw new \Exception('[OSS]向阿里云上传失败！', 30001);
            }

            return $this->success('[OSS]上传成功！', ['oss-request-url' => $result['oss-request-url']]);
        } catch (\Exception $e) {
            return $this->fail($e->getCode(), $e->getMessage());
        }
    }

    /**
     * 下载图片到本地
     * @param $imageUrl
     * @return array
     */
    public function downloadFile($imageUrl)
    {
        try {
            $imageUrlArr = explode('/', $imageUrl);
            $imageName = end($imageUrlArr);
            $factory = new HandlerStackFactory();
            $stack = $factory->create();

            $client = make(Client::class, [
                'config' => [
                    'handler' => $stack,
                ],
            ]);
            $content = $client->get($imageUrl)->getBody()->getContents();
            if (!$content) {
                throw new \Exception('下载图片失败: ' . $imageUrl);
            }
            $dir = BASE_PATH . '/upload/ebay/' . date('Ymd');
            if (!file_exists($dir)) {
                $i = umask(0);
                mkdir($dir, 0777);
                chmod($dir, 0777);
                umask($i);
            }
            $imagePath = $dir . '/' . $imageName;
            file_put_contents($imagePath, $content);

            return $this->success('下载成功！', ['image_url' => $imageUrl, 'image_path' => $imagePath, 'image_name' => $imageName]);
        } catch (\Exception $e) {
            return $this->fail($e->getCode(), $e->getMessage());
        }
    }

}
