<?php

namespace Site\Service;

use DateTime;
use DateInterval;
use DatePeriod;

class ReservationService
{
    const PARAM_TIME_FORMAT = 'Y-m-d';

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

    /**
     * Creates count
     * 
     * @param array $entity
     * @param int $dailyTax
     * @return array
     */
    public static function createCount(array $entity, $dailyTax)
    {
        return self::getCount($entity['arrival'], $entity['departure'], $entity['room_price'], $dailyTax, $entity['discount']);
    }

    /**
     * Creates count
     * 
     * @param string $arrival
     * @param string $departure
     * @param mixed $price
     * @param int $dailyTax
     * @param mixed $discount
     * @return array
     */
    public static function getCount($arrival, $departure, $price, $dailyTax, $discount)
    {
        $date1 = new DateTime($arrival);
        $date2 = new DateTime($departure);

        $days = $date2->diff($date1)->format("%a");
        $totalPrice = $days * $price;

        if ($discount) {
            // To subtract from total price
            $subtract = ($totalPrice * floatval($discount) / 100);
            $totalPrice -= $subtract;
        }

        return array(
            'days' => $days,
            'discount' => $discount ? $discount : 0,
            'daily_tax' => number_format($days * $dailyTax),
            'price' => number_format($price)
        );
    }
}
