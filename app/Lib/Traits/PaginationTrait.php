<?php

declare(strict_types=1);

namespace App\Lib\Traits;

/**
 * 分页格式化
 * Trait PaginationTrait
 * @package App\Lib\Traits
 */
trait PaginationTrait
{

    /**
     * @param array $list
     * @param int $page_num
     * @param int $page_size
     * @param int $total
     * @return array
     */
    public function pagination(array $list, int $page_num = 0, int $page_size = 0, $total = 0): array
    {
        return [
            'page_num' => $page_num,
            'page_size'    => $page_size,
            'total' => $total,
            'list'    => $list,
        ];
    }
}
