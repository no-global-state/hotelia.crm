<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;
use Krystal\Db\Sql\RawBinding;
use Krystal\Db\Filter\FilterableServiceInterface;
use Krystal\Db\Filter\InputDecorator;
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
            self::column('id'),
            self::column('hotel_id'),
            self::column('room_id'),
            self::column('payment_system_id'),
            self::column('price_group_id'),
            self::column('full_name'),
            self::column('gender'),
            self::column('country'),
            self::column('status'),
            self::column('phone'),
            self::column('email'),
            self::column('passport'),
            self::column('discount'),
            self::column('state'),
            self::column('source'),
            self::column('purpose'),
            self::column('legal_status'),
            self::column('arrival'),
            self::column('departure'),
            new RawSqlFragment('ABS(DATEDIFF(departure, arrival)) AS days'),
            self::column('comment'),
            self::column('company'),
            self::column('tax'),
            self::column('price'),
            RoomMapper::column('name') => 'room'
        );
    }

    /**
     * Counts by reservation states
     * 
     * @param int $hotelId
     * @return array
     */
    public function countStates(int $hotelId) : array
    {
        return $this->db->select('state')
                        ->count('id', 'count')
                        ->from(self::getTableName())
                        ->whereEquals('hotel_id', $hotelId)
                        ->groupBy('state')
                        ->queryAll();
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
     * @param int $year
     * @param array $months
     * @param array $roomIds
     * @param int $priceGroupId
     * @return array
     */
    public function getSumCount(int $year, array $months, array $roomIds, int $priceGroupId) : array
    {
        // Columns to be selected
        $columns = [
            PriceGroupMapper::column('currency')
        ];

        return $this->db->select($columns)
                        // Calculate functions
                        ->sum(self::column('price'), 'price')
                        ->sum(self::column('tax'), 'tax')
                        ->count(self::column('id'), 'id')
                        ->from(self::getTableName())
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => self::getRawColumn('price_group_id')
                        ])
                        // Constraints
                        ->whereIn('MONTH(arrival)', new RawBinding($months))
                        ->andWhereIn('room_id', $roomIds)
                        ->andWhereEquals('YEAR(arrival)', $year)
                        ->andWhereEquals('price_group_id', $priceGroupId)
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
     * @param int $hotelId
     * @param int $langId Language ID constraint
     * @param string $type Optional room type filter
     * @return array
     */
    public function findReservations(int $hotelId, int $langId, $type = null) : array
    {
        // Columns to be selected
        $columns = array(
            RoomMapper::column('id'),
            RoomMapper::column('name'),
            RoomCategoryTranslationMapper::column('name') => 'type',
            self::column('arrival'),
            self::column('departure'),
            self::column('id') => 'reservation_id'
        );

        $db = $this->db->select($columns)
                        ->from(RoomMapper::getTableName())
                        // Reservation relation
                        ->leftJoin(self::getTableName(), [
                            self::column('room_id') => RoomMapper::getRawColumn('id')
                        ])
                        // Room type relation
                        ->leftJoin(RoomTypeMapper::getTableName(), [
                            RoomMapper::column('type_id') => RoomTypeMapper::getRawColumn('id')
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
                        ->whereEquals(RoomMapper::column('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId);

        // If type is provided, the filter by its ID
        if ($type != null) {
            $db->andWhereEquals(RoomTypeMapper::column('id'), $type);
        }

        // Sort by name
        return $db->orderBy(RoomMapper::column('name'))
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
            PriceGroupMapper::column('currency'),
            PaymentSystemMapper::column('name') => 'payment_system'
        ));

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room relation
                        ->leftJoin(RoomMapper::getTableName())
                        ->on()
                        ->equals(
                            self::column('room_id'),
                            RoomMapper::getRawColumn('id')
                        )
                        // Room type relation
                        ->leftJoin(RoomTypeMapper::getTableName())
                        ->on()
                        ->equals(
                            RoomMapper::column('type_id'),
                            RoomTypeMapper::getRawColumn('id')
                        )
                        // Price group mapper
                        ->leftJoin(PriceGroupMapper::getTableName())
                        ->on()
                        ->equals(
                            PriceGroupMapper::column('id'),
                            self::getRawColumn('price_group_id')
                        )
                        // Payment system relation
                        ->leftJoin(PaymentSystemMapper::getTableName())
                        ->on()
                        ->equals(
                            PaymentSystemMapper::column('id'),
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
     * Fetch reservation info by room ID
     * 
     * @param int $roomId
     * @return array
     */
    public function fetchByRoomId(int $roomId) : array
    {
        return $this->findByConstraint(function($db) use ($roomId){
            $db->whereEquals(self::column('room_id'), $roomId)
               ->andWhere(self::column('departure'), '>=', new RawSqlFragment('CURDATE()'));
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
            $db->whereEquals(self::column($this->getPk()), $id);
        });
    }

    /**
     * Fetch latest reservations
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchLatest(int $hotelId) : array
    {
        return $this->filter(new InputDecorator(), 1, 10, false, true, ['hotel_id' => $hotelId]);
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
                            self::column('room_id'),
                            RoomMapper::getRawColumn('id')
                       )
                       ->whereEquals(self::column('hotel_id'), $parameters['hotel_id'])
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

        $db->orderBy($sortingColumn ? self::column($sortingColumn) : self::column('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }
}
