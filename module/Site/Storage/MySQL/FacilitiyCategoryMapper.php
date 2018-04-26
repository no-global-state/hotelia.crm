<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

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
     * @param bool $roomable Whether to fetch only roomable categories
     * @return array
     */
    public function fetchAll(int $langId, bool $roomable = false) : array
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
                        ->whereEquals(FacilitiyCategoryTranslationMapper::column('lang_id'), $langId);

        // Whether to constraint by roomable items
        if ($roomable === true) {
            $db->andWhereEquals(FacilitiyItemMapper::column('roomable'), new RawSqlFragment(1));
        }

        $db->groupBy($this->getColumns())
           ->orderBy('id')
           ->desc();

        return $db->queryAll();
    }
}
