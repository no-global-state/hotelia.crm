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
     * Creates shared select
     * 
     * @return \Krystal\Db\Sql\Db
     */
    private function createSharedSelect()
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('hotel_id'),
            self::column('price_group_id'),
            self::column('datetime'),
            self::column('status'),
            self::column('arrival'),
            self::column('departure'),
            self::column('mobile'),
            self::column('email'),
            self::column('comment'),
            self::column('near_preferred'),
            self::column('amount'),
            self::column('token'),
            PriceGroupMapper::column('name') => 'price_group',
            PriceGroupMapper::column('currency'),
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            self::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                        ]);
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
     * Find booking row by its associated ID
     * 
     * @param int $id Booking ID
     * @return array
     */
    public function findById(int $id)
    {
        return $this->createSharedSelect()
                    ->whereEquals(self::column('id'), $id)
                    ->query();
    }

    /**
     * Find all booking rows
     * 
     * @param int $hotelId Attached hotel ID
     * @return array
     */
    public function findAll(int $hotelId) : array
    {
        return $this->createSharedSelect()
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
        return $this->createSharedSelect()
                    ->whereEquals('status', $status)
                    ->andWhereEquals('hotel_id', $hotelId)
                    ->queryAll();
    }
}
