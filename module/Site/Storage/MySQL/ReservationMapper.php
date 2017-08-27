<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;
use Krystal\Stdlib\ArrayUtils;

final class ReservationMapper extends AbstractMapper
{
    const PARAM_COLUMN_ATTACHED = 'services';

    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('hotelia_reservation');
    }

    /**
     * {@inheritDoc}
     */
    public static function getJunctionTableName()
    {
        return self::getWithPrefix('hotelia_reservation_services');
    }

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
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
     * Fetches reservation by its ID
     * 
     * @param string $id
     * @return array
     */
    public function fetchById($id)
    {
        return $this->db->select('*')
                        ->from(self::getTableName())
                        ->whereEquals($this->getPk(), $id)
                        // Service relation
                        ->asManyToMany(
                            self::PARAM_COLUMN_ATTACHED, 
                            self::getJunctionTableName(), 
                            self::PARAM_JUNCTION_MASTER_COLUMN, 
                            RoomServiceMapper::getTableName(), 
                            'id', 
                            '*' // Columns to be selected in Service table
                        )
                        ->query();
    }

    /**
     * Fetch all records
     * 
     * @return array
     */
    public function fetchAll()
    {
        // Columns to be selected
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('room_id'),
            self::getFullColumnName('full_name'),
            self::getFullColumnName('gender'),
            self::getFullColumnName('country'),
            self::getFullColumnName('status'),
            self::getFullColumnName('arrival'),
            self::getFullColumnName('departure'),
            RoomMapper::getFullColumnName('name') => 'room'
        );

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName())
                       ->on()
                       ->equals(
                            self::getFullColumnName('room_id'),
                            RoomMapper::getRawColumn('id')
                       )
                       ->orderBy('id')
                       ->desc();

        return $db->queryAll();
    }
}
