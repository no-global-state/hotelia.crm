<?php

namespace Site\Service;

use Krystal\Db\Filter\FilterableServiceInterface;
use Site\Storage\MySQL\TransactionMapper;

final class TransactionService implements FilterableServiceInterface
{
    /**
     * Any compliant transaction mapper
     * 
     * @var \Site\Storage\MySQL\TransactionMapper
     */
    private $transactionMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\TransactionMapper $transactionMapper
     * @return void
     */
    public function __construct(TransactionMapper $transactionMapper)
    {
        $this->transactionMapper = $transactionMapper;
    }

    /**
     * Returns prepared pagination instance
     * 
     * @return \Krystal\Paginate\Paginator
     */
    public function getPaginator()
    {
        return $this->transactionMapper->getPaginator();
    }

    /**
     * Fetch latest transactions
     * 
     * @param int $hotelId
     * @param int $limit
     * @return array
     */
    public function fetchLast(int $hotelId, int $limit = 5) : array
    {
        return $this->transactionMapper->fetchLast($hotelId, $limit);
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        return $this->transactionMapper->filter($input, $page, $itemsPerPage, $sortingColumn, $desc, $parameters);
    }
}