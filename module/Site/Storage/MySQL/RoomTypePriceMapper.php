<?php

namespace Site\Storage\MySQL;

final class RoomTypePriceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_type_prices');
    }

    /**
     * Find price by room type ID
     * 
     * @param int $roomTypeId
     * @param int $priceGroupId
     * @param int $qty
     * @return array
     */
    public function findPriceByRoomTypeId(int $roomTypeId, int $priceGroupId, $qty)
    {
        return $this->db->select('price')
                        ->from(self::getTableName())
                        ->whereEquals('room_type_id', $roomTypeId)
                        ->andWhereEquals('price_group_id', $priceGroupId)
                        ->andWhereEquals('capacity', $qty)
                        ->queryScalar();
    }

    /**
     * Find a price by its attached ID
     * 
     * @param int $id
     * @return array
     */
    public function findPrice(int $id)
    {
        return $this->db->select('price')
                        ->from(self::getTableName())
                        ->whereEquals('id', $id)
                        ->queryScalar();
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
                            RoomMapper::column('id'),
                            self::column('price'),
                            self::column('capacity'),
                            self::column('price_group_id')
                        ])
                        ->from(self::getTableName())
                        // Price relation
                        ->leftJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            RoomMapper::column('type_id'),
                            self::getRawColumn('room_type_id')
                        )
                        // Hotel ID constraint
                        ->whereEquals(RoomMapper::column('hotel_id'), $hotelId)
                        ->queryAll();
    }

    /**
     * Find data by room type ID
     * 
     * @param int $roomTypeId
     * @param mixed $priceGroupId
     * @return array
     */
    public function findAllByRoomTypeId(int $roomTypeId, $priceGroupId = null) : array
    {
        // Columns to be selected
        $columns = [
            PriceGroupMapper::column('name'),
            PriceGroupMapper::column('currency'),
            self::column('price'),
            self::column('capacity'),
            self::column('room_type_id'),
            self::column('price_group_id') => 'id',
            self::column('id') => 'uniq'
        ];

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        ->leftJoin(PriceGroupMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('price_group_id'),
                            PriceGroupMapper::getRawColumn('id')
                        )
                        ->whereEquals('room_type_id', $roomTypeId);

        // Append optional filter on demand
        if ($priceGroupId !== null){
            $db->andWhereEquals('price_group_id', $priceGroupId);
        }

        return $db->queryAll();
    }

    /**
     * Save singular data
     * 
     * @param int $roomTypeId
     * @param array $prices
     * @return boolean
     */
    public function saveSingular(int $roomTypeId, array $prices) : bool
    {
        foreach ($prices as $groupId => $price) {
            // To be inserted
            $row = [
                'room_type_id' => $roomTypeId,
                'price_group_id' => $groupId,
                'price' => $price,
                'capacity' => 1
            ];

            $this->persist($row);
        }

        return true;
    }

    /**
     * Updates room type price
     * 
     * @param int $roomTypeId
     * @param array $collection
     * @return boolean
     */
    public function save(int $roomTypeId, array $collection)
    {
        // Clear previous if any
        $this->deleteByColumn('room_type_id', $roomTypeId);
        return $this->persistMany($collection);
    }
}
