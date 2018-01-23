<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayUtils;
use Krystal\Text\Math;
use Site\Storage\MySQL\FloorMapper;
use Site\Storage\MySQL\RoomMapper;
use Site\Storage\MySQL\RoomTypeMapper;
use DateTime;

class ArchitectureService
{
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
     * @param \Site\Storage\MySQL\RoomMapper $roomMapper
     * @param \Site\Storage\MySQL\RoomTypeMapper $roomTypeMapper
     * @return void
     */
    public function __construct(RoomMapper $roomMapper, RoomTypeMapper $roomTypeMapper)
    {
        $this->roomMapper = $roomMapper;
        $this->roomTypeMapper = $roomTypeMapper;
    }

    /**
     * Checks whether room name exists
     * 
     * @param string $name
     * @param int $hotelId
     * @return boolean
     */
    public function roomNameExists(string $name, int $hotelId) : bool
    {
        return $this->roomMapper->nameExists($name, $hotelId);
    }

    /**
     * Gets room data by its associated ID
     * 
     * @param string $id Room ID
     * @param int $langId
     * @return array
     */
    public function getById(int $id, int $langId)
    {
        return $this->roomMapper->fetchById($id, $langId);
    }

    /**
     * Find available rooms for next days
     * 
     * @param int $langId
     * @param string $hotelId Current hotel ID
     * @param string $days Days to add to current date
     * @return array
     */
    public function findAvailableRooms(int $langId, int $hotelId, $days = '+10 day')
    {
        $format = 'Y-m-d';
        $today = date($format);

        $next = (new DateTime($today))->modify($days)->format($format);
        return $this->findFreeRooms($langId, $hotelId, $today, $next);
    }

    /**
     * Finds free available rooms based on date range and attached hotel ID
     * 
     * @param int $langId
     * @param int $hotelId
     * @param string $arrival
     * @param string $departure
     * @param array $typeIds Optional type ID filters
     * @param array $inventoryIds
     * @return array
     */
    public function findFreeRooms(int $langId, int $hotelId, $arrival, $departure, $typeIds = array(), $inventoryIds = array())
    {
        return $this->roomMapper->findFreeRooms($langId, $hotelId, $arrival, $departure, $typeIds, $inventoryIds);
    }

    /**
     * Returns room types
     * 
     * @param int $langId
     * @param integer $hotelId
     * @return array
     */
    public function getRoomTypes(int $langId, int $hotelId)
    {
        return ArrayUtils::arrayList($this->roomTypeMapper->fetchAll($langId, $hotelId), 'id', 'name');
    }

    /**
     * Creates basic statistic
     * 
     * @param integer $hotelId
     * @return array
     */
    public function createStat(int $hotelId) : array
    {
        $room = $this->roomMapper->fetchStatistic($hotelId);
        $floorCount = $this->roomMapper->getFloorCount($hotelId);

        // Free in %
        $free = Math::percentage($room['rooms_count'], $room['rooms_taken']);
        $taken = 100 - $free;

        // Statistic
        return [
            'Total room count' => (int) $room['rooms_count'],
            'Total floors count' => (int) $floorCount,
            'Taken rooms count' => (int) $room['rooms_taken'],
            'Free rooms count' => (int) ($room['rooms_count'] - $room['rooms_taken']),
            'Rooms freeing today' => (int) $room['rooms_leaving_today'],
            'Free' => $free . ' % ',
            'Taken' => $taken . ' % ',
        ];
    }

    /**
     * Creates table
     * 
     * @param int $langId
     * @param integer $hotelId
     * @return array
     */
    public function createTable(int $langId, int $hotelId) : array
    {
        // Find all rooms associated with current hotel ID
        $rooms = $this->roomMapper->fetchAll($langId, $hotelId);

        // Data holders
        $floors = [];
        $output = [];

        // Append available floors
        foreach ($rooms as $room) {
            $floors[] = $room['floor'];
        }

        // Remove duplicated floors if any
        $floors = array_unique($floors);

        // Create relation
        foreach ($floors as $floor) {
            foreach ($rooms as $room) {
                if ($room['floor'] == $floor) {
                    $output[$floor][] = $room;
                }
            }
        }

        return $output;
    }

    /**
     * Create rooms
     * 
     * @param int $langId
     * @param integer $hotelId
     * @return array
     */
    public function createRooms(int $langId, int $hotelId)
    {
        $output = [];

        foreach ($this->createTable($langId, $hotelId) as $floor => $room) {
            $output[$floor] = ArrayUtils::arrayList($room, 'id', 'name');
        }

        return $output;
    }
}
