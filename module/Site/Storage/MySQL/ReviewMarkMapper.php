<?php

namespace Site\Storage\MySQL;

final class ReviewMarkMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews_marks');
    }

    /**
     * Find all marks by associated review ID
     * 
     * @param int $reviewId
     * @return array
     */
    public function findAllByReviewId(int $reviewId) : array
    {
        // Columns to be selected
        $columns = [
            self::getFullColumnName('id'),
            self::getFullColumnName('review_id'),
            self::getFullColumnName('review_type_id'),
            self::getFullColumnName('mark'),
            ReviewTypeMapper::getFullColumnName('name') => 'type'
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Review type relation
                        ->leftJoin(ReviewTypeMapper::getTableName(), [
                            ReviewTypeMapper::getFullColumnName('id') => self::getFullColumnName('review_type_id')
                        ])
                        ->whereEquals(self::getFullColumnName('review_id'), $reviewId)
                        ->queryAll();
    }
}
