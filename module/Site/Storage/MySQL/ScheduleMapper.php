<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class ScheduleMapper extends AbstractMapper
{
    /**
     * Find all events by hotel it and date ranges
     * 
     * @param int $hotelId
     * @param string $arrival
     * @param string $departure
     * @return array
     */
    public function findEvents(int $hotelId, string $arrival, string $departure) : array
    {
        $query = sprintf('SELECT * FROM %s WHERE NOT ((departure <= :arrival) OR (arrival >= :departure)) AND hotel_id = :hotelId', ReservationMapper::getTableName());
        $bindings = [
            ':arrival' => $arrival,
            ':departure' => $departure,
            ':hotelId' => $hotelId
        ];

        return $this->db->raw($query, $bindings)
                        ->queryAll();
    }
}
