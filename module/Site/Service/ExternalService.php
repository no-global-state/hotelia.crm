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
     * Finds all by external user ID
     * 
     * @param int $id External user ID
     * @return array
     */
    public function findAllByExternalId(int $id) : array
    {
        return $this->externalMapper->findAllByExternalId($id);
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
