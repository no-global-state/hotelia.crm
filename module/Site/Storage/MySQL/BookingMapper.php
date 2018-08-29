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
            self::column('lang_id'),
            self::column('datetime'),
            self::column('status'),
            self::column('arrival'),
            self::column('departure'),
            self::column('phone'),
            self::column('email'),
            self::column('comment'),
            self::column('near_preferred'),
            self::column('amount'),
            self::column('token'),
            self::column('discount'),
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
     * Updates status by column and value
     * 
     * @param string $column
     * @param string $value
     * @param int $status
     * @return boolean Depending on success
     */
    private function updateStatusByColumn(string $column, string $value, int $status) : bool
    {
        // Affected row count
        $rowCount = $this->db->update(self::getTableName(), ['status' => $status])
                             ->whereEquals($column, $value)
                             ->execute(true);

        return (bool) $rowCount;
    }

    /**
     * Inserts relation
     * 
     * @param int $bookingId
     * @param array $ids Reservation IDs
     * @return boolean
     */
    public function insertRelation(int $bookingId, array $ids) : bool
    {
        return $this->insertIntoJunction(BookingReservationRelation::getTableName(), $bookingId, $ids);
    }

    /**
     * Updates status by booking ID
     * 
     * @param int $id
     * @param int $status
     * @return boolean Depending on success
     */
    public function updateStatusById(int $id, int $status) : bool
    {
        return $this->updateStatusByColumn('id', $id, $status);
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
        return $this->updateStatusByColumn('token', $token, $status);
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
     * Finds only cancellation-related data by its token
     * 
     * @param string $token
     * @return array
     */
    public function findCancelationDataByToken(string $token)
    {
        // Columns to be selected
        $columns = [
            HotelMapper::column('penality_enabled'),
            HotelMapper::column('penality_not_taken_after'),
            HotelMapper::column('penality_not_later_arrival'),
            self::column('datetime'),
            self::column('arrival'),
            self::column('status'),
        ];

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('id') => self::getRawColumn('hotel_id')
                       ])
                       ->whereEquals(self::column('token'), $token);

        return $db->query();
    }

    /**
     * Find booking row by its associated token
     * 
     * @param string $token
     * @return array
     */
    public function findByToken(string $token)
    {
        return $this->createSharedSelect()
                    ->whereEquals(self::column('token'), $token)
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
