<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class PaymentSystemFieldDataMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_payment_systems_fields_data');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Delete all data by hotel ID
     * 
     * @param int $hotelId
     * @return boolean
     */
    public function deleteAllByHotelId(int $hotelId)
    {
        return $this->deleteByColumn('hotel_id', $hotelId);
    }

    /**
     * Find all data by associated hotel id
     * 
     * @param int $hotelId
     * @return array
     */
    public function findAllByHotelId(int $hotelId) : array
    {
        // Data to be selected
        $columns = [
            self::getFullColumnName('id'),
            PaymentSystemFieldMapper::getFullColumnName('id') => 'field_id',
            self::getFullColumnName('hotel_id'),
            self::getFullColumnName('value'),
            PaymentSystemFieldMapper::getFullColumnName('name') => 'field',
            PaymentSystemMapper::getFullColumnName('name') => 'system'
        ];

        return $this->db->select($columns)
                        ->from(PaymentSystemFieldMapper::getTableName())
                        // Field relation
                        ->leftJoin(self::getTableName(), [
                            PaymentSystemFieldMapper::getFullColumnName('id') => self::getRawColumn('field_id'),
                            self::getFullColumnName('hotel_id') => new RawSqlFragment((int) $hotelId)
                        ])
                        // Payment system relation
                        ->leftJoin(PaymentSystemMapper::getTableName(), [
                            PaymentSystemMapper::getFullColumnName('id') => PaymentSystemFieldMapper::getRawColumn('payment_system_id')
                        ])
                        // Sort them
                        ->orderBy(PaymentSystemFieldMapper::getFullColumnName('order'))
                        ->queryAll();
    }
}
