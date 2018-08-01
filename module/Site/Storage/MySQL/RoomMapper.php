<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;
use Krystal\Db\Sql\QueryBuilder;

final class RoomMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room');
    }

    /**
     * Gets floor count by associated hotel ID
     * 
     * @param int $hotelId
     * @return int
     */
    public function getFloorCount(int $hotelId) : int
    {
        return $this->db->select()
                        ->count(new RawSqlFragment('DISTINCT floor'))
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->queryScalar();
    }

    /**
     * Checks whether room name exists
     * 
     * @param string $name
     * @param int $hotelId
     * @return boolean
     */
    public function nameExists(string $name, int $hotelId) : bool
    {
        return (bool) $this->db->select()
                               ->count('id')
                               ->from(self::getTableName())
                               ->whereEquals('name', $name)
                               ->andWhereEquals('hotel_id', $hotelId)
                               ->queryScalar();
    }

    /**
     * Finds free available rooms based on date range and attached hotel ID
     * 
     * @param integer $langId
     * @param integer $hotelId
     * @param string $arrival
     * @param string $departure
     * @param array $typeIds Optional type ID filters
     * @param array $inventoryIds Inventory ID filters
     * @return array
     */
    public function findFreeRooms(int $langId, int $hotelId, $arrival, $departure, $typeIds = array(), $inventoryIds = array())
    {
        // Columns to be selected
        $columns = array(
            self::column('id'),
            self::column('name'),
            self::column('floor'),
            self::column('persons'),
            self::column('square'),
            self::column('quality'),
            self::column('cleaned'),
            RoomCategoryTranslationMapper::column('name') => 'type',
        );

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room type mapper
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            self::column('type_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Room inventory relation
                        ->leftJoin(RoomInventoryMapper::getTableName(), [
                            RoomInventoryMapper::column('room_id') => self::getRawColumn('id')
                        ])
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::column('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Language ID constraint
                        ->whereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->andWhereNotIn(self::column('id'), new RawSqlFragment($this->createBookingQuery($hotelId, $arrival, $departure)))
                        ->andWhereIn('type_id', $typeIds) // Will not be appended if $typeIds is empty
                        ->andWhereIn(RoomInventoryMapper::column('inventory_id'), $inventoryIds) // Will not be appended if $inventoryIds is empty
                        ->andWhereEquals(self::column('hotel_id'), $hotelId);

        // Ensure all values are matched if provided
        if (!empty($inventoryIds)) {
            $db->having('COUNT', RoomInventoryMapper::column('inventory_id'), '=', count($inventoryIds));
        }

        // Sort by floor names
        return $db->orderBy(self::column('name'))
                  ->queryAll();
    }

    /**
     * Create query that finds non-available rooms
     * 
     * @param integer $hotelId
     * @param string $arrival
     * @param string $departure
     * @return string
     */
    private function createBookingQuery($hotelId, $arrival, $departure)
    {
        // @TODO: Escape these values
        $arrival = sprintf("'%s'", $arrival);
        $departure = sprintf("'%s'", $departure);

        $qb = new QueryBuilder();
        $qb->select('room_id')
           ->from(ReservationMapper::getTableName())
           ->append(' WHERE ')

           // Wrapped expression
           ->openBracket()
           ->compare('arrival', '<=', $arrival)
           ->andWhere('departure', '>=', $arrival)
           ->closeBracket()
           ->rawOr()

           // Wrapped expression
           ->openBracket()
           ->compare('arrival', '<', $arrival)
           ->andWhere('departure', '>=', $departure)
           ->closeBracket()
           ->rawOr()

           // Wrapped expression
           ->openBracket()
           ->compare('arrival', '>=', $arrival)
           ->andWhere('departure', '<', $departure)
           ->closeBracket()

           ->andWhereEquals(ReservationMapper::column('hotel_id'), $hotelId);

        return $qb->getQueryString();
    }

    /**
     * Fetches room name by its associated ID
     * 
     * @param string $id
     * @return string
     */
    public function fetchNameById($id)
    {
        return $this->findColumnByPk($id, 'name');
    }

    /**
     * Fetches today's statistic
     * 
     * @param integer $hotelId
     * @return array
     */
    public function fetchStatistic($hotelId)
    {
        // Columns to be selected
        $columns = array(
            // Availability indicators (virtual columns)
            new RawSqlFragment(sprintf('COUNT(%s) AS rooms_count', self::column('id'))),
            new RawSqlFragment('SUM(CURDATE() BETWEEN arrival AND departure) AS rooms_taken'),
            new RawSqlFragment('SUM(CURDATE() = departure) AS rooms_leaving_today'),
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('id'),
                            ReservationMapper::getRawColumn('room_id')
                        )
                        // Remove duplicates in case pre-reservation is done
                        ->rawAnd()
                        ->compare('arrival', '<=', new RawSqlFragment('CURDATE()'))
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        ->query();
    }

    /**
     * Fetch room data by its associated id
     * 
     * @param int $id Room ID
     * @param int $langId
     * @return array
     */
    public function fetchById(int $id, int $langId)
    {
        // Columns to be selected
        $columns = array(
            self::column('id'),
            self::column('type_id'),
            self::column('persons'),
            self::column('name'),
            self::column('floor'),
            self::column('square'),
            self::column('quality'),
            self::column('cleaned'),
            RoomCategoryTranslationMapper::column('name') => 'type',
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            self::column('type_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::column('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        ->whereEquals(self::column($this->getPk()), $id)
                        // Language ID filter
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->query();
    }

    /**
     * Fetches cleaning data of rooms
     * 
     * @param int $langId
     * @param integer $hotelId
     * @return array
     */
    public function fetchCleaning(int $langId, int $hotelId)
    {
        // Columns to be selected
        $columns = array(
            self::column('id'),
            self::column('type_id'),
            self::column('persons'),
            self::column('name'),
            self::column('floor'),
            self::column('square'),
            self::column('quality'),
            self::column('cleaned'),
            RoomCategoryTranslationMapper::column('name') => 'type',
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::column('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Filter by Hotel ID
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        // Language ID filter
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->orderBy(array(self::column('name')))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Find all rooms by attached hotel ID
     * 
     * @param int $langId
     * @param int $hotelId
     * @param int|null $typeId Optional type ID filter
     * @return array
     */
    public function findAll(int $langId, int $hotelId, $typeId = null) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('type_id'),
            self::column('persons'),
            self::column('name'),
            self::column('floor'),
            self::column('square'),
            self::column('quality'),
            self::column('cleaned'),
            RoomCategoryTranslationMapper::column('name') => 'type',
        ];

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            self::column('type_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::column('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId);

        // Optional type ID filter
        if ($typeId !== null && !empty($typeId)) {
            $db->andWhereEquals(self::column('type_id'), $typeId);
        }

        // Sort in DESC order
        return $db->orderBy(self::column('id'))
                  ->desc()
                  ->queryAll();
    }

    /**
     * Fetch all rooms by associated floor ID
     * 
     * @param int $langId
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $langId, int $hotelId)
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('type_id'),
            self::column('persons'),
            self::column('name'),
            self::column('floor'),
            self::column('square'),
            self::column('quality'),
            self::column('cleaned'),
            RoomCategoryTranslationMapper::column('name') => 'type',
            ReservationMapper::column('departure'),

            // Availability indicators (virtual columns)
            new RawSqlFragment('(CURDATE() BETWEEN arrival AND departure) AS taken'),
            new RawSqlFragment('(CURDATE() > departure) AS free'),
            new RawSqlFragment('(CURDATE() = departure) AS leaving_today'),
            new RawSqlFragment('DATEDIFF(departure, CURDATE()) AS left_days'),
        ];

        $db = $this->db->select($columns, true)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            self::column('type_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::column('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName(), [
                            ReservationMapper::column('room_id') => self::getRawColumn('id')
                        ])
                        // Remove duplicates in case pre-reservation is done
                        ->rawAnd()
                        ->compare('arrival', '<=', new RawSqlFragment('CURDATE()'))
                        ->rawAnd()
                        ->compare('DATEDIFF(departure, CURDATE())', '>=', new RawSqlFragment(0))
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->orderBy(self::column('id'))
                        ->desc();

        return $db->queryAll();
    }
}
