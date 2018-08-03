<?php

namespace Site\Service;

use Krystal\Stdlib\ArrayUtils;
use Krystal\Text\Math;
use Krystal\I18n\TranslatorInterface;
use Site\Storage\MySQL\FloorMapper;
use Site\Storage\MySQL\RoomMapper;
use Site\Storage\MySQL\RoomTypeMapper;
use DateTime;
use Site\Collection\CleaningCollection;

final class RoomService
{
    const PARAM_ROOM_COUNT = 1;
    const PARAM_FLOOR_COUNT = 2;
    const PARAM_TAKEN_ROOM_COUNT = 3;
    const PARAM_FREE_ROOM_COUNT = 4;
    const PARAM_FREE_ROOMS_TODAY = 5;
    const PARAM_FREE = 6;
    const PARAM_TAKEN = 7;

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
     * Updated cleaned attribute
     * 
     * @param int $ids Room ID or IDs
     * @param int $type Cleaning status constant
     * @return boolean
     */
    public function updateCleaned($ids, int $type) : bool
    {
        if (!is_array($ids)) {
            $ids = (array) $ids;
        }

        $collection = new CleaningCollection();

        if ($collection->hasKey($type)) {
            foreach ($ids as $id) {
                $this->roomMapper->updateColumnByPk($id, 'cleaned', $type);
            }

            return true;

        } else {
            // Invalid request
            return false;
        }
    }

    /**
     * Persists room entity
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input) : bool
    {
        return $this->roomMapper->persist($input);
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
     * Deletes a room by its associated ID
     * 
     * @param int $id Room ID
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->roomMapper->deleteByPk($id);
    }

    /**
     * Finds all rooms
     * 
     * @param int $langId
     * @param int $hotelId
     * @param int|null $typeId Optional type ID filter
     * @return array
     */
    public function findAll(int $langId, int $hotelId, $typeId = null) : array
    {
        return $this->roomMapper->findAll($langId, $hotelId, $typeId);
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

        return [
            self:: PARAM_ROOM_COUNT => [
                'value' => (int) $room['rooms_count'],
                'description' => 'Total room count',
            ],

            self::PARAM_FLOOR_COUNT => [
                'value' => (int) $floorCount,
                'description' => 'Total floors count',
            ],

            self::PARAM_TAKEN_ROOM_COUNT => [
                'value' => (int) $room['rooms_taken'],
                'description' => 'Taken rooms count',
            ],

            self::PARAM_FREE_ROOM_COUNT => [
                'value' => (int) ($room['rooms_count'] - $room['rooms_taken']),
                'description' => 'Free rooms count',
            ],

            self::PARAM_FREE_ROOMS_TODAY => [
                'value' => (int) $room['rooms_leaving_today'],
                'description' => 'Rooms freeing today',
            ],

            self::PARAM_FREE => [
                'value' => $free . ' % ',
                'description' => 'Free',
            ],

            self::PARAM_TAKEN => [
                'value' => $taken . ' % ',
                'description' => 'Taken',
            ]
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
     * @param \Krystal\I18n\TranslatorInterface $translator
     * @return array
     */
    public function createRooms(int $langId, int $hotelId, TranslatorInterface $translator) : array
    {
        foreach ($this->createTable($langId, $hotelId) as $floor => $room) {
            $data = ArrayUtils::arrayList($room, 'id', 'name');

            // If floor is not undefined
            if ($floor) {
                $output[sprintf('%s %s', $floor, $translator->translate('Floor'))] = $data;
            } else {
                $output = array_replace($output, $data);
            }
        }

        return $output;
    }
}
