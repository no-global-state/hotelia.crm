<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\AbstractMapper;

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
    protected function getPk()
    {
        return 'id';
    }

    /**
     * Find all items attached to particular category
     * 
     * @param integer $categoryId Optional category ID filter
     * @return array
     */
    public function fetchAll($categoryId = null)
    {
        $db = $this->db->select('*')
                        ->from(self::getTableName());

        if ($categoryId !== null) {
            $db->whereEquals('category_id', $categoryId);
        }

        return $db->queryAll();
    }
}
