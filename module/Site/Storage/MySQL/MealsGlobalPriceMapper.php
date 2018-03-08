<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class MealsGlobalPriceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_meals_global_prices');
    }

    /**
     * Update price
     * 
     * @param int $hotelId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $hotelId, array $data) : bool
    {
        $this->deleteByColumn('hotel_id', $hotelId);

        return $this->db->insertMany(self::getTableName(), ['hotel_id', 'price_group_id', 'price'], $data)
                        ->execute();
    }

    /**
     * Finds price group by hotel ID
     * 
     * @param int $hotelId
     * @return array
     */
    public function findByHotelId(int $hotelId) : array
    {
        // Columns to be selected
        $columns = [
            PriceGroupMapper::column('id') => 'price_group_id',
            PriceGroupMapper::column('currency'),
            PriceGroupMapper::column('name') => 'group',
            self::column('id'),
            self::column('price')
        ];

        return $this->db->select($columns)
                        ->from(PriceGroupMapper::getTableName())
                        // Global price relation
                        ->leftJoin(self::getTableName(), [
                            self::column('price_group_id') => new RawSqlFragment(PriceGroupMapper::column('id')),
                            self::column('hotel_id') => $hotelId
                        ])
                        ->queryAll();
    }
}
