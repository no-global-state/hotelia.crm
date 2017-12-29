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
        return self::getWithPrefix('velveto_floor_room');
    }

    /**
     * Checks whether room name exists
     * 
     * @param string $name
     * @return boolean
     */
    public function nameExists(string $name) : bool
    {
        return $this->valueExists('name', $name);
    }

    /**
     * Finds free available rooms based on date range and attached hotel ID
     * 
     * @param integer $hotelId
     * @param string $arrival
     * @param string $departure
     * @param array $typeIds Optional type ID filters
     * @param array $inventoryIds Inventory ID filters
     * @return array
     */
    public function findFreeRooms($hotelId, $arrival, $departure, $typeIds = array(), $inventoryIds = array())
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('name'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            self::getFullColumnName('description'),
            RoomTypeMapper::getFullColumnName('type'),
            FloorMapper::getFullColumnName('name') => 'floor'
        );

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room type mapper
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Floor relation
                        ->leftJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        // Room inventory relation
                        ->leftJoin(RoomInventoryMapper::getTableName())
                        ->on()
                        ->equals(
                            RoomInventoryMapper::getFullColumnName('room_id'),
                            self::getRawColumn('id')
                        )
                        ->whereEquals('1', '1')
                        ->andWhereNotIn(self::getFullColumnName('id'), new RawSqlFragment($this->createBookingQuery($hotelId, $arrival, $departure)))
                        ->andWhereIn('type_id', $typeIds) // Will not be appended if $typeIds is empty
                        ->andWhereIn(RoomInventoryMapper::getFullColumnName('inventory_id'), $inventoryIds) // Will not be appended if $inventoryIds is empty
                        ->andWhereEquals(self::getFullColumnName('hotel_id'), $hotelId);

        // Ensure all values are matched if provided
        if (!empty($inventoryIds)) {
            $db->having('COUNT', RoomInventoryMapper::getFullColumnName('inventory_id'), '=', count($inventoryIds));
        }

        // Sort by floor names
        return $db->orderBy(FloorMapper::getFullColumnName('name'))
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

           ->andWhereEquals(ReservationMapper::getFullColumnName('hotel_id'), $hotelId);

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
            new RawSqlFragment('COUNT(velveto_floor_room.id) AS rooms_count'),
            new RawSqlFragment('SUM(CURDATE() BETWEEN arrival AND departure) AS rooms_taken'),
            new RawSqlFragment('SUM(CURDATE() = departure) AS rooms_leaving_today'),
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Floor relation
                        ->innerJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            ReservationMapper::getRawColumn('room_id')
                        )
                        // Remove duplicates in case pre-reservation is done
                        ->rawAnd()
                        ->compare('arrival', '<=', new RawSqlFragment('CURDATE()'))
                        ->whereEquals(FloorMapper::getFullColumnName('hotel_id'), $hotelId)
                        ->query();
    }

    /**
     * Fetch room data by its associated id
     * 
     * @param string $id Room ID
     * @return array
     */
    public function fetchById($id)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            self::getFullColumnName('description'),
            RoomTypeMapper::getFullColumnName('type'),
            FloorMapper::getFullColumnName('name') => 'floor'
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Floor relation
                        ->leftJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        ->whereEquals(self::getFullColumnName($this->getPk()), $id)
                        ->query();
    }

    /**
     * Fetches cleaning data of rooms
     * 
     * @param integer $hotelId
     * @return array
     */
    public function fetchCleaning($hotelId)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('floor_id'),
            self::getFullColumnName('type_id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            RoomTypeMapper::getFullColumnName('type'),
            FloorMapper::getFullColumnName('name') => 'floor'
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Floor relation
                        ->leftJoin(FloorMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('floor_id'),
                            FloorMapper::getRawColumn('id')
                        )
                        // Filter by Hotel ID
                        ->whereEquals(FloorMapper::getFullColumnName('hotel_id'), $hotelId)
                        ->orderBy(array(FloorMapper::getFullColumnName('name')))
                        ->desc()
                        ->queryAll();
    }

    /**
     * Fetch all rooms by associated floor ID
     * 
     * @param string $floorId
     * @return array
     */
    public function fetchAll($floorId)
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('floor_id'),
            self::getFullColumnName('type_id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('name'),
            self::getFullColumnName('square'),
            self::getFullColumnName('quality'),
            self::getFullColumnName('cleaned'),
            RoomTypeMapper::getFullColumnName('type'),
            ReservationMapper::getFullColumnName('departure'),

            // Availability indicators (virtual columns)
            new RawSqlFragment('(CURDATE() BETWEEN arrival AND departure) AS taken'),
            new RawSqlFragment('(CURDATE() > departure) AS free'),
            new RawSqlFragment('(CURDATE() = departure) AS leaving_today'),
            new RawSqlFragment('DATEDIFF(departure, CURDATE()) AS left_days'),
        );

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Reservation relation
                        ->leftJoin(ReservationMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            ReservationMapper::getRawColumn('room_id')
                        )
                        // Remove duplicates in case pre-reservation is done
                        ->rawAnd()
                        ->compare('arrival', '<=', new RawSqlFragment('CURDATE()'))
                        ->whereEquals('floor_id', $floorId)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
