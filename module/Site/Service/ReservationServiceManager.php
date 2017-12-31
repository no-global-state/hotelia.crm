<?php

namespace Site\Service;

use Site\Storage\MySQL\ReservationServiceMapper;
use Krystal\Stdlib\ArrayUtils;

final class ReservationServiceManager
{
    /**
     * Any compliant reservation mapper
     * 
     * @var \Site\Storage\MySQL\ReservationServiceMapper
     */
    private $reservationServiceMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\ReservationServiceMapper $reservationServiceMapper
     * @return void
     */
    public function __construct(ReservationServiceMapper $reservationServiceMapper)
    {
        $this->reservationServiceMapper = $reservationServiceMapper;
    }

    /**
     * Saves reservation service
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        // Append counted price
        $input['price'] = floatval($input['qty']) * floatval($input['rate']);

        return $this->reservationServiceMapper->persist($input);
    }

    /**
     * Fetches reservation service by its ID
     * 
     * @param int $id
     * @return array
     */
    public function fetchById(int $id)
    {
        return $this->reservationServiceMapper->findByPk($id);
    }

    /**
     * Delete reservation service by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->reservationServiceMapper->deleteByPk($id);
    }

    /**
     * Finds currency by reservation id
     * 
     * @param int $id Reservation ID
     * @return string
     */
    public function findCurrencyByReservationId(int $id) : string
    {
        return $this->reservationServiceMapper->findCurrencyByReservationId($id);
    }

    /**
     * Find all services attached to reservation id
     * 
     * @param int $id Reservation ID
     * @return array
     */
    public function findOptionsByReservationId(int $id) : array
    {
        return $this->reservationServiceMapper->findOptionsByReservationId($id);
    }

    /**
     * Find all by reservation ID
     * 
     * @param int $id Reservation ID
     * @return array
     */
    public function findAllByReservationId(int $id) : array
    {
        $services = $this->reservationServiceMapper->findAllByReservationId($id);
        $sum = ArrayUtils::columnSum($services, ['price']);

        return [
            'services' => $services,
            'sum' => $sum['price']
        ];
    }
}
