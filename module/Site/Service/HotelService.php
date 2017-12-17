<?php

namespace Site\Service;

use Site\Storage\MySQL\HotelMapper;

final class HotelService
{
    /**
     * Any compliant hotel mapper
     * 
     * @var \Site\Storage\MySQL\HotelMapper
     */
    private $hotelMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\HotelMapper $hotelMapper
     * @return void
     */
    public function __construct(HotelMapper $hotelMapper)
    {
        $this->hotelMapper = $hotelMapper;
    }

    /**
     * Fetch hotel by its ID
     * 
     * @param int $id Hotel ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->hotelMapper->fetchById($id, $langId);
    }

    /**
     * Fetch all hotels
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->hotelMapper->fetchAll($langId);
    }

    /**
     * Saves hotel
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->hotelMapper->saveEntity($input['hotel'], $input['translation']);
    }
}
