<?php

namespace Site\Service;

use Site\Storage\MySQL\RoomTypeMapper;
use Site\Storage\MySQL\PriceGroupMapper;
use Site\Storage\MySQL\RoomTypePriceMapper;
use Krystal\Stdlib\ArrayUtils;

final class RoomTypeService
{
    const PARAM_PRICE_GROUP_IDS = 'price_group_ids';

    /**
     * Any compliant room type mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeMapper
     */
    private $roomTypeMapper;

    /**
     * Room type price mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypePriceMapper
     */
    private $roomTypePriceMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RoomTypeMapper $roomTypeMapper
     * @param \Site\Storage\MySQL\RoomTypePriceMapper $roomTypePriceMapper
     * @return void
     */
    public function __construct(RoomTypeMapper $roomTypeMapper, RoomTypePriceMapper $roomTypePriceMapper)
    {
        $this->roomTypeMapper = $roomTypeMapper;
        $this->roomTypePriceMapper = $roomTypePriceMapper;
    }

    /**
     * Fetch all room prices
     * 
     * @param int $hotelId
     * @return array
     */
    public function findAllPrices(int $hotelId) : array
    {
        // Turn raw result-set into collection
        $collection = ArrayUtils::arrayPartition($this->roomTypePriceMapper->findAllPrices($hotelId), 'id');
        $output = [];

        foreach ($collection as $id => $items) {
            $output[$id] =  array_column($items, 'price', 'price_group_id');
        }

        return $output;
    }

    /**
     * Delete room type by its associated ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->roomTypeMapper->deleteByPk($id);
    }

    /**
     * Fetch all room types
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        $rows = $this->roomTypeMapper->fetchAll($hotelId);

        foreach ($rows as &$row) {
            $row['prices'] = $this->findPricesByRoomTypeId($row['id']);
        }

        return $rows;
    }

    /**
     * Finds room type info by its id
     * 
     * @param int $id Room type id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->roomTypeMapper->findByPk($id);
    }

    /**
     * Find data by room type ID
     * 
     * @param int $roomTypeId
     * @return array
     */
    public function findPricesByRoomTypeId(int $roomTypeId) : array
    {
        return $this->roomTypePriceMapper->findAllByRoomTypeId($roomTypeId);
    }

    /**
     * Saves room type service
     * 
     * @param array $input
     * @return array
     */
    private function save(array $input) : array
    {
        // Keep them
        $priceGroupIds = $input[self::PARAM_PRICE_GROUP_IDS];

        // No need to insert IDs
        unset($input[self::PARAM_PRICE_GROUP_IDS]);

        $this->roomTypeMapper->persist($input);

        return $priceGroupIds;
    }

    /**
     * Updates room type
     * 
     * @param array $input
     * @return boolean
     */
    public function update(array $input)
    {
        $priceGroupIds = $this->save($input);
        $this->roomTypePriceMapper->save($input['id'], $priceGroupIds);

        return true;
    }

    /**
     * Adds room type
     * 
     * @param array $input
     * @return boolean
     */
    public function add(array $input)
    {
        $priceGroupIds = $this->save($input);
        $this->roomTypePriceMapper->save($this->roomTypeMapper->getMaxId(), $priceGroupIds);

        return true;
    }
}
