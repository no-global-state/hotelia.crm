<?php

namespace Site\Storage\MySQL;

final class ReservationServiceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reservation_services');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Finds currency by reservation id
     * 
     * @param int $id Reservation ID
     * @return string
     */
    public function findCurrencyByReservationId(int $id) : string
    {
        return $this->db->select(PriceGroupMapper::column('currency'))
                        ->from(ReservationMapper::getTableName())
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            ReservationMapper::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                        ])
                        ->whereEquals(ReservationMapper::column('id'), $id)
                        ->queryScalar();
    }

    /**
     * Find reservation by its ID
     * 
     * @param int $id Reservation ID
     * @return array
     */
    public function findAllByReservationId(int $id)
    {
        return $this->db->select([
                            self::column('id'),
                            self::column('rate'),
                            self::column('price'),
                            self::column('qty'),
                            ServiceMapper::column('unit'),
                            ServiceMapper::column('name') => 'service',
                        ])
                        ->from(self::getTableName())
                        // Service relation
                        ->leftJoin(ServiceMapper::getTableName(), [
                            self::column('slave_id') => ServiceMapper::getRawColumn('id')
                        ])
                        ->whereEquals('master_id', $id)
                        ->orderBy(self::column($this->getPk()))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Find all services attached to reservation id
     * 
     * @param int $id Reservation ID
     * @param int $hotelId
     * @return array
     */
    public function findOptionsByReservationId(int $id, int $hotelId) : array
    {
        // Columns to be selected
        $columns = [
            ServiceMapper::column('id'),
            ServiceMapper::column('name') => 'service',
            ServiceMapper::column('unit'),
            ServicePriceMapper::column('price') => 'rate',
            PriceGroupMapper::column('currency')
        ];

        $db = $this->db->select($columns, true)
                        ->from(ServiceMapper::getTableName())
                        
                        // Service prices
                        ->leftJoin(ServicePriceMapper::getTableName(), [
                            ServicePriceMapper::column('service_id') => ServiceMapper::getRawColumn('id')
                        ])
                        
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName(), [
                            ReservationMapper::column('price_group_id') => ServicePriceMapper::getRawColumn('price_group_id'),
                            ReservationMapper::column('hotel_id') => ServiceMapper::getRawColumn('hotel_id')
                        ])
                        
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => ReservationMapper::getRawColumn('price_group_id')
                        ])
                        // Constraint
                        ->whereEquals(ReservationMapper::column('id'), $id)
                        ->andWhereEquals(ReservationMapper::column('hotel_id'), $hotelId);

        return $db->queryAll();
    }
}
