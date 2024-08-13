<?php

namespace App\Lib\Platform\OpenApi;

use Hyperf\Guzzle\ClientFactory;
use Hyperf\Guzzle\HandlerStackFactory;
use App\Lib\Platform\OpenApi\OpenApiBase;

class OpenApiService extends OpenApiBase
{
    /**
     * @param $id
     * @return array
     */
    public function createPackageQueue($id)
    {
        try {
            $getData = [
                'id' => (int)$id
            ];
            $header = [
                'LoginName' => env('LoginName'),
                'Password' => env('Password'),
            ];
            // 请求数据
            $result = $this->requestClient->setPostType('form_params')->get($this->thirdPartyUrl,$getData,$header, 0);
            if (empty($result)) {
                throw new \Exception('获取数据失败', 30001);
            }
            $data = json_decode($result, true);

            return $this->success('获取成功', $data);
        } catch (\Exception $e) {
            return $this->fail($e->getCode(), $e->getMessage());
        }
    }
}