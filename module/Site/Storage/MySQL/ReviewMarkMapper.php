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
     * @param int $langId
     * @return array
     */
    public function findAllByReviewId(int $reviewId, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('review_id'),
            self::column('review_type_id'),
            self::column('mark'),
            ReviewTypeTranslationMapper::column('name') => 'type'
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Review type relation
                        ->leftJoin(ReviewTypeMapper::getTableName(), [
                            ReviewTypeMapper::column('id') => self::getRawColumn('review_type_id')
                        ])
                        // Review type localization
                        ->leftJoin(ReviewTypeTranslationMapper::getTableName(), [
                            ReviewTypeTranslationMapper::column('id') => ReviewTypeMapper::getRawColumn('id')
                        ])
                        // Constraints
                        ->whereEquals(self::column('review_id'), $reviewId)
                        ->andWhereEquals(ReviewTypeTranslationMapper::column('lang_id'), $langId)
                        ->queryAll();
    }
}
