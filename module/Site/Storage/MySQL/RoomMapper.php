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
     * Fetch all rooms by associated floor ID
     * 
     * @param string $floorId
     * @return array
     */
    public function fetchAll($floorId)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('floor_id', $floorId)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
