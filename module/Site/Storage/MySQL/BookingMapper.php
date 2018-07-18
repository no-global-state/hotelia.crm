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
     * Updates status by token
     * 
     * @param string $token
     * @param int $status
     * @return boolean Depending on success
     */
    public function updateStatusByToken(string $token, int $status) : bool
    {
        // Affected row count
        $rowCount = $this->db->update(self::getTableName(), ['status' => $status])
                             ->whereEquals('token', $token)
                             ->execute(true);

        return (bool) $rowCount;
    }

    /**
     * Count rows by status code
     * 
     * @param int $hotelId Attached hotel ID
     * @param int $status Status code
     * @return int
     */
    public function countByStatus(int $hotelId, int $status) : int
    {
        return $this->db->select()
                        ->count($this->getPk())
                        ->from(self::getTableName())
                        ->whereEquals('status', $status)
                        ->andWhereEquals('hotel_id', $hotelId)
                        ->queryScalar();
    }

    /**
     * Find all booking rows
     * 
     * @param int $hotelId Attached hotel ID
     * @return array
     */
    public function findAll(int $hotelId) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }

    /**
     * Find rows by status
     * 
     * @param int $hotelId Attached hotel ID
     * @param int $status Status code
     * @return array
     */
    public function findByStatus(int $hotelId, int $status) : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals('status', $status)
                        ->andWhereEquals('hotel_id', $hotelId)
                        ->queryAll();
    }
}
