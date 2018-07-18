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
     * Find all booking rows
     * 
     * @return array
     */
    public function findAll() : array
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
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
