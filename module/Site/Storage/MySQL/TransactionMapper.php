<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Filter\FilterableServiceInterface;
use Krystal\Db\Filter\InputDecorator;

final class TransactionMapper extends AbstractMapper implements FilterableServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_transactions');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
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
        return $this->filter(new InputDecorator(), 1, $limit, $this->getPk(), true, ['hotel_id' => $hotelId]);
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        $db = $this->db->select('*')
                       ->from(self::getTableName())
                       ->whereEquals('hotel_id', $parameters['hotel_id'])
                       ->andWhereEquals('datetime', $input['datetime'], true)
                       ->andWhereLike('holder', '%'.$input['holder'].'%', true)
                       ->andWhereLike('payment_system', '%'.$input['payment_system'].'%', true)
                       ->andWhereLike('amount', $input['amount'], true)
                       ->andWhereEquals('currency', $input['currency'], true)
                       ->orderBy($sortingColumn ? self::column($sortingColumn) : self::column('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }
}
