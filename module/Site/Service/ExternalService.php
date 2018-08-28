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
     * @return string
     */
    private function formatTime(array $item, string $key, string $subject) : string
    {
        $timestamp = strtotime($item[$key]);

        return sprintf('%s, %s %s, %s (%s %s)', 
            date('D', $timestamp), 
            date('d', $timestamp), 
            date('F', $timestamp), 
            date('Y', $timestamp),
            $subject,
            $item[$key]
        );
    }

    /**
     * Find booked hotels by external user ID
     * 
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findHotelsByExternalId(int $id, int $langId) : array
    {
        $bookings = $this->externalMapper->findHotelsByExternalId($id, $langId);

        // Append rooms key
        foreach ($bookings as &$booking) {
            $items = $this->externalMapper->findBookingsByHotelId($booking['id'], $langId);

            // Alter keys for better readability
            foreach ($items as &$item) {
                $item['checkin'] = $this->formatTime($item, 'checkin', 'from');
                $item['checkout'] = $this->formatTime($item, 'checkout', 'to');

                // Unset dates
                unset($item['arrival'], $item['departure']);
            }

            // Append rooms
            $booking['rooms'] = $items;
        }

        return $bookings;
    }

    /**
     * Find all bookings by external user ID
     * 
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findAllByExternalId(int $id, int $langId) : array
    {
        return $this->externalMapper->findAllByExternalId($id, $langId);
    }

    /**
     * Save relation about external user ID and its booking ID
     * 
     * @param int $bookingId
     * @return boolean
     */
    public function saveIfPossible(int $bookingId) : bool
    {
        $userId = $this->getExternalId();

        if ($userId === false) {
            return false;
        }

        return $this->externalMapper->persist([
            'master_id' => $userId,
            'slave_id' => $bookingId
        ]);
    }
}
