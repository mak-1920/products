<?php

declare(strict_types=1);

namespace App\Services\Paginator;

use Doctrine\ORM\Query;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

interface PaginatorInterface
{
    /**
     * @param int $page
     * @param int $lastId
     * @param Query $query
     * @param int $countRows
     *
     * @return SlidingPagination
     */
    public function paginate(int $page, int $lastId, Query $query, int $countRows = 10): SlidingPagination;
}
