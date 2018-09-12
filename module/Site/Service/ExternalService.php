<?php

namespace Site\Service;

use Krystal\Http\Client\CurlHttplCrawler;
use Site\Storage\MySQL\BookingExternalRelationMapper;

final class ExternalService
{
    /**
     * External mapper
     * 
     * @var \Site\Storage\MySQL\BookingExternalRelationMapper
     */
    private $externalMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\BookingExternalRelationMapper $externalMapper
     * @return void
     */
    public function __construct(BookingExternalRelationMapper $externalMapper)
    {
        $this->externalMapper = $externalMapper;
    }

    /**
     * Maps through external language ID and returns attached one
     * 
     * @param int $id External language ID
     * @return int
     */
    public static function internalLangId(int $id)
    {
        $map = array_flip($_ENV['languages']);

        return isset($map[$id]) ? $map[$id] : 2;
    }

    /**
     * Maps through external language ID and returns attached one
     * 
     * @param int $id External language ID
     * @return int
     */
    public static function externalLangId(int $id)
    {
        $map = $_ENV['languages'];

        return isset($map[$id]) ? $map[$id] : 2;
    }

    /**
     * Query external service to grab external user ID
     * 
     * @return void
     */
    private function queryExternalServer()
    {
        $response = (new CurlHttplCrawler)->get($_ENV['externalAuth']);

        return json_decode($response, true);
    }

    /**
     * Checks whether external user is logged in
     * 
     * @return mixed
     */
    private function getExternalId()
    {
        $data = $this->queryExternalServer();

        // Empty means, that non-logged in
        if (!$data || !isset($data['id']) ) {
            return false;
        } else {
            return $data['id'];
        }
    }

    /**
     * Formats raw date into human readable format
     * 
     * @param array $item
     * @param string $key Inner key of $item
     * @param string $subject
     * @param \Site\Service\Dictionary $dictionary Dictionary to translate strings
     * @return string
     */
    private function formatTime(array $item, string $key, string $subject, Dictionary $dictionary) : string
    {
        $timestamp = strtotime($item[$key]);

        return sprintf('%s, %s %s, %s (%s %s)', 
            $dictionary(date('D', $timestamp)), 
            date('d', $timestamp), 
            $dictionary(date('F', $timestamp)), 
            date('Y', $timestamp),
            $dictionary($subject),
            $item[$key]
        );
    }

    /**
     * Format items
     * 
     * @param array $items
     * @param \Site\Service\Dictionary $dictionary Dictionary to translate strings
     * @return array
     */
    private function formatItems(array $items, Dictionary $dictionary) : array
    {
        // Alter keys for better readability
        foreach ($items as &$item) {
            $item['checkin'] = $this->formatTime($item, 'checkin', 'FROM', $dictionary);
            $item['checkout'] = $this->formatTime($item, 'checkout', 'UPTO', $dictionary);

            // Unset dates
            unset($item['arrival'], $item['departure']);

            // Now format the price
            $item['price'] = sprintf('%s %s', number_format($item['amount']), $item['currency']);
            // And unset used ones
            unset($item['amount'], $item['currency']);

            // Now format title
            $item['title'] = sprintf('%s %s, %s %s', $item['nights'], $dictionary('NIGHTS'), $item['qty'], $dictionary('ROOMS'));
            // And unset used ones
            unset($item['nights'], $item['qty']);
        }

        return $items;
    }

    /**
     * Find booked hotels by external user ID
     * 
     * @param mixed $target External user ID or serial
     * @param int $langId Language ID constraint
     * @param \Site\Service\Dictionary $dictionary Dictionary to translate strings
     * @return array
     */
    public function findHotelsByExternal($target, int $langId, Dictionary $dictionary) : array
    {
        if (is_numeric($target)) {
            $bookings = $this->externalMapper->findHotelsByExternalId($target, $langId);
        } else {
            $bookings = $this->externalMapper->findHotelsByExternalSerial($target, $langId);
        }

        // Append rooms key
        foreach ($bookings as &$booking) {
            $items = $this->externalMapper->findBookingsByHotelId($booking['id'], $langId);
            $items = $this->formatItems($items, $dictionary);

            // Append rooms
            $booking['rooms'] = $items;
        }

        return $bookings;
    }

    /**
     * Find total bookings by external user ID
     * 
     * @param mixed $target External user ID or serial
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findTotalByExternal($target, int $langId)
    {
        if (is_numeric($target)) {
            $rows = $this->externalMapper->findTotalByExternalId($target, $langId);
        } else {
            $rows = $this->externalMapper->findTotalByExternalSerial($target, $langId);
        }

        foreach ($rows as &$row) {
            // Append price
            $row['price'] = sprintf('%s %s', number_format($row['amount']), $row['currency']);
            unset($row['amount'], $row['currency']);
        }

        return $rows;
    }

    /**
     * Find all bookings by external user ID
     * 
     * @param mixed $target External user ID or serial
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findAllByExternal($target, int $langId) : array
    {
        if (is_numeric($target)) {
            $bookings = $this->externalMapper->findAllByExternalId($target, $langId);
        } else {
            $bookings = $this->externalMapper->findAllByExternalSerial($target, $langId);
        }

        // Append night count
        foreach ($bookings as &$booking) {
            $booking['nights'] = ReservationService::getDaysDiff($booking['arrival'], $booking['departure']);
        }

        return $bookings;
    }

    /**
     * Save relation about external user ID and its booking ID
     * 
     * @param int $bookingId
     * @param mixed $userId User ID in case needs to be overridden
     * @param mixed Optional serial
     * @return boolean
     */
    public function saveIfPossible(int $bookingId, $userId = null, $serial = '') : bool
    {
        if ($userId === null) {
            $userId = $this->getExternalId();

            if ($userId === false) {
                return false;
            }
        }

        return $this->externalMapper->persist([
            'master_id' => $userId,
            'slave_id' => $bookingId,
            'serial' => $serial
        ]);
    }
}
