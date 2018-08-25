<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Filter\InputDecorator;

final class TransactionMapper extends AbstractMapper
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
            self::column('price_group_id'),
            self::column('hotel_id'),
            self::column('datetime'),
            self::column('amount'),
            PriceGroupMapper::column('currency'),
            HotelTranslationMapper::column('name') => 'hotel'
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
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => self::getRawColumn('price_group_id')
                       ])
                       // Language constraint
                       ->whereEquals(HotelTranslationMapper::column('lang_id'), 1);

        // Filter by hotel ID if explicitly provided
        if (isset($parameters['hotel_id'])) {
            $db->andWhereEquals('hotel_id', $parameters['hotel_id']);
        }

        // The rest
        $db->andWhereEquals('datetime', $input['datetime'], true)
           ->andWhereLike('amount', $input['amount'], true)
           // Hotel name filter
           ->andWhereLike(HotelTranslationMapper::column('name'), '%'.$input['hotel'].'%', true)
           ->orderBy($sortingColumn ? self::column($sortingColumn) : self::column('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }
}
