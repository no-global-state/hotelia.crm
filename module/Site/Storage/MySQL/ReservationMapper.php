<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;
use Krystal\Db\Sql\RawSqlFragment;
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
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
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
            self::getFullColumnName('full_name'),
            self::getFullColumnName('gender'),
            self::getFullColumnName('country'),
            self::getFullColumnName('status'),
            self::getFullColumnName('phone'),
            self::getFullColumnName('email'),
            self::getFullColumnName('passport'),
            self::getFullColumnName('discount'),
            self::getFullColumnName('state'),
            self::getFullColumnName('purpose'),
            self::getFullColumnName('payment_type'),
            self::getFullColumnName('legal_status'),
            self::getFullColumnName('arrival'),
            self::getFullColumnName('departure'),
            self::getFullColumnName('comment'),
            RoomMapper::getFullColumnName('name') => 'room'
        );
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
            RoomTypeMapper::getFullColumnName('unit_price') => 'room_price'
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
                        );

                        // Apply by reference
                        $visitor($db);

        // Service relation
        $db->asManyToMany(
            self::PARAM_COLUMN_ATTACHED, 
            self::getJunctionTableName(), 
            self::PARAM_JUNCTION_MASTER_COLUMN, 
            RoomServiceMapper::getTableName(), 
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
        $db = $this->db->select($this->getSharedColumns())
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
