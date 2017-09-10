<?php

namespace Site\Service;

use DateTime;

class ReservationService
{
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

        return $date->format('Y-m-d');
    }

    /**
     * Creates count
     * 
     * @param array $entity
     * @return array
     */
    public static function createCount(array $entity)
    {
        return self::getCount($entity['arrival'], $entity['departure'], $entity['room_price'], $entity['discount']);
    }

    /**
     * Creates count
     * 
     * @param string $arrival
     * @param string $departure
     * @param mixed $price
     * @param mixed $discount
     * @return array
     */
    public static function getCount($arrival, $departure, $price, $discount)
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
            'price' => number_format($price)
        );
    }
}
