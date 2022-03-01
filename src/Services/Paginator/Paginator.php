<?php

declare(strict_types=1);

namespace App\Services\Paginator;

use Doctrine\ORM\Query;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class Paginator implements PaginatorInterface
{
    public function __construct(
        private \Knp\Component\Pager\PaginatorInterface $paginator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function paginate(int $page, int $lastId, Query $query, int $countRows = 10): SlidingPagination
    {
        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate(
            $query,
            $page,
            $countRows
        );
        $pagination->setParam('last', $lastId);

        return $pagination;
    }
}
