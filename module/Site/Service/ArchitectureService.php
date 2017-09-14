<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayUtils;
use Site\Storage\MySQL\FloorMapper;
use Site\Storage\MySQL\RoomMapper;
use Site\Storage\MySQL\RoomTypeMapper;

class ArchitectureService
{
    /**
     * Any compliant mapper implementing floor mapper
     * 
     * @var \Site\Storage\MySQL\FloorMapper
     */
    private $floorMapper;

    /**
     * Any compliant mapper implementing room mapper
     * 
     * @var \Site\Storage\MySQL\RoomMapper
     */
    private $roomMapper;

    /**
     * Any compliant mapper implementing room type mapper
     * 
     * @var \Site\Storage\MySQL\RoomTypeMapper
     */
    private $roomTypeMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\FloorMapper $floorMapper
     * @param \Site\Storage\MySQL\RoomMapper $roomMapper
     * @param \Site\Storage\MySQL\RoomTypeMapper $roomTypeMapper
     * @return void
     */
    public function __construct(FloorMapper $floorMapper, RoomMapper $roomMapper, RoomTypeMapper $roomTypeMapper)
    {
        $this->floorMapper = $floorMapper;
        $this->roomMapper = $roomMapper;
        $this->roomTypeMapper = $roomTypeMapper;
    }

    /**
     * Finds free available rooms based on date range and attached hotel ID
     * 
     * @param integer $hotelId
     * @param string $arrival
     * @param string $departure
     * @param array $typeIds Optional type ID filters
     * @return string
     */
    public function findFreeRooms($hotelId, $arrival, $departure, $typeIds = array())
    {
        return $this->roomMapper->findFreeRooms($hotelId, $arrival, $departure, $typeIds);
    }

    /**
     * Returns a collection of floors
     * 
     * @param integer $hotelId
     * @return array
     */
    public function getFloors($hotelId)
    {
        return ArrayUtils::arrayList($this->floorMapper->fetchAll($hotelId), 'id', 'name');
    }

    /**
     * Returns room types
     * 
     * @param integer $hotelId
     * @return array
     */
    public function getRoomTypes($hotelId)
    {
        return ArrayUtils::arrayList($this->roomTypeMapper->fetchAll($hotelId), 'id', 'type');
    }

    /**
     * Returns room prices
     * 
     * @param integer $hotelId
     * @return array
     */
    public function getRoomPrices($hotelId)
    {
        return ArrayUtils::arrayList($this->roomMapper->fetchPrices($hotelId), 'id', 'unit_price');
    }

    /**
     * Creates basic statistic
     * 
     * @param integer $hotelId
     * @return array
     */
    public function createStat($hotelId)
    {
        $room = $this->roomMapper->fetchStatistic($hotelId);
        $floorCount = $this->floorMapper->getFloorCount($hotelId);

        // Statistic
        return array(
            'Total room count' => $room['rooms_count'],
            'Total floors count' => $floorCount,
            'Taken rooms count' => $room['rooms_taken'],
            'Free rooms count' => $room['rooms_free'],
            'Rooms freeing today' => $room['rooms_leaving_today']
        );
    }

    /**
     * Creates table
     * 
     * @param integer $hotelId
     * @return array
     */
    public function createTable($hotelId)
    {
        $output = array();

        foreach ($this->floorMapper->fetchAll($hotelId) as $floor) {
            $floor['rooms'] = $this->roomMapper->fetchAll($floor['id']);
            $output[] = $floor;
        }

        return $output;
    }

    /**
     * Create rooms
     * 
     * @param integer $hotelId
     * @return array
     */
    public function createRooms($hotelId)
    {
        $output = array();

        foreach ($this->createTable($hotelId) as $row) {
            $output[$row['name']] = ArrayUtils::arrayList($row['rooms'], 'id', 'name');
        }

        return $output;
    }
}
