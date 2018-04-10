<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class MealsMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_meals');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return MealsTranslationMapper::getTableName();
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
            MealsTranslationMapper::column('lang_id'),
            MealsTranslationMapper::column('name'),
        ];
    }

    /**
     * Update relation with hotel ID
     * 
     * @param int $hotelId
     * @param array $mealIds
     * @return boolean
     */
    public function updateRelation(int $hotelId, array $mealIds) : bool
    {
        return $this->syncWithJunction(MealsRelationMapper::getTableName(), $hotelId, $mealIds);
    }

    /**
     * Fetch meal by its ID
     * 
     * @param int $id Meal ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Fetch all meals
     * 
     * @param int $langId Language ID filter
     * @param mixed $hotelId Optional hotel ID
     * @return array
     */
    public function fetchAll(int $langId, $hotelId = null) : array
    {
        $columns = $this->getColumns();

        if ($hotelId !== null) {
            $columns[] = new RawSqlFragment(sprintf('(%s = %s) AS active', MealsRelationMapper::column('slave_id'), self::column('id')));
        }

        $db = $this->createEntitySelect($columns);

        if ($hotelId !== null) {
            $db->leftJoin(MealsRelationMapper::getTableName(), [
                MealsRelationMapper::column('master_id') => $hotelId,
                MealsRelationMapper::column('slave_id') => new RawSqlFragment(self::column('id'))
            ]);
        }

        $db->whereEquals(MealsTranslationMapper::column('lang_id'), $langId)
           ->orderBy($this->getPk())
           ->desc();

        return $db->queryAll();
    }
}
