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
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('hotel_id'),
            self::column('datetime'),
            self::column('holder'),
            self::column('payment_system'),
            self::column('amount'),
            self::column('currency'),
            self::column('comment'),
            HotelTranslationMapper::column('name')
        ];

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('id') => self::getRawColumn('hotel_id')
                       ])
                       // Hotel translation translation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::column('id') => HotelMapper::getRawColumn('id')
                       ])
                       // Language constraint
                       ->whereEquals(HotelTranslationMapper::column('lang_id'), 1);

        // Filter by hotel ID if explicitly provided
        if (isset($parameters['hotel_id'])) {
            $db->andWhereEquals('hotel_id', $parameters['hotel_id']);
        }

        // The rest
        $db->andWhereEquals('datetime', $input['datetime'], true)
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
