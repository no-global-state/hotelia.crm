<?php

namespace Site\Storage\MySQL;

final class ReviewTypeMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews_types');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return ReviewTypeTranslationMapper::getTableName();
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
            self::column('mark'),
            ReviewTypeTranslationMapper::column('lang_id'),
            ReviewTypeTranslationMapper::column('name'),
        ];
    }

    /**
     * Fetch review type entry by its ID
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
     * Fetch all review types entries
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->createEntitySelect($this->getColumns())
                    ->whereEquals(ReviewTypeTranslationMapper::column('lang_id'), $langId)
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
