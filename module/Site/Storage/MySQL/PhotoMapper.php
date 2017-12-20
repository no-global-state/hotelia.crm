<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class PhotoMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels_photos');
    }

    /**
     * Fetch all photos
     * 
     * @param string $hotelId
     * @return array
     */
    public function fetchAll($hotelId)
    {
        // Columns to be selected
        $columns = [
            self::getFullColumnName('id'),
            self::getFullColumnName('hotel_id'),
            self::getFullColumnName('file'),
            self::getFullColumnName('order'),
            new RawSqlFragment(sprintf('(%s.id = %s.slave_id) as cover', self::getTableName(), PhotoCoverMapper::getTableName()))
        ];

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Photo cover relation
                        ->leftJoin(PhotoCoverMapper::getTableName())
                        ->on()
                        ->equals(
                            self::getFullColumnName('id'),
                            PhotoCoverMapper::getRawColumn('slave_id')
                        )
                        ->whereEquals('hotel_id', $hotelId)
                        ->queryAll();
    }
}
