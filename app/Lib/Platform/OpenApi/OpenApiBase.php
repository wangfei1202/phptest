<?php

declare(strict_types=1);

namespace App\Lib\Platform\OpenApi;

use App\Lib\Utils\RequestClient;
use App\Traits\ResponseTrait;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\ApplicationContext;

/**
 * Class OpenApiBase
 * @package App\Lib\Platform\OpenApi
 */
class OpenApiBase
{
    use ResponseTrait;

    /**
     * @Inject
     * @var RequestClient
     */
    protected $requestClient;

    protected $apiUrl = '';

    protected $thirdPartyUrl = '';

    public function __construct()
    {
        $this->apiUrl = config('open_api_url');
        $this->thirdPartyUrl = config('third_party_api_url');
    }
}
