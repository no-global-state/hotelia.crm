<?php

namespace Site\Storage\MySQL;

final class ServiceMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_services');
    }

    /**
     * Fetch all services
     * 
     * @param integer $hotelId
     * @return array
     */
    public function fetchAll($hotelId)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
