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
        $columns = [
            self::column('qty'),
            self::column('guests'),
            RoomCategoryTranslationMapper::column('name') => 'category'
        ];

        return $this->db->select($columns)
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
                        // Constraints
                        ->whereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->andWhereEquals(self::column('booking_id'), $bookingId)
                        ->queryAll();
    }
}
