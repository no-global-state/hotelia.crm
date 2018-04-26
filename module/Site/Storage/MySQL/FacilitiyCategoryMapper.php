<?php

namespace Site\Storage\MySQL;

final class FacilitiyCategoryMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_facilitiy_categories');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return FacilitiyCategoryTranslationMapper::getTableName();
    }

    /**
     * Returns shared columns to be selected
     * 
     * @return array
     */
    private function getColumns()
    {
        return [
            self::column('id'),
            self::column('order'),
            FacilitiyCategoryTranslationMapper::column('lang_id'),
            FacilitiyCategoryTranslationMapper::column('name')
        ];
    }

    /**
     * Fetch category by its ID
     * 
     * @param int $id Category ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all categories with corresponding count
     * 
     * @param int $langId
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        $db = $this->db->select($this->getColumns())
                        ->count(FacilitiyItemMapper::column('category_id'), 'item_count')
                        ->from(self::getTableName())
                        // Room relation
                        ->leftJoin(FacilitiyItemMapper::getTableName(), [
                            self::column('id') => FacilitiyItemMapper::getRawColumn('category_id')
                        ])
                        // Translation relation
                        ->leftJoin(self::getTranslationTable(), [
                            self::column(self::PARAM_COLUMN_ID) => FacilitiyCategoryTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID)
                        ])
                        // Language ID constraint
                        ->whereEquals(FacilitiyCategoryTranslationMapper::column('lang_id'), $langId)
                        ->groupBy($this->getColumns())
                        ->orderBy('id')
                        ->desc();

        return $db->queryAll();
    }
}
