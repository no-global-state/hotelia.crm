<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Filter\FilterableServiceInterface;

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
                       ->orderBy($sortingColumn ? self::getFullColumnName($sortingColumn) : self::getFullColumnName('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }
}
