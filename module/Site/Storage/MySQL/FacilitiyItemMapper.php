<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class FacilitiyItemMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_facilitiy_items');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return FacilitiyItemTranslationMapper::getTableName();
    }

    /**
     * Returns shared columns 
     * 
     * @return array
     */
    private function getColumns() : array
    {
        return [
            self::getFullColumnName('id'),
            self::getFullColumnName('icon'),
            self::getFullColumnName('front'),
            self::getFullColumnName('always_free'),
            self::getFullColumnName('category_id'),
            FacilitiyItemTranslationMapper::getFullColumnName('name'),
            FacilitiyItemTranslationMapper::getFullColumnName('description'),
            FacilitiyItemTranslationMapper::getFullColumnName('lang_id'),
        ];
    }

    /**
     * Fetch relations: hotel ID -> Item ID
     * 
     * @param array $hotelIds
     * @return array
     */
    public function fetchRelations(array $hotelIds) : array
    {
        // Columns to be selected
        $columns = [
            'master_id' => 'hotel_id', 
            'slave_id' => 'item_id'
        ];

        return $this->db->select($columns)
                        ->from(FacilityRelationMapper::getTableName())
                        ->whereIn('master_id', $hotelIds)
                        ->queryAll();
    }

    /**
     * Fetch item by its ID
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
     * Updates a relation
     * 
     * @param string $hotelId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $hotelId, array $data)
    {
        // Remove all related items
        $this->removeFromJunction(FacilityRelationMapper::getTableName(), $hotelId);

        return $this->db->insertMany(FacilityRelationMapper::getTableName(), ['master_id', 'slave_id', 'type'], $data)
                        ->execute();
    }

    /**
     * Find all items attached to particular category
     * 
     * @param int $langId Language ID filter
     * @param integer $categoryId Optional category ID filter
     * @param integer $hotelId Optional hotel ID filter
     * @param bool $front Whether to fetch only front items
     * @param bool $checked Whether to select only checked items
     * @return array
     */
    public function fetchAll(int $langId, $categoryId = null, $hotelId = null, bool $front = false, bool $checked = false) : array
    {
        $columns = $this->getColumns();

        // Append hotel ID relation if provided
        if ($hotelId !== null) {
            // Columns to be selected
            $columns = array_merge($columns, [
                FacilityRelationMapper::getFullColumnName('type'),
                new RawSqlFragment(sprintf('(slave_id = %s.id) AS checked', self::getTableName()))
            ]);
        }

        $db = $this->db->select($columns)
                       ->from(self::getTableName())
                       // Translation relation
                       ->leftJoin(FacilitiyItemTranslationMapper::getTableName(), [
                            FacilitiyItemTranslationMapper::getFullColumnName('id') => self::getRawColumn('id')
                        ]);

        // Append hotel ID relation if provided
        if ($hotelId !== null) {
            // JOIN type
            $joinType = $checked === false ? 'LEFT' : 'INNER';

            // Junction relation
            $db->join($joinType, FacilityRelationMapper::getTableName(), [
                FacilityRelationMapper::getFullColumnName('slave_id') => self::getRawColumn('id'),
                FacilityRelationMapper::getFullColumnName('master_id') => $hotelId
            ]);
        }

        // Language ID filter
        $db->whereEquals(FacilitiyItemTranslationMapper::getFullColumnName('lang_id'), $langId);

        if ($categoryId !== null) {
            $db->andWhereEquals(self::getFullColumnName('category_id'), $categoryId);
        }

        if ($front === true) {
            $db->andWhereEquals(self::getFullColumnName('front'), '1');
        }

        return $db->queryAll();
    }
}
