<?php

namespace Site\Service;

use DateTime;
use DateInterval;
use DatePeriod;
use LogicException;
use Site\Storage\MySQL\ReservationMapper;
use Site\Collection\ReservationCollection;
use Krystal\Stdlib\ArrayUtils;

final class ReservationService
{
    const PARAM_TIME_FORMAT = 'Y-m-d';

    /**
     * Any compliant reservation mapper
     * 
     * @var \Site\Storage\MySQL\ReservationMapper
     */
    private $reservationMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ReservationMapper $reservationMapper
     * @return void
     */
    public function __construct(ReservationMapper $reservationMapper)
    {
        $this->reservationMapper = $reservationMapper;
    }

    /**
     * Counts by reservation states
     * 
     * @param int $hotelId
     * @return array
     */
    public function countStates(int $hotelId) : array
    {
        $rows = ArrayUtils::arrayList($this->reservationMapper->countStates($hotelId), 'state', 'count');

        // Add missing values on absence
        foreach ((new ReservationCollection)->getAll() as $key => $value) {
            if (!isset($rows[$key])) {
                $rows[$key] = 0;
            }
        }

        return $rows;
    }

    /**
     * Deletes a reservation by its associated id
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->reservationMapper->deleteById($id);
    }

    /**
     * Fetch reservation info by room ID
     * 
     * @param int $roomId
     * @return array
     */
    public function fetchByRoomId(int $roomId) : array
    {
        return $this->reservationMapper->fetchByRoomId($roomId);
    }

    /**
     * Fetch latest reservations
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchLatest(int $hotelId) : array
    {
        return $this->reservationMapper->fetchLatest($hotelId);
    }

    /**
     * Find reservations
     * 
     * @param int $hotelId
     * @param int $langId Language ID constraint
     * @param string $type Optional room type filter
     * @return array
     */
    public function fetchReservations(int $hotelId, int $langId, $type = null) : array
    {
        $rows = $this->reservationMapper->findReservations($hotelId, $langId, $type);
        return self::parseRooms($rows);
    }

    /**
     * Fetch reservation info by its associated id
     * 
     * @param int $id
     * @return mixed
     */
    public function fetchById(int $id)
    {
        return $this->reservationMapper->fetchById($id);
    }

    /**
     * Save many reservations at once
     * 
     * @param array $reservations
     * @return array IDs of saved reservations
     */
    public function saveMany(array $reservations) : array
    {
        // To be returned
        $ids = [];

        foreach ($reservations as $reservation) {
            $row = $this->save($reservation);
            $ids[] = $row['id'];
        }

        return $ids;
    }

    /**
     * Saves a reservation
     * 
     * @param array $data
     * @return array
     */
    public function save(array $data) : array
    {
        return $this->reservationMapper->persistRow($data);
    }

    /**
     * Checks room availability based on arrival date and its ID
     * 
     * @param string $date Arrival date
     * @param int $roomId Room ID
     * @return boolean
     */
    public function hasAvailability(string $date, int $roomId) : bool
    {
        return $this->reservationMapper->hasAvailability($date, $roomId);
    }

    /**
     * Find reservation dates from collection of rooms and group them
     * 
     * @param string $pk Primary column item name
     * @param array $rows A collection of items to be overated over
     * @param array $columns Columns to be considered
     * @return array
     */
    private static function findReservations(string $pk, array $rows, array $columns) : array
    {
        $fk = 'reservation_id';
        
        $seen = [];

        foreach ($rows as $row) {
            $id = $row[$pk];

            if (!in_array($id, $row)) {
                $seen[$id] = [];
            }

            foreach ($columns as $column) {
                if (!isset($seen[$id][$column])) {
                    $seen[$id][$column] = [];
                }

                $seen[$id][$column][$row[$fk]] = $row[$column];
            }
        }

        return $seen;
    }

