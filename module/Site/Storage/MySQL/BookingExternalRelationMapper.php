<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;
use Site\Collection\BookingStatusCollection;

final class BookingExternalRelationMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_external');
    }

    /**
     * Find total bookings by external user ID
     * 
     * @param string $column
     * @param string $value
     * @param int $langId Language ID constraint
     * @return array
     */
    private function findTotalByExternal($column, $value, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            BookingMapper::column('amount'),
            PriceGroupMapper::column('currency'),
            HotelTranslationMapper::column('name') => 'hotel',
            new RawSqlFragment(sprintf('DATE(%s) AS date', BookingMapper::column('datetime'))),
            new RawSqlFragment(sprintf('TIME(%s) AS time', BookingMapper::column('datetime'))),
        ];

        $db = $this->db->select($columns)
                       ->from(BookingMapper::getTableName())
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => BookingMapper::getRawColumn('price_group_id')
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('id') => BookingMapper::getRawColumn('hotel_id')
                       ])
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::column('id') => HotelMapper::getRawColumn('id')
                       ])
                       // External relation
                       ->leftJoin(self::getTableName(), [
                            self::column('slave_id') => BookingMapper::getRawColumn('id')
                       ])
                       // Constraints
                       ->whereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                       ->andWhereEquals($column, $value)
                       ->orderBy(self::column('id'))
                       ->desc();

        return $db->queryAll();
    }

    /**
     * Find total bookings by external user ID
     * 
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findTotalByExternalId(int $id, int $langId) : array
    {
        return $this->findTotalByExternal(self::column('master_id'), $id, $langId);
    }

    /**
     * Find total bookings by external user ID
     * 
     * @param string $serial External serial
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findTotalByExternalSerial($serial, int $langId) : array
    {
        return $this->findTotalByExternal(self::column('serial'), $serial, $langId);
    }

    /**
     * Find bookings by hotel ID
     * 
     * @param int $hotelId Hotel ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findBookingsByHotelId(int $hotelId, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            BookingMapper::column('id'),
            BookingMapper::column('arrival'),
            BookingMapper::column('departure'),
            new RawSqlFragment(sprintf('DATEDIFF(%s, %s) AS nights', BookingMapper::column('departure'), BookingMapper::column('arrival'))),
            BookingMapper::column('status'),
            BookingMapper::column('amount'),
            PriceGroupMapper::column('currency'),
            BookingRoomMapper::column('qty'),
            BookingRoomMapper::column('guests'),
            RoomCategoryTranslationMapper::column('name') => 'room',
            HotelMapper::column('checkin_from') => 'checkin',
            HotelMapper::column('checkout_from') => 'checkout'
        ];

        $db = $this->db->select($columns)
                       ->from(BookingRoomMapper::getTableName())
                       // Booking relation
                       ->leftJoin(BookingMapper::getTableName(), [
                            BookingMapper::column('id') => BookingRoomMapper::getRawColumn('booking_id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => BookingMapper::getRawColumn('price_group_id')
                       ])
                       // Room type relation
                       ->leftJoin(RoomTypeMapper::getTableName(), [
                            RoomTypeMapper::column('id') => BookingRoomMapper::getRawColumn('room_type_id')
                       ])
                       // Room category relation
                       ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::column('id') => RoomTypeMapper::getRawColumn('category_id')
                       ])
                       // Room category translation relation
                       ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('id') => BookingMapper::getRawColumn('hotel_id')
                       ])
                       // Constraints
                       ->whereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                       ->andWhereEquals(BookingMapper::column('hotel_id'), $hotelId)
                       ->orderBy(BookingRoomMapper::column('id'))
                       ->desc();

        return $db->queryAll();
    }

    /**
     * Find booked hotels by external user ID
     * 
     * @param string $column
     * @param string $value
     * @param int $langId Language ID constraint
     * @return array
     */
    private function findHotelsByExternal($column, $value, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            HotelMapper::column('id'),
            HotelMapper::column('rate'),
            HotelMapper::column('phone'),
            HotelMapper::column('email'),
            HotelMapper::column('discount'),
            HotelTranslationMapper::column('name') => 'hotel',
            HotelTranslationMapper::column('address'),
            BookingMapper::column('discount') => 'with_discount'
        ];

        $db = $this->db->select($columns, true)
                       ->from(self::getTableName())
                       // Booking relation
                       ->leftJoin(BookingMapper::getTableName(), [
                           BookingMapper::column('id') => self::getRawColumn('slave_id')
                       ])
                       // Hotel relation
                       ->leftJoin(HotelMapper::getTableName(), [
                           HotelMapper::column('id') => BookingMapper::getRawColumn('hotel_id')
                       ])
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                           HotelTranslationMapper::column('id') => HotelMapper::getRawColumn('id')
                       ])
                       // Constraints
                       ->whereEquals($column, $value)
                       ->andWhereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                       // Order by latest
                       ->orderBy(self::column('id'))
                       ->desc();

        return $db->queryAll();
    }

    /**
     * Find booked hotels by external user ID
     * 
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findHotelsByExternalId(int $id, int $langId) : array
    {
        return $this->findHotelsByExternal(self::column('master_id'), $id, $langId);
    }

    /**
     * Find booked hotels by external user ID
     * 
     * @param string $serial
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findHotelsByExternalSerial($serial, int $langId) : array
    {
        return $this->findHotelsByExternal(self::column('serial'), $serial, $langId);
    }

    /**
     * Find all by external
     * 
     * @param string $column
     * @param string $value
     * @param int $langId Language ID constraint
     * @return array
     */
    private function findAllByExternal($column, $value, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            BookingMapper::column('id'),
            BookingMapper::column('hotel_id'),
            BookingMapper::column('price_group_id'),
            BookingMapper::column('lang_id'),
            HotelTranslationMapper::column('name') => 'hotel',
            BookingMapper::column('datetime'),
            BookingMapper::column('status'),
            BookingMapper::column('arrival'),
            BookingMapper::column('departure'),
            BookingMapper::column('phone'),
            BookingMapper::column('email'),
            BookingMapper::column('comment'),
            BookingMapper::column('near_preferred'),
            BookingMapper::column('amount'),
            BookingMapper::column('token'),
            BookingMapper::column('cancellation_time'),
            PriceGroupMapper::column('name') => 'price_group',
            PriceGroupMapper::column('currency'),
        ];

        $db = $this->db->select($columns)
                       ->from(BookingMapper::getTableName())
                       // Relational constraint
                       ->innerJoin(self::getTableName(), [
                            self::column('slave_id') => BookingMapper::getRawColumn('id')
                       ])
                       // Hotel relation
                       ->innerJoin(HotelMapper::getTableName(), [
                            HotelMapper::column('id') => BookingMapper::getRawColumn('hotel_id')
                       ])
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::column('id') => HotelMapper::getRawColumn('id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            BookingMapper::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                       ])
                       // Constraints
                       ->whereEquals($column, $value)
                       ->andWhereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                       ->andWhereNotEquals(BookingMapper::column('status'), BookingStatusCollection::STATUS_TEMPORARY);

        return $db->queryAll();
    }

    /**
     * Find all bookings by external user ID
     * 
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findAllByExternalId(int $id, int $langId) : array
    {
        return $this->findAllByExternal(self::column('master_id'), $id, $langId);
    }

    /**
     * Find all bookings by external user ID
     * 
     * @param string $serial External serial
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findAllByExternalSerial(int $serial, int $langId) : array
    {
        return $this->findAllByExternal(self::column('serial'), $serial);
    }
}
