<?php

/**
 * This file is part of the Hotelia CRM Solution
 * 
 * Copyright (c) No Global State Lab
 * 
 * For the full copyright and license information, please view
 * the license file that was distributed with this source code.
 */

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;
use Krystal\Db\Sql\RawSqlFragment;

final class RoomMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('hotelia_floor_room');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetches room name by its associated ID
     * 
     * @param string $id
     * @return string
     */
    public function fetchNameById($id)
    {
        return $this->findColumnByPk($id, 'name');
    }

    /**
     * Fetch room data by its associated id
     * 
     * @param string $id Room ID
     * @return array
     */
    public function fetchById($id)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            RoomTypeMapper::getFullColumnName('type'),
            FloorMapper::getFullColumnName('name') => 'floor'
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Floor relation
                        ->leftJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        ->whereEquals(self::getFullColumnName($this->getPk()), $id)
                        ->query();
    }

    /**
     * Fetches cleaning data of rooms
     * 
     * @return array
     */
    public function fetchCleaning()
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('floor_id'),
            self::getFullColumnName('type_id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            RoomTypeMapper::getFullColumnName('type'),
            FloorMapper::getFullColumnName('name') => 'floor'
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Floor relation
                        ->leftJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        ->orderBy(array(FloorMapper::getFullColumnName('name')))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Fetch all rooms by associated floor ID
     * 
     * @param string $floorId
     * @return array
     */
    public function fetchAll($floorId)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('floor_id'),
            self::getFullColumnName('type_id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            RoomTypeMapper::getFullColumnName('type'),
            ReservationMapper::getFullColumnName('departure'),

            // Availability indicators (virtual columns)
            new RawSqlFragment('(CURDATE() BETWEEN arrival AND departure) AS taken'),
            new RawSqlFragment('(CURDATE() > departure) AS free'),
            new RawSqlFragment('(CURDATE() = departure) AS leaving_today'),
            new RawSqlFragment('DATEDIFF(departure, CURDATE()) AS left_days'),
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            ReservationMapper::getRawColumn('room_id')
                        )
                        // Remove duplicates in case pre-reservation is done
                        ->rawAnd()
                        ->compare('arrival', '<=', new RawSqlFragment('CURDATE()'))
                        ->whereEquals('floor_id', $floorId)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
