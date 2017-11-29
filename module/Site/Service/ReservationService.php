<?php

namespace Site\Service;

use DateTime;
use DateInterval;
use DatePeriod;

class ReservationService
{
    const PARAM_TIME_FORMAT = 'Y-m-d';

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
     * Checks whether date is in range between two another dates
     * 
     * @param string $target
     * @param string $start
     * @param string $end
     * @return boolean
     */
    public static function isDateInRange($target, $start, $end)
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
