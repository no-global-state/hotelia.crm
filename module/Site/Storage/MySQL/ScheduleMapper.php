<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class ScheduleMapper extends AbstractMapper
{
    /**
     * Resizes an event
     * 
     * @param int $id
     * @param array $data
     * @return boolean
     */
    private function updateById(int $id, array $data) : bool
    {
        return $this->db->update(ReservationMapper::getTableName(), $data)
                        ->whereEquals('id', $id)
                        ->execute();
    }

    /**
     * Checks whether there's an overlap
     * 
     * @param int $roomId
     * @param string $arrival
     * @param string $departure
     * @return boolean
     */
    public function hasOverlap(int $roomId, string $arrival, string $departure) : bool
    {
        $query = sprintf('SELECT COUNT(id) FROM %s WHERE NOT ((departure <= :arrival) OR (arrival >= :departure)) AND id <> :id AND room_id = :room_id', ReservationMapper::getTableName());
        $bindings = [
            ':arrival' => $arrival,
            ':departure' => $departure,
            ':roomId' => $roomId
        ];

        $count = $this->db->raw($query, $bindings)
                          ->queryScalar();

        return $count > 0;
    }

    /**
     * Resizes an event
     * 
     * @param int $id
     * @param string $arrival
     * @param string $departure
     * @return boolean
     */
    public function resize(int $id, string $arrival, string $departure) : bool
    {
        // Data to be updated
        $data = [
            'arrival' => $arrival,
            'departure' => $departure
        ];

        return $this->updateById($id, $data);
    }

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
