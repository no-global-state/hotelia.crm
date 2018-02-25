<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;
use Krystal\Db\Sql\RawBinding;
use Krystal\Db\Filter\FilterableServiceInterface;
use Krystal\Stdlib\ArrayUtils;
use Closure;

final class ReservationMapper extends AbstractMapper implements FilterableServiceInterface
{
    const PARAM_COLUMN_ATTACHED = 'services';

    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reservation');
    }

    /**
     * {@inheritDoc}
     */
    public static function getJunctionTableName()
    {
        return self::getWithPrefix('velveto_reservation_services');
    }

    /**
     * Returns shared columns to be selected
     * 
     * @return array
     */
    private function getSharedColumns()
    {
        // Columns to be selected
        return array(
            self::getFullColumnName('id'),
            self::getFullColumnName('hotel_id'),
            self::getFullColumnName('room_id'),
            self::getFullColumnName('payment_system_id'),
            self::getFullColumnName('price_group_id'),
            self::getFullColumnName('full_name'),
            self::getFullColumnName('gender'),
            self::getFullColumnName('country'),
            self::getFullColumnName('status'),
            self::getFullColumnName('phone'),
            self::getFullColumnName('email'),
            self::getFullColumnName('passport'),
            self::getFullColumnName('discount'),
            self::getFullColumnName('state'),
            self::getFullColumnName('source'),
            self::getFullColumnName('purpose'),
            self::getFullColumnName('legal_status'),
            self::getFullColumnName('arrival'),
            self::getFullColumnName('departure'),
            new RawSqlFragment('ABS(DATEDIFF(departure, arrival)) AS days'),
            self::getFullColumnName('comment'),
            self::getFullColumnName('company'),
            self::getFullColumnName('tax'),
            self::getFullColumnName('price'),
            RoomMapper::getFullColumnName('name') => 'room'
        );
    }

    /**
     * Finds full name of reservation ID
     * 
     * @param int $id Reservation ID
     * @return string
     */
    public function findFullNameById(int $id) : string
    {
        return $this->findColumnByPk($id, 'full_name');
    }

    /**
     * Gets sum count (price, tax, id) based on period
     * 
     * @param array $values
     * @param string $func SQL function
     * @return array
     */
    public function getSumCount(int $year, array $months, array $roomIds) : array
    {
        return $this->db->select()
                        // Calculate functions
                        ->sum('price', 'price')
                        ->sum('tax', 'tax')
                        ->count('id', 'id')
                        ->from(self::getTableName())
                        ->whereIn('MONTH(arrival)', new RawBinding($months))
                        ->andWhereIn('room_id', $roomIds)
                        ->andWhereEquals('YEAR(arrival)', $year)
                        ->query();
    }

    /**
     * Checks room availability based on arrival date and its ID
     * 
     * @param string $date Arrival date
     * @param int $roomId Room ID
     * @return boolean
     */
    public function hasAvailability(string $date, int $roomId) : bool
    {
        $result = $this->db->select()
                        ->count($this->getPk())
                        ->from(self::getTableName())
                        ->where('arrival', '<=', $date)
                        ->andWhere('departure', '>=', $date)
                        ->andWhere('room_id', '=', $roomId)
                        ->queryScalar();

        return boolval(!$result);
    }

    /**
     * Find reservations
     * 
     * @param string $type Optional room type filter
     * @param int $hotelId
     * @return array
     */
    public function findReservations(int $hotelId, $type = null) : array
    {
        // Columns to be selected
        $columns = array(
            RoomMapper::getFullColumnName('id'),
            RoomMapper::getFullColumnName('name'),
            RoomCategoryTranslationMapper::getFullColumnName('name') => 'type',
            self::getFullColumnName('arrival'),
            self::getFullColumnName('departure'),
            self::getFullColumnName('id') => 'reservation_id'
        );

        $db = $this->db->select($columns)
                        ->from(RoomMapper::getTableName())
                        // Reservation relation
                        ->leftJoin(self::getTableName(), [
                            self::getFullColumnName('room_id') => RoomMapper::getRawColumn('id')
                        ])
                        // Room type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            RoomMapper::getFullColumnName('type_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomTypeMapper::getFullColumnName('category_id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(RoomMapper::getFullColumnName('hotel_id'), $hotelId);

        // If type is provided, the filter by its ID
        if ($type != null) {
            $db->andWhereEquals(RoomTypeMapper::getFullColumnName('id'), $type);
        }

        // Sort by name
        return $db->orderBy(RoomMapper::getFullColumnName('name'))
                  ->queryAll();
    }

    /**
     * Adds a reservation
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function insert(array $input)
    {
        $this->persist(ArrayUtils::arrayWithout($input, array(self::PARAM_COLUMN_ATTACHED)));
        $id = $this->getLastId();

        // Insert relational posts if provided
        if (isset($input[self::PARAM_COLUMN_ATTACHED])) {
            $this->insertIntoJunction(self::getJunctionTableName(), $id, $input[self::PARAM_COLUMN_ATTACHED]);
        }

        return true;
    }

    /**
     * Updates a reservation
     * 
     * @param array $input Raw input data
     * @return boolean
     */
    public function update(array $input)
    {
        // Synchronize relations if provided
        if (isset($input[self::PARAM_COLUMN_ATTACHED])) {
            $this->syncWithJunction(self::getJunctionTableName(), $input[$this->getPk()], $input[self::PARAM_COLUMN_ATTACHED]);
        } else {
            $this->removeFromJunction(self::getJunctionTableName(), $input[$this->getPk()]);
        }

        return $this->persist(ArrayUtils::arrayWithout($input, array(self::PARAM_COLUMN_ATTACHED)));
    }

    /**
     * Deletes a reservation by its associated id
     * 
     * @param string $id Post id
     * @return boolean
     */
    public function deleteById($id)
    {
        $this->removeFromJunction(self::getJunctionTableName(), $id);
        return $this->deleteByPk($id);
    }

    /**
     * Finds a row by constraint
     * 
     * @param \Closure $visitor
     * @return array
     */
    private function findByConstraint(Closure $visitor)
    {
        $columns = array_merge($this->getSharedColumns(), array(
            PriceGroupMapper::getFullColumnName('currency'),
            PaymentSystemMapper::getFullColumnName('name') => 'payment_system'
        ));

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room relation
                        ->leftJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('room_id'),
                            RoomMapper::getRawColumn('id')
                        )
                        // Room type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            RoomMapper::getFullColumnName('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Price group mapper
                        ->leftJoin(PriceGroupMapper::getTableName())
                        ->on()
                        ->equals(
                            PriceGroupMapper::getFullColumnName('id'),
                            self::getRawColumn('price_group_id')
                        )
                        // Payment system relation
                        ->leftJoin(PaymentSystemMapper::getTableName())
                        ->on()
                        ->equals(
                            PaymentSystemMapper::getFullColumnName('id'),
                            self::getRawColumn('payment_system_id')
                        );

                        // Apply by reference
                        $visitor($db);

        // Service relation
        $db->asManyToMany(
            self::PARAM_COLUMN_ATTACHED, 
            self::getJunctionTableName(), 
            self::PARAM_JUNCTION_MASTER_COLUMN, 
            ServiceMapper::getTableName(), 
            'id', 
            '*' // Columns to be selected in Service table
        );

        return $db->query();
    }

    /**
     * Fetches by room ID
     * 
     * @param string $roomId
     * @return array
     */
    public function fetchByRoomId($roomId)
    {
        return $this->findByConstraint(function($db) use ($roomId){
            $db->whereEquals(self::getFullColumnName('room_id'), $roomId)
               ->andWhere(self::getFullColumnName('departure'), '>=', new RawSqlFragment('CURDATE()'));
        });
    }

    /**
     * Fetches reservation by its ID
     * 
     * @param string $id
     * @return array
     */
    public function fetchById($id)
    {
        return $this->findByConstraint(function($db) use ($id){
            $db->whereEquals(self::getFullColumnName($this->getPk()), $id);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        // To be selected
        $columns = array_merge($this->getSharedColumns(), [
            // Indicates whether time of there departure is already passed
            new RawSqlFragment('(CURDATE() > departure) AS passed')
        ]);

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName())
                       ->on()
                       ->equals(
                            self::getFullColumnName('room_id'),
                            RoomMapper::getRawColumn('id')
                       )
                       ->whereEquals(self::getFullColumnName('hotel_id'), $parameters['hotel_id'])
                       ->andWhereEquals('country', $input['country'], true)
                       ->andWhereLike('full_name', '%'.$input['full_name'].'%', true)
                       ->andWhereEquals('room_id', $input['room_id'], true)
                       ->andWhereEquals('state', $input['state'], true);

        // Date range filter
        if (!empty($parameters['from']) && !empty($parameters['to']) && !empty($parameters['type'])) {
            $db->andWhereBetween($parameters['type'], $parameters['from'], $parameters['to']);
        }

        // Leaving
        if (!empty($parameters['leaving'])) {
            if ($parameters['leaving'] == 'today') {
                $db->andWhereEquals('departure', new RawSqlFragment('CURDATE()'));
            } elseif ($parameters['leaving'] == 'tomorrow') {
                $db->andWhereEquals('departure', new RawSqlFragment('DATE_ADD(CURDATE(), INTERVAL 1 DAY)'));
            }

            $db->andWhereEquals('arrival', $input['arrival'], true);

        // Coming
        } else if (!empty($parameters['coming'])) {
            if ($parameters['coming'] == 'today') {
                $db->andWhereEquals('arrival', new RawSqlFragment('CURDATE()'));
            } elseif ($parameters['coming'] == 'tomorrow') {
                $db->andWhereEquals('arrival', new RawSqlFragment('DATE_ADD(CURDATE(), INTERVAL 1 DAY)'));
            }

            $db->andWhereEquals('departure', $input['departure'], true);

        } else {
            // Default 
            $db->andWhereEquals('arrival', $input['arrival'], true)
               ->andWhereEquals('departure', $input['departure'], true);
        }

        $db->orderBy($sortingColumn ? self::getFullColumnName($sortingColumn) : self::getFullColumnName('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }
}
