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

final class FloorMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_floor');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all rooms by associated floor ID
     * 
     * @return array
     */
    public function fetchAll()
    {
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('name')
        );

        return $this->db->select($columns)
                        ->count(RoomMapper::getFullColumnName('floor_id'), 'room_count')
                        ->from(self::getTableName())
                        // Room relation
                        ->innerJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            RoomMapper::getRawColumn('floor_id')
                        )
                        ->groupBy($columns)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
