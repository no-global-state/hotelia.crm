<?php

namespace Site\Service;

use Site\Storage\MySQL\ScheduleMapper;
use Krystal\Db\Filter\InputDecorator;

final class ScheduleService
{
    /**
     * Schedule mapper
     * 
     * @var \Site\Storage\MySQL\ScheduleMapper
     */
    private $scheduleMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ScheduleMapper $scheduleMapper
     * @return void
     */
    public function __construct(ScheduleMapper $scheduleMapper)
    {
        $this->scheduleMapper = $scheduleMapper;
    }

    /**
     * Fixes date format
     * 
     * @param string $date
     * @return string
     */
    private static function fixDateFormat(string $date) : string
    {
        return substr($date, 0, 10);
    }

    /**
     * Creates empty entity
     * 
     * @param int $roomId
     * @param string $arrival
     * @param string $departure
     * @return \Krystal\Db\Filter\InputDecorator
     */
    public function createEntity(int $roomId, string $arrival, string $departure) : InputDecorator
    {
        // Fix formats
        $arrival = self::fixDateFormat($arrival);
        $departure = self::fixDateFormat($departure);

        $entity = new InputDecorator();

        $entity['room_id'] = $roomId;
        $entity['arrival'] = $arrival;
        $entity['departure'] = $departure;

        return $entity;
    }

    /**
     * Find all events
     * 
     * @param int $hotelId
     * @param string $arrival
     * @param string $departure
     * @return array
     */
    public function findEvents(int $hotelId, string $arrival, string $departure) : array
    {
        $arrival = self::fixDateFormat($arrival);
        $departure = self::fixDateFormat($departure);

        $output = [];

        $rows = $this->scheduleMapper->findEvents($hotelId, $arrival, $departure);

        foreach ($rows as $row) {
            $output[] = [
                'id' => $row['id'],
                'text' => $row['full_name'],
                'start' => $row['arrival'] . ' 12:00:00',
                'end' => $row['departure'] . ' 12:00:00',
                'resource' => $row['room_id'],
                'bubbleHtml' => '',
                'status' => '',
                'paid' => '50%'
            ];
        }

        return $output;
    }

    /**
     * Resizes an event
     * 
     * @param int $id Reservation ID
     * @param string $arrival
     * @param string $departure
     * @return boolean
     */
    public function resize(int $id, string $arrival, string $departure) : bool
    {
        $arrival = self::fixDateFormat($arrival);
        $departure = self::fixDateFormat($departure);

        return $this->scheduleMapper->resize($id, $arrival, $departure);
    }

    /**
     * Moves an event
     * 
     * @param int $id
     * @param int $roomId
     * @param string $arrival
     * @param string $departure
     * @return boolean
     */
    public function move(int $id, int $roomId, string $arrival, string $departure) : bool
    {
        $arrival = self::fixDateFormat($arrival);
        $departure = self::fixDateFormat($departure);

        if ($this->scheduleMapper->hasOverlap($id, $roomId, $arrival, $departure)) {
            return false;
        } else {
            $this->scheduleMapper->move($id, $roomId, $arrival, $departure);
            return true;
        }
    }
}
