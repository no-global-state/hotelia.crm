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
     * Fetch all categories with corresponding count
     * 
     * @return array
     */
    public function fetchAll()
    {
        $columns = array(
            self::getFullColumnName('id'),
            self::getFullColumnName('name')
        );

        return $this->db->select($columns)
                        ->count(FacilitiyItemMapper::getFullColumnName('category_id'), 'item_count')
                        ->from(self::getTableName())
                        // Room relation
                        ->leftJoin(FacilitiyItemMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            FacilitiyItemMapper::getRawColumn('category_id')
                        )
                        ->groupBy($columns)
                        ->orderBy('id')
                        ->desc()
                        ->queryAll();
    }
}
