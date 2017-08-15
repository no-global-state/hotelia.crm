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

final class RoomTypeMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('hotelia_floor_room_types');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all rooms types
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
