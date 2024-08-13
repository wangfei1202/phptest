<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Services\Image\ImageService;

/**
 *
 */
class ImageController extends AbstractController
{
    /**
     * 上传图片到OBS
     * @return array
     * @throws \Exception
     */
    public function uploadImg()
    {
        try {
            $file = $this->request->file('file_name');
            $url = make(ImageService::class)->uploadToOBS($file);
            return $this->apiSuccess('操作成功', $url);
        } catch (Exception $e) {
            return $this->apiFail('000402', $e->getMessage());
        }
    }

}
