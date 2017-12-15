<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

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
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
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
    public function update(int $roomTypeId, array $priceGroupIds)
    {
        foreach ($priceGroupIds as $priceGroupId => $price) {
            // Updates
            $this->db->update(self::getTableName(), [
                        'price' => $price
                    ])
                    ->whereEquals('room_type_id', $roomTypeId)
                    ->andWhereEquals('price_group_id', $priceGroupId)
                    ->execute();
        }

        return true;
    }

    /**
     * Adds room type price
     * 
     * @param int $roomTypeId
     * @param array $priceGroupIds
     * @return boolean
     */
    public function add(int $roomTypeId, array $priceGroupIds)
    {
        foreach ($priceGroupIds as $priceGroupId => $price) {
            $this->insert($roomTypeId, $priceGroupId, $price);
        }

        return true;
    }

    /**
     * Adds new item
     * 
     * @param int $roomTypeId
     * @param int $priceGroupId
     * @param float $price
     * @return boolean
     */
    private function insert(int $roomTypeId, int $priceGroupId, float $price)
    {
        $data = [
            'room_type_id' => $roomTypeId,
            'price_group_id' => $priceGroupId,
            'price' => $price
        ];

        return $this->persist($data);
    }
}
