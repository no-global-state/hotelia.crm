<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;
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
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc)
    {
        $db = $this->db->select('*')
                       ->from(self::getTableName())
                       ->whereEquals('1', '1')
                       ->andWhereEquals('datetime', $input['datetime'], true)
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
