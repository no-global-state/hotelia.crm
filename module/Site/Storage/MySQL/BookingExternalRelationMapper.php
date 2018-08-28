<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

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
     * @param int $id External user ID
     * @param int $langId Language ID constraint
     * @return array
     */
    public function findHotelsByExternalId(int $id, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            HotelMapper::column('id'),
            HotelMapper::column('rate'),
            HotelMapper::column('phone'),
            HotelMapper::column('email'),
            HotelTranslationMapper::column('name') => 'hotel',
            HotelTranslationMapper::column('address'),
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
                       ->whereEquals(self::column('master_id'), $id)
                       ->andWhereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                       // Order by latest
                       ->orderBy(self::column('id'))
                       ->desc();

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
                       ->whereEquals(self::column('master_id'), $id)
                       ->andWhereEquals(HotelTranslationMapper::column('lang_id'), $langId);

        return $db->queryAll();
    }
}
