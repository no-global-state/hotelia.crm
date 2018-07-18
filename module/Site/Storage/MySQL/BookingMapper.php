<?php

namespace Site\Storage\MySQL;

final class BookingMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings');
    }

    /**
     * Count rows by status code
     * 
     * @param int $status Status code
     * @return int
     */
    public function countByStatus(int $status) : int
    {
        return $this->db->select()
                        ->count($this->getPk())
                        ->from(self::getTableName())
                        ->whereEquals('status', $status)
                        ->queryScalar();
    }

    /**
     * Find rows by status
     * 
     * @param int $status Status code
     * @return array
     */
    public function findByStatus(int $status) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('status', $status)
                        ->queryAll();
    }
}
