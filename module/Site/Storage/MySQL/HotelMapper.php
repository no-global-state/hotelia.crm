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

final class HotelMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Fetch all hotels
     * 
     * @return array
     */
    public function fetchAll()
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->desc()
                        ->asManyToMany(
                            'facilities', 
                            FacilitiyItemMapper::getJunctionTableName(), 
                            self::PARAM_JUNCTION_MASTER_COLUMN, 
                            FacilitiyItemMapper::getTableName(), 
                            'id', 
                            'name' // Columns to be selected in Service table
                        )
                        ->queryAll();
    }
}
