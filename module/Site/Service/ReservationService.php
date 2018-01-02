<?php

namespace Site\Service;

use DateTime;
use DateInterval;
use DatePeriod;
use LogicException;

class ReservationService
{
    const PARAM_TIME_FORMAT = 'Y-m-d';

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

                $seen[$id][$column][] = $row[$column];
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
     * Checks whether two dates
     * 
     * @param string $date
     * @param array $arrival Arrival dates
     * @param array $departures Departure dates
     * @throws \LogicException If count of items in arrivals and departures is different
     * @return boolean
     */
    public static function isDateInRanges(string $date, array $arrivals, array $departures) : bool
    {
        $arrivalCount = count($arrivals);
        $departureCount = count($departures);

        if ($arrivalCount !== $departureCount) {
            throw new LogicException('Arrival and departure dates must have the same count of items');
        }

        $count = $arrivalCount; // Or $departureCount, it doesn't matter because their count is equal

        for ($i = 0; $i < $count; ++$i) {
            if (self::isDateInRange($date, $arrivals[$i], $departures[$i])) {
                return true;
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
                'date' => $dt->format(self::PARAM_TIME_FORMAT),
            );
        }

        return $output;
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
