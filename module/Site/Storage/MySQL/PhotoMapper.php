<?php

namespace Site\Storage\MySQL;

final class PhotoMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_photos');
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @return array
     */
    public function fetchAll($hotelId)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->queryAll();
    }
}
