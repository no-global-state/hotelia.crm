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
                    ->whereEquals(DictionaryTranslationMapper::column('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
