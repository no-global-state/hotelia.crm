<?php

namespace Site\Storage\MySQL;

final class BookingExternalRelationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_external');
    }

    /**
     * Find all bookings by external user ID
     * 
     * @param int $id External user ID
     * @return array
     */
    public function findAllByExternalId(int $id) : array
    {
        // Columns to be selected
        $columns = [
            BookingMapper::column('id'),
            BookingMapper::column('hotel_id'),
            BookingMapper::column('price_group_id'),
            BookingMapper::column('lang_id'),
            BookingMapper::column('datetime'),
            BookingMapper::column('status'),
            BookingMapper::column('arrival'),
            BookingMapper::column('departure'),
            BookingMapper::column('phone'),
            BookingMapper::column('email'),
            BookingMapper::column('comment'),
            BookingMapper::column('near_preferred'),
            BookingMapper::column('amount'),
            PriceGroupMapper::column('name') => 'price_group',
            PriceGroupMapper::column('currency'),
        ];

        $db = $this->db->select($columns)
                       ->from(BookingMapper::getTableName())
                       // Relational constraint
                       ->innerJoin(self::getTableName(), [
                            self::column('slave_id') => BookingMapper::getRawColumn('id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            BookingMapper::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                       ])
                       ->whereEquals(self::column('master_id'), $id);

        return $db->queryAll();
    }
}
