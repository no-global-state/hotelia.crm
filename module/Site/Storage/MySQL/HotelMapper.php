<?php

namespace Site\Storage\MySQL;

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
