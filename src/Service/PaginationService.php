<?php

namespace App\Service;

use Knp\Component\Pager\Pagination\PaginationInterface;


class PaginationService
{
    public function __construct()
    {}

    public function getPaginatonResult(PaginationInterface $pagination) : array
    {
        return [
            'total' => $pagination->getTotalItemCount(),
            'page' => $pagination->getCurrentPageNumber(),
            'lastPage' => $pagination->getPageCount(),
            'items' => $pagination->getItems(),
        ];
    }
}