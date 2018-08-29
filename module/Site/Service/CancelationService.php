<?php

namespace Site\Service;

use DateTime;
use DateInterval;

final class CancelationService
{
    /**
     * Booking date and time
     * 
     * @var string
     */
    private $bookingTime;

    /**
     * Arrival date
     * 
     * @var string
     */
    private $arrival;

    /**
     * State initialization
     * 
     * @param string $bookingTime
     * @param string $arrival
     * @param string $now Current time
     * @return void
     */
    public function __construct(string $bookingTime, string $arrival, $now = 'now')
    {
        $this->bookingTime = $bookingTime;
        $this->arrival = $arrival;
        $this->now = new DateTime($now);
    }

    /**
     * Converts DateInterval to seconds
     * 
     * @param \DateInterval $dateInterval
     * @return int
     */
    private function intervalToSeconds(DateInterval $dateInterval)
    {
        $days = $dateInterval->format('%a');
        $hours = $dateInterval->format('%h');
        $mins = $dateInterval->format('%i');
        $secs = $dateInterval->format('%s');

        return ($days * 24 * 60 * 60) + ($hours * 60 * 60) + ($mins * 60) + $secs;
    }

    /**
     * Creates DateInterval object
     * 
     * @param string $time
     * @return \DateInterval
     */
    private function createInterval(string $time)
    {
        $target = new DateTime($time);
        $interval = $target->diff($this->now);

        return $interval;
    }
    
    /**
     * Checks whether booking can be canceled
     * 
     * @param $x First range
     * @param $y Second $range
     * @return boolean
     */
    public function canCancel($x, $y)
    {
        $freeCancelation = $this->isFreeCancelation($x);

        // First check
        if ($freeCancelation === true) {
            return true;
        } else {
            // Last chance
            return !$this->isLateToCancel($y);
        }
    }

    /**
     * Checks whether cancellation is free
     * 
     * @param int $hours
     * @return boolean
     */
    public function isFreeCancelation(int $hours) : bool
    {
        $interval = $this->createInterval($this->bookingTime);
        $secs = $this->intervalToSeconds($interval);

        return $secs <= ($hours * 3600);
    }

    /**
     * Checks whether it's not too late to cancel booking
     * 
     * @param int $hours
     * @return boolean
     */
    public function isLateToCancel(int $hours) : bool
    {
        $interval = $this->createInterval($this->arrival);
        $secs = $this->intervalToSeconds($interval);

        return $secs <= ($hours * 3600);
    }
}