    /**
     * Parse raw set of room for format of the chessboard
     * 
     * @param array $rooms
     * @return array
     */
    public static function parseRooms(array $rooms) : array
    {
        $output = [];
        $columns = ['arrival', 'departure'];

        // Find reservations
        $reservations = self::findReservations('id', $rooms, $columns);

        foreach ($rooms as $index => $room) {
            $id = $room['id'];

            if (!isset($output[$id])) {
                // Remove columns in output
                foreach($columns as $column){
                    unset($room[$column]);
                }

                $output[$id] = $room;

                if (isset($reservations[$id])) {
                    $output[$id]['reservations'] = $reservations[$id];
                }

            } else {
                continue;
            }
        }

        return $output;
    }

    /**
     * Returns today format
     * 
     * @return string
     */
    public static function getToday() : string
    {
        return date(self::PARAM_TIME_FORMAT);
    }

    /**
     * Returns random color
     * 
     * @return array
     */
    public static function getRandomColor()
    {
        $colors = self::getColors();
        $key = array_rand($colors);

        return [
            'background' => $key,
            'hover' => $colors[$key]
        ];
    }

    /**
     * Returns a collection of colors for chessboard
     * 
     * @return array
     */
    public static function getColors()
    {
        // Initial => Hover colors
        return [
            '#e68946' => '#ca7436',
            '#cc8315' => '#ad6e10',
            '#72257b' => '#53175a'
        ];
    }

    /**
     * Finds primary key by date within collections
     * 
     * @param string $date
     * @param array $arrival Arrival dates
     * @param array $departures Departure dates
     * @throws \LogicException If count of items in arrivals and departures is different
     * @return string|boolean False on failure
     */
    public static function findPkInDateRanges(string $date, array $arrivals, array $departures)
    {
        $arrivalCount = count($arrivals);
        $departureCount = count($departures);

        if ($arrivalCount !== $departureCount) {
            throw new LogicException('Arrival and departure dates must have the same count of items');
        }

        $targets = array_combine($arrivals, $departures);

        foreach ($targets as $arrival => $departure) {
            if (self::isDateInRange($date, $arrival, $departure)) {
                return array_search($arrival, $arrivals); // Or departures, doesn't matter, they hold the same IDs
            }
        }

        return false;
    }

    /**
     * Checks whether date is in range between two another dates
     * 
     * @param string $target
     * @param string $start
     * @param string $end
     * @return boolean
     */
    private static function isDateInRange($target, $start, $end)
    {
        $date = new DateTime($target);
        $startDate = new DateTime($start);
        $endDate = new DateTime($end);

        return $date >= $startDate && $date <= $endDate;
    }

    /**
     * Creates week range
     * 
     * @param integer $days
     * @return array
     */
    public static function createPeriodRange($days = 6)
    {
        $output = array();

        $start = new DateTime();

        $end = new DateTime();
        $end->modify(sprintf('+%s days', $days));

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            $output[] = array(
                'weekday' => $dt->format('l'),
                'month' => $dt->format('F'),
                'year' => $dt->format('Y'),
                'date' => $dt->format(self::PARAM_TIME_FORMAT),
            );
        }

        return $output;
    }

    /**
     * Gets days difference count
     * 
     * @param string $arrival
     * @param string $departure
     * @return integer
     */
    public static function getDaysDiff(string $arrival, string $departure) : int
    {
        $date1 = new DateTime($arrival);
        $date2 = new DateTime($departure);

        return $date2->diff($date1)->format("%a");
    }

    /**
     * Calculates stay price based on arrival and departure dates
     * 
     * @param string $arrival
     * @param string $departure
     * @param mixed $float One night price
     * @return array
     */
    public static function calculateStayPrice(string $arrival, string $departure, $price) : array
    {
        $days = self::getDaysDiff($arrival, $departure);

        return [
            'days' => $days,
            'price' => round($days * $price)
        ];
    }

    /**
     * Returns reservation dates
     * 
     * @return array
     */
    public static function getReservationDefaultDates()
    {
        $today = date(self::PARAM_TIME_FORMAT);

        return [
            'today' => $today,
            'tomorrow' => self::addOneDay($today)
        ];
    }

    /**
     * Adds one day to current date
     * 
     * @param string $date
     * @return string
     */
    public static function addOneDay($date)
    {
        $date = new DateTime($date);
        $date->modify('+1 day');

        return $date->format(self::PARAM_TIME_FORMAT);
    }
}
