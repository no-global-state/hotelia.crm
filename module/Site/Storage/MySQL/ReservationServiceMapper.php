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
        return $this->db->select(PriceGroupMapper::getFullColumnName('currency'))
                        ->from(ReservationMapper::getTableName())
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            ReservationMapper::getFullColumnName('price_group_id') => PriceGroupMapper::getRawColumn('id')
                        ])
                        ->whereEquals(ReservationMapper::getFullColumnName('id'), $id)
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
                            self::getFullColumnName('id'),
                            self::getFullColumnName('rate'),
                            self::getFullColumnName('price'),
                            self::getFullColumnName('qty'),
                            ServiceMapper::getFullColumnName('unit'),
                            ServiceMapper::getFullColumnName('name') => 'service',
                        ])
                        ->from(self::getTableName())
                        // Service relation
                        ->leftJoin(ServiceMapper::getTableName(), [
                            self::getFullColumnName('slave_id') => ServiceMapper::getRawColumn('id')
                        ])
                        ->whereEquals('master_id', $id)
                        ->orderBy(self::getFullColumnName($this->getPk()))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Find all services attached to reservation id
     * 
     * @param int $id Reservation ID
     * @return array
     */
    public function findOptionsByReservationId(int $id) : array
    {
        return $this->db->select([
                            ServiceMapper::getFullColumnName('id'),
                            ServiceMapper::getFullColumnName('name') => 'service',
                            PriceGroupMapper::getFullColumnName('currency'),
                            ServiceMapper::getFullColumnName('unit'),
                            ServicePriceMapper::getFullColumnName('price') => 'rate'
                        ])
                        ->from(ReservationMapper::getTableName())
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::getFullColumnName('id') => ReservationMapper::getRawColumn('price_group_id')
                        ])
                        // Price relation
                        ->leftJoin(ServicePriceMapper::getTableName(), [
                            ServicePriceMapper::getFullColumnName('price_group_id') => ReservationMapper::getRawColumn('price_group_id')
                        ])
                        // Service relation
                        ->leftJoin(ServiceMapper::getTableName(), [
                            ServiceMapper::getFullColumnName('id') => ServicePriceMapper::getRawColumn('service_id')
                        ])
                        // Reservation constraint
                        ->whereEquals(ReservationMapper::getFullColumnName('id'), $id)
                        ->queryAll();
    }
}
