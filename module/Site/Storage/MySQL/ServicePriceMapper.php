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
                            ServiceMapper::getFullColumnName('id'),
                            self::getFullColumnName('price'),
                            self::getFullColumnName('price_group_id')
                        ])
                        ->from(self::getTableName())
                        // Price relation
                        ->leftJoin(ServiceMapper::getTableName())
                        ->on()
                        ->equals(
                            ServiceMapper::getFullColumnName('type_id'),
                            self::getRawColumn('service_id')
                        )
                        // Hotel ID constraint
                        ->whereEquals(ServiceMapper::getFullColumnName('hotel_id'), $hotelId)
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
            PriceGroupMapper::getFullColumnName('name'),
            PriceGroupMapper::getFullColumnName('currency'),
            self::getFullColumnName('price'),
            self::getFullColumnName('service_id'),
            self::getFullColumnName('price_group_id') => 'id'
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        ->leftJoin(PriceGroupMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('price_group_id'),
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
