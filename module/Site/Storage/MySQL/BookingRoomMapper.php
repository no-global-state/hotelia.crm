<?php

namespace Site\Storage\MySQL;

final class BookingRoomMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_bookings_rooms');
    }

    /**
     * Find details by booking ID
     * 
     * @param int $bookingId
     * @param int $langId
     * @return array
     */
    public function findDetailsByBookingId(int $bookingId, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('qty'),
            self::column('guests'),
            RoomCategoryTranslationMapper::column('name') => 'category',
            ReservationMapper::column('room_id')
        ];

        $db = $this->db->select($columns, true)
                        ->from(self::getTableName())
                        // Room type relation
                        ->innerJoin(RoomTypeMapper::getTableName(), [
                            RoomTypeMapper::column('id') => self::getRawColumn('room_type_id')
                        ])
                        // Category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::column('id') => RoomTypeMapper::getRawColumn('category_id')
                        ])
                        // Category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room Relation
                        ->leftJoin(RoomMapper::getTableName(), [
                            RoomMapper::column('type_id') => self::getRawColumn('room_type_id')
                        ])
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName(), [
                            ReservationMapper::column('room_id') => RoomMapper::getRawColumn('id')
                        ])
                        // Junction relation
                        ->leftJoin(BookingReservationRelation::getTableName(), [
                            BookingReservationRelation::column('master_id') => self::getRawColumn('booking_id'),
                            BookingReservationRelation::column('slave_id') => ReservationMapper::getRawColumn('id')
                        ])
                        // Constraints
                        ->whereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->andWhereEquals(self::column('booking_id'), $bookingId);
                        
        return $db->queryAll();
    }
}
