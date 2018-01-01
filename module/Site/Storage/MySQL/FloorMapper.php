<?php

namespace Site\Storage\MySQL;

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
     * Returns last floor ID attached to hotel
     * 
     * @param int $hotelId
     * @return int
     */
    public function getLastFloorId(int $hotelId) : int
    {
        return $this->db->select($this->getPk())
                        ->from(static::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->limit(1)
                        ->queryScalar();
    }

    /**
     * Gets total floor count
     * 
     * @param integer $hotelId
     * @return integer
     */
    public function getFloorCount($hotelId)
    {
        return $this->db->select()
                        ->count($this->getPk())
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->queryScalar();
    }

    /**
     * Fetch all rooms by associated floor ID
     * 
     * @param integer $hotelId
     * @return array
     */
    public function fetchAll($hotelId)
    {
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('name')
        );

        return $this->db->select($columns)
                        ->count(RoomMapper::getFullColumnName('floor_id'), 'room_count')
                        ->from(self::getTableName())
                        // Room relation
                        ->leftJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            RoomMapper::getRawColumn('floor_id')
                        )
                        ->whereEquals(self::getFullColumnName('hotel_id'), $hotelId)
                        ->groupBy($columns)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
