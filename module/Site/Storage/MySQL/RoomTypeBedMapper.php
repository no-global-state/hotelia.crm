<?php

namespace Site\Storage\MySQL;

final class RoomTypeBedMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_type_beds');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return RoomTypeBedTranslationMapper::getTableName();
    }

    /**
     * Returns shared columns to be selected
     * 
     * @return array
     */
    private function getColumns() : array
    {
        return [
            self::column('id'),
            self::column('order'),
            RoomTypeBedTranslationMapper::column('lang_id'),
            RoomTypeBedTranslationMapper::column('name'),
        ];
    }

    /**
     * Update relations
     * 
     * @param int $roomTypeId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $roomTypeId, array $data) : bool
    {
        // Remove previous if any
        $this->db->delete()
                 ->from(RoomTypeBedRelationMapper::getTableName())
                 ->whereEquals('room_type_id', $roomTypeId)
                 ->execute();

        // Now insert
        return $this->db->insertMany(RoomTypeBedRelationMapper::getTableName(), ['room_type_id', 'bed_id', 'qty'], $data)
                        ->execute();
    }

    /**
     * Fetch relational data
     * 
     * @param int $roomTypeId
     * @param int $langId
     * @param bool $exclude Whether to return only matching rows or all
     * @return array
     */
    public function fetchRelation(int $roomTypeId, int $langId, bool $exclude) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            RoomTypeBedTranslationMapper::column('name'),
            RoomTypeBedRelationMapper::column('qty')
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Translation relation
                        ->leftJoin(RoomTypeBedTranslationMapper::getTableName(), [
                            RoomTypeBedTranslationMapper::column('id') => self::getRawColumn('id')
                        ])
                        // Relation
                        ->join($exclude ? 'INNER' : 'LEFT', RoomTypeBedRelationMapper::getTableName(), [
                            RoomTypeBedRelationMapper::column('bed_id') => self::getRawColumn('id'),
                            RoomTypeBedRelationMapper::column('room_type_id') => $roomTypeId
                        ])
                        // Constraints
                        ->whereEquals(RoomTypeBedTranslationMapper::column('lang_id'), $langId)
                        ->queryAll();
    }

    /**
     * Fetch bed entry by its ID
     * 
     * @param int $id Entry ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all bed entries
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(RoomTypeBedTranslationMapper::column('lang_id'), $langId)
                    ->queryAll();
    }
}
