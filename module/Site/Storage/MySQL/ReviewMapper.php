<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class ReviewMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_reviews');
    }

    /**
     * Fetch averages
     * 
     * @param int $hotelId
     * @param int $langId
     * @return array
     */
    public function fetchAverages(int $hotelId, int $langId) : array
    {
        // Columns to be selected
        $columns = [
            ReviewTypeMapper::column('id'),
            ReviewTypeTranslationMapper::column('name'),
            new RawSqlFragment('ROUND(AVG(velveto_reviews_marks.mark), 1) AS mark_avg')
        ];

        $db = $this->db->select($columns)
                        ->from(ReviewTypeMapper::getTableName())
                        // Type translation relation
                        ->leftJoin(ReviewTypeTranslationMapper::getTableName(), [
                            ReviewTypeTranslationMapper::column('id') => ReviewTypeMapper::getRawColumn('id')
                        ])
                        // Mark relation
                        ->leftJoin(ReviewMarkMapper::getTableName(), [
                            ReviewMarkMapper::column('review_type_id') => ReviewTypeMapper::getRawColumn('id')
                        ])
                        // Reviews relation
                        ->leftJoin(self::getTableName(), [
                            self::column('id') => ReviewMarkMapper::column('review_id'),
                            ReviewTypeTranslationMapper::column('lang_id') => self::getRawColumn('lang_id')
                        ])
                        // Constraints
                        ->whereEquals(ReviewTypeTranslationMapper::column('lang_id'), $langId)
                        ->andWhereEquals(self::column('hotel_id'), $hotelId)
                        ->groupBy([
                            ReviewTypeMapper::column('id'),
                            ReviewTypeTranslationMapper::column('name'),
                        ]);

        return $db->queryAll();
    }

    /**
     * Fetch all reviews
     * 
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $hotelId) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('lang_id'),
            self::column('hotel_id'),
            self::column('date'),
            self::column('title'),
            self::column('review'),
            BookingMapper::column('arrival'),
            BookingMapper::departure('arrival'),
        ];

        return $this->db->select(array_merge($columns, [new RawSqlFragment(sprintf('ROUND(AVG(%s), 1) AS mark', ReviewMarkMapper::column('mark')))]))
                        ->from(self::getTableName())
                        // Mark relation
                        ->innerJoin(ReviewMarkMapper::getTableName(), [
                            ReviewMarkMapper::column('review_id') => self::getRawColumn('id')
                        ])
                        // Booking relation
                        ->leftJoin(BookingMapper::getTableName(), [
                            BookingMapper::column('review_id') => self::getRawColumn('id')
                        ])
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        ->groupBy($columns)
                        ->orderBy($this->getPk())
                        ->desc()
                        ->queryAll();
    }
}
