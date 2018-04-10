<?php

namespace Site\Storage\MySQL;

final class ServicePriceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_service_prices');
    }

    /**
     * Fetch all room prices
     * 
     * @param int $hotelId
     * @return array
     */
    public function findAllPrices(int $hotelId) : array
    {
        return $this->db->select([
                            ServiceMapper::column('id'),
                            self::column('price'),
                            self::column('price_group_id')
                        ])
                        ->from(self::getTableName())
                        // Price relation
                        ->leftJoin(ServiceMapper::getTableName())
                        ->on()
                        ->equals(
                            ServiceMapper::column('type_id'),
                            self::getRawColumn('service_id')
                        )
                        // Hotel ID constraint
                        ->whereEquals(ServiceMapper::column('hotel_id'), $hotelId)
                        ->queryAll();
    }

    /**
     * Find data by room type ID
     * 
     * @param int $serviceId
     * @return array
     */
    public function findAllByServiceId(int $serviceId) : array
    {
        // Columns to be selected
        $columns = [
            PriceGroupMapper::column('name'),
            PriceGroupMapper::column('currency'),
            self::column('price'),
            self::column('service_id'),
            self::column('price_group_id') => 'id'
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->leftJoin(PriceGroupMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('price_group_id'),
                            PriceGroupMapper::getRawColumn('id')
                        )
                        ->whereEquals('service_id', $serviceId)
                        ->queryAll();
    }

    /**
     * Updates room type price
     * 
     * @param int $serviceId
     * @param array $priceGroupIds
     * @return boolean
     */
    public function save(int $serviceId, array $priceGroupIds) : bool
    {
        foreach ($priceGroupIds as $priceGroupId => $price) {
            // Exist counter
            $exists = $this->db->select()
                               ->count('id')
                               ->from(self::getTableName())
                               ->whereEquals('service_id', $serviceId)
                               ->andWhereEquals('price_group_id', $priceGroupId)
                               ->queryScalar();

            if ($exists) {
                // Updates
                $this->db->update(self::getTableName(), [
                            'price' => $price
                        ])
                        ->whereEquals('service_id', $serviceId)
                        ->andWhereEquals('price_group_id', $priceGroupId)
                        ->execute();
            } else {
                // Insert
                $this->persist([
                    'service_id' => $serviceId,
                    'price_group_id' => $priceGroupId,
                    'price' => $price
                ]);
            }
        }

        return true;
    }
}
