<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper as CoreMapper;
use Krystal\Db\Sql\RawSqlFragment;

abstract class AbstractMapper extends CoreMapper
{
    /* Shared column names */
    const PARAM_COLUMN_ID = 'id';
    const PARAM_COLUMN_LANG_ID = 'lang_id';

    /**
     * {@inheritDoc}
     */
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Checks whether there's attached language ID for particular entity
     * 
     * @param string $id Entity ID
     * @param string $languageId Language ID
     * @return boolean
     */
    private function translationExists($id, $languageId)
    {
        $count = $this->db->select()
                          ->count(self::PARAM_COLUMN_ID)
                          ->from(static::getTranslationTable())
                          ->whereEquals(self::PARAM_COLUMN_ID, $id)
                          ->andWhereEquals(self::PARAM_COLUMN_LANG_ID, $languageId)
                          ->queryScalar();

        return intval($count) > 0;
    }

    /**
     * Saves the entity
     * 
     * @param array $options
     * @param array $translations
     * @return boolean
     */
    final public function saveEntity(array $options, array $translations)
    {
        if (!empty($options[self::PARAM_COLUMN_ID])) {
            $id = (int) $options[self::PARAM_COLUMN_ID];

            // Update entity
            $this->db->update(static::getTableName(), $options)
                     ->whereEquals(self::PARAM_COLUMN_ID, $options[self::PARAM_COLUMN_ID])
                     ->execute();
        } else {
            // ID is incremented automatically, so no need to insert it
            unset($options[self::PARAM_COLUMN_ID]);

            // Add entity configuration
            $this->db->insert(static::getTableName(), $options)
                     ->execute();

            // Last entity ID
            $id = (int) $this->getLastId();
        }

        // Now handle translations
        foreach ($translations as $translation) {
            // Safe type casting
            $translation[self::PARAM_COLUMN_ID] = $id;
            $translation[self::PARAM_COLUMN_LANG_ID] = (int) $translation[self::PARAM_COLUMN_LANG_ID];

            if ($this->translationExists($translation[self::PARAM_COLUMN_ID], $translation[self::PARAM_COLUMN_LANG_ID])) {
                // Update translations
                $this->db->update(static::getTranslationTable(), $translation)
                         ->whereEquals(self::PARAM_COLUMN_ID, $translation[self::PARAM_COLUMN_ID])
                         ->andWhereEquals(self::PARAM_COLUMN_LANG_ID, (int) $translation[self::PARAM_COLUMN_LANG_ID])
                         ->execute();
            } else {
                // Insert translation
                $this->db->insert(static::getTranslationTable(), $translation)
                         ->execute();
            }
        }

        return true;
    }

    /**
     * Create entity select
     * 
     * @param array $columns Columns to be selected
     * @param string $table Table name in case needs to be overridden
     * @return \Krystal\Db\Db
     */
    final protected function createEntitySelect(array $columns, $table = null)
    {
        if ($table === null) {
            $table = static::getTableName();
        }
        
        return $this->db->select($columns)
                       ->from($table)
                       // Translation relation
                       ->leftJoin(static::getTranslationTable())
                       ->on()
                       ->equals(
                            static::getFullColumnName(self::PARAM_COLUMN_ID), 
                            new RawSqlFragment(static::getFullColumnName(self::PARAM_COLUMN_ID, static::getTranslationTable()))
                        );
    }

    /**
     * Finds an entity
     * 
     * @param array $columns Columns to be selected
     * @param string $id Entity ID
     * @param int $langId Language ID filter
     * @return array
     */
    final protected function findEntity(array $columns, $id, int $langId = 0)
    {
        $db = $this->createEntitySelect($columns)
                   ->whereEquals(self::getFullColumnName(self::PARAM_COLUMN_ID), $id);

        if ($langId == 0) {
            return $db->queryAll();
        } else {
            return $db->andWhereEquals(self::getFullColumnName(self::PARAM_COLUMN_LANG_ID, static::getTranslationTable()), $langId)
                      ->query();
        }
    }

    /**
     * Delete an entity completely
     * 
     * @param string|array $id
     * @return bolean
     */
    final public function deleteEntity($id)
    {
        if (!is_array($id)) {
            $id = array($id);
        }

        $tables = array(
            static::getTableName(),
            static::getTranslationTable()
        );

        // Delete entity with all its relational data
        return $this->db->delete($tables)
                     ->from(static::getTableName())
                     // Translation relation
                     ->innerJoin(static::getTranslationTable())
                     ->on()
                     ->equals(
                        static::getFullColumnName(self::PARAM_COLUMN_ID), 
                        new RawSqlFragment(static::getFullColumnName(self::PARAM_COLUMN_ID, static::getTranslationTable()))
                     )
                     // Current ID
                     ->whereIn(static::getFullColumnName(self::PARAM_COLUMN_ID), $id)
                     ->execute();
    }
}
