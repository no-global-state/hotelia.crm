<?php

namespace Site\Storage\MySQL;

final class RoomTypePriceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor_room_type_prices');
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
                            RoomMapper::getFullColumnName('id'),
                            self::getFullColumnName('price'),
                            self::getFullColumnName('price_group_id')
                        ])
                        ->from(self::getTableName())
                        // Price relation
                        ->leftJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            RoomMapper::getFullColumnName('type_id'),
                            self::getRawColumn('room_type_id')
                        )
                        // Hotel ID constraint
                        ->whereEquals(RoomMapper::getFullColumnName('hotel_id'), $hotelId)
                        ->queryAll();
    }

    /**
     * Find data by room type ID
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function findAllByRoomTypeId(int $roomTypeId) : array
    {
        // Columns to be selected
        $columns = [
            PriceGroupMapper::getFullColumnName('name'),
            PriceGroupMapper::getFullColumnName('currency'),
            self::getFullColumnName('price'),
            self::getFullColumnName('room_type_id'),
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
                        ->whereEquals('room_type_id', $roomTypeId)
                        ->queryAll();
    }

    /**
     * Updates room type price
     * 
     * @param int $roomTypeId
     * @param array $priceGroupIds
     * @return boolean
     */
    public function save(int $roomTypeId, array $priceGroupIds)
    {
        foreach ($priceGroupIds as $priceGroupId => $price) {
            // Exist counter
            $exists = $this->db->select()
                               ->count('id')
                               ->from(self::getTableName())
                               ->whereEquals('room_type_id', $roomTypeId)
                               ->andWhereEquals('price_group_id', $priceGroupId)
                               ->queryScalar();

            if ($exists) {
                // Updates
                $this->db->update(self::getTableName(), [
                            'price' => $price
                        ])
                        ->whereEquals('room_type_id', $roomTypeId)
                        ->andWhereEquals('price_group_id', $priceGroupId)
                        ->execute();
            } else {
                // Insert
                $this->persist([
                    'room_type_id' => $roomTypeId,
                    'price_group_id' => $priceGroupId,
                    'price' => $price
                ]);
            }
        }

        return true;
    }
}
