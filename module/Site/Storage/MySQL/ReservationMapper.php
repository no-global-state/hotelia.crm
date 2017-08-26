<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

final class ReservationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('hotelia_reservation');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all records
     * 
     * @return array
     */
    public function fetchAll()
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('room_id'),
            self::getFullColumnName('full_name'),
            self::getFullColumnName('gender'),
            self::getFullColumnName('country'),
            self::getFullColumnName('status'),
            self::getFullColumnName('arrival'),
            self::getFullColumnName('departure'),
            RoomMapper::getFullColumnName('name') => 'room'
        );

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName())
                       ->on()
                       ->equals(
                            self::getFullColumnName('room_id'),
                            RoomMapper::getRawColumn('id')
                       )
                       ->orderBy('id')
                       ->desc();

        return $db->queryAll();
    }
}
