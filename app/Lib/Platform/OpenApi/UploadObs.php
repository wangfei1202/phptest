<?php

declare(strict_types=1);

namespace App\Lib\Platform\OpenApi;

use App\Lib\Utils\RequestClient;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;

/**
 * Class OpenApiBase
 * @package App\Lib\Platform\OpenApi
 */
class UploadObs extends OpenApiBase
{

    public $method = 'obs.upload';
    public $dir = 'warehouse';
    public $platform = 'warehouse';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取价格
     *
     * @param array $param
     *
     * @return array
     */
    public function exec(array $param = [])
    {
        try {
            $postData = [
                'platform' => $this->platform,
                'method' => strtolower($this->method),
                'image_url' => $param['image_url'],
                'dir' => $this->dir,
                'object' => $param['object']
            ];
            if(isset($param['base64_image'])){
                $postData['base64_image'] = $param['base64_image'];
            }
            //echo json_encode($postData,JSON_UNESCAPED_UNICODE);
            // 请求数据
            $result = $this->requestClient->setPostType('form_params')->post($this->apiUrl, $postData, [], 60);
            if(empty($result)) throw new \Exception('请求api失败');
            $data = json_decode($result, true);

            if (empty($data)) {
                throw new \Exception('获取数据失败', 30001);
            }
            if ($data['ack'] == false) {
                throw new \Exception('获取数据失败：' . $data['message'], 30001);
            }
            return $this->success('获取成功', $data['data']);
        } catch (\Exception $e) {
            return $this->fail($e->getCode(), $e->getMessage());
        }
    }

}
