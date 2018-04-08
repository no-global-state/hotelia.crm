<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Sql\RawSqlFragment;

final class RoomTypeMapper extends AbstractMapper
{
    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_room_types');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return RoomTypeTranslationMapper::getTableName();
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
            RoomTypeTranslationMapper::getFullColumnName('lang_id'),
            self::getFullColumnName('hotel_id'),
            self::getFullColumnName('category_id'),
            self::getFullColumnName('persons'),
            self::getFullColumnName('children'),
            RoomTypeTranslationMapper::getFullColumnName('description'),
        ];
    }

    /**
     * Updates a relation
     * 
     * @param string $typeId
     * @param array $data
     * @return boolean
     */
    public function updateRelation(int $typeId, array $data)
    {
        // Remove all related items
        $this->removeFromJunction(RoomTypeFacilityRelationMapper::getTableName(), $typeId);

        // Update only if not empty
        if (!empty($data)) {
            foreach ($data as $index => $array) {
                if (empty($array[2])) {
                    $data[$index][2] = 'DEFAULT';
                }
            }

            return $this->db->insertMany(RoomTypeFacilityRelationMapper::getTableName(), ['master_id', 'slave_id', 'type'], $data)
                            ->execute();
        }
    }

    /**
     * Find all items attached to particular category
     * 
     * @param integer $hotelId typeId type ID
     * @param int $langId Language ID filter
     * @param integer $categoryId Optional category ID filter
     * @param bool $front Whether to fetch only front items
     * @param boolean $strict Whether to get all rows, including non-matching ones
     * @return array
     */
    public function findFacilities($typeId, int $langId, $categoryId = null, $front = false, bool $strict = false) : array
    {
        $columns = [
            FacilitiyItemMapper::getFullColumnName('id'),
            FacilitiyItemMapper::getFullColumnName('icon'),
            FacilitiyItemMapper::getFullColumnName('front'),
            FacilitiyItemMapper::getFullColumnName('always_free'),
            FacilitiyItemMapper::getFullColumnName('category_id'),
            FacilitiyItemTranslationMapper::getFullColumnName('name'),
            FacilitiyItemTranslationMapper::getFullColumnName('lang_id'),
        ];

        // Append hotel ID relation if provided
        if ($typeId !== null) {
            // Columns to be selected
            $columns = array_merge($columns, [
                RoomTypeFacilityRelationMapper::getFullColumnName('type'),
                new RawSqlFragment(sprintf('(slave_id = %s.id) AS checked', FacilitiyItemMapper::getTableName()))
            ]);
        }

        $db = $this->db->select($columns)
                       ->from(FacilitiyItemMapper::getTableName())
                       // Translation relation
                       ->leftJoin(FacilitiyItemTranslationMapper::getTableName(), [
                            FacilitiyItemTranslationMapper::getFullColumnName('id') => FacilitiyItemMapper::getRawColumn('id')
                        ]);

        // Append hotel ID relation if provided
        if ($typeId !== null) {
            $joinType = $strict ? 'INNER' : 'LEFT';

            // Junction relation
            $db->join($joinType, RoomTypeFacilityRelationMapper::getTableName(), [
                RoomTypeFacilityRelationMapper::getFullColumnName('slave_id') => FacilitiyItemMapper::getRawColumn('id'),
                RoomTypeFacilityRelationMapper::getFullColumnName('master_id') => $typeId
            ]);
        }

        // Language ID filter
        $db->whereEquals(FacilitiyItemTranslationMapper::getFullColumnName('lang_id'), $langId);

        if ($categoryId !== null) {
            $db->andWhereEquals(FacilitiyItemMapper::getFullColumnName('category_id'), $categoryId);
        }

        if ($front === true) {
            $db->andWhereEquals(FacilitiyItemMapper::getFullColumnName('front'), '1');
        }

        return $db->queryAll();
    }

    /**
     * Find available room types based on dates
     * 
     * @param string $arrival
     * @param string $departure
     * @param int $priceGroupId Price group ID filter
     * @param int $langId
     * @param int $hotelId
     * @param mixed $typeId Optional type id filter
     * @return array
     */
    public function findAvailableTypes(string $arrival, string $departure, int $priceGroupId, int $langId, int $hotelId, $typeId = null) : array
    {
        // Shared columns
        $columns = [
            self::getFullColumnName('id'),
            RoomCategoryTranslationMapper::getFullColumnName('name'),
            self::getFullColumnName('persons'),
            RoomTypeTranslationMapper::getFullColumnName('description'),
            RoomTypePriceMapper::getFullColumnName('price'),
            PriceGroupMapper::getFullColumnName('currency'),
            RoomTypeGalleryMapper::getFullColumnName('file') => 'cover',
            RoomTypeGalleryMapper::getFullColumnName('id') => 'cover_id'
        ];

        // To be selected
        $select = array_merge($columns, [
            new RawSqlFragment(sprintf('(COUNT(%s) - COUNT(%s)) AS free_count', 
                RoomMapper::getFullColumnName('type_id'), 
                ReservationMapper::getFullColumnName('id')
            ))
        ]);

        $db = $this->db->select($select)
                       ->from(RoomMapper::getTableName())
                       // Reservation relation
                       ->leftJoin(ReservationMapper::getTableName(), [
                            RoomMapper::getFullColumnName('id') => ReservationMapper::getRawColumn('room_id'),
                            ReservationMapper::getFullColumnName('hotel_id') => RoomMapper::getRawColumn('hotel_id')
                       ])
                       ->rawAnd()
                       ->openBracket()
                       ->compare('arrival', '<=', $arrival)
                       ->rawAnd()
                       ->compare('departure', ' >=', $departure)
                       ->closeBracket()
                       // Room type relation
                       ->leftJoin(self::getTableName(), [
                            RoomMapper::getFullColumnName('type_id') => self::getRawColumn('id')
                       ])
                       // Category relation
                       ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::getFullColumnName('id') => self::getRawColumn('category_id')
                       ])
                       // Category translation relation
                       ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id')
                       ])
                       // Room type translation
                       ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::getFullColumnName('id') => RoomTypeTranslationMapper::getRawColumn('id'),
                            RoomTypeTranslationMapper::getFullColumnName('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Room type price relation
                       ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('room_type_id') => RoomTypeMapper::getRawColumn('id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('price_group_id') => PriceGroupMapper::getRawColumn('id')
                       ])
                       // Room type cover
                       ->leftJoin(RoomTypeCoverMapper::getTableName(), [
                            RoomTypeCoverMapper::getFullColumnName('master_id') => RoomTypeMapper::getRawColumn('id')
                       ])
                       ->leftJoin(RoomTypeGalleryMapper::getTableName(), [
                            RoomTypeGalleryMapper::getFullColumnName('id') => RoomTypeCoverMapper::getRawColumn('slave_id'),
                            RoomTypeGalleryMapper::getFullColumnName('room_type_id') => RoomTypeCoverMapper::getRawColumn('master_id')
                       ])
                       // Constraints
                       ->whereEquals(RoomMapper::getFullColumnName('hotel_id'), $hotelId)
                       ->andWhereEquals(RoomCategoryTranslationMapper::getFullColumnName('lang_id'), $langId)
                       ->andWhereEquals(RoomTypePriceMapper::getFullColumnName('price_group_id'), $priceGroupId)
                       ->andWhereEquals(RoomMapper::getFullColumnName('type_id'), $typeId, true)
                       ->groupBy($columns);

        return $db->queryAll();
    }

    /**
     * Fetch region by its ID
     * 
     * @param int $id Room type id
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0)
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
    }

    /**
     * Find room by its type
     * 
     * @param int $typeId Room type Id
     * @param int $priceGroupId Optional price group ID filter
     * @param int $hotelId Hotel Id
     * @param int $langId Language Id filter
     * @return array
     */
    public function findByTypeId(int $typeId, int $priceGroupId, int $hotelId, int $langId)
    {
        // Columns to be selected
        $columns = array_merge($this->getColumns(), [
            RoomTypePriceMapper::getFullColumnName('price'),
            PriceGroupMapper::getFullColumnName('currency'),
            RoomCategoryTranslationMapper::getFullColumnName('name'),
            RoomTypeTranslationMapper::getFullColumnName('description'),
            RoomTypeGalleryMapper::getFullColumnName('file') => 'cover',
            RoomTypeGalleryMapper::getFullColumnName('id') => 'cover_id'
        ]);

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::getFullColumnName('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id'),
                        ])
                        // Translation relation
                        ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::getFullColumnName(self::PARAM_COLUMN_ID) => RoomTypeTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID),
                            RoomTypeTranslationMapper::getFullColumnName('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                        ])
                        // Room type price relation
                        ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('room_type_id') => self::getRawColumn('id')
                        ])
                        
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('price_group_id') => PriceGroupMapper::getRawColumn('id')
                        ])
                        // Room type cover
                        ->leftJoin(RoomTypeCoverMapper::getTableName(), [
                            RoomTypeCoverMapper::getFullColumnName('master_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Gallery relation
                        ->leftJoin(RoomTypeGalleryMapper::getTableName(), [
                            RoomTypeGalleryMapper::getFullColumnName('id') => RoomTypeCoverMapper::getRawColumn('slave_id'),
                            RoomTypeGalleryMapper::getFullColumnName('room_type_id') => RoomTypeCoverMapper::getRawColumn('master_id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::getFullColumnName('hotel_id'), $hotelId)
                        ->andWhereEquals(RoomTypePriceMapper::getFullColumnName('price_group_id'), $priceGroupId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::getFullColumnName('lang_id'), $langId)
                        ->andWhereEquals(self::getFullColumnName('id'), $typeId)
                        ->query();
    }

    /**
     * Fetch all entities
     * 
     * @param int $langId
     * @param int $hotelId
     * @return array
     */
    public function fetchAll(int $langId, int $hotelId) : array
    {
        // Columns to be selected
        $columns = array_merge($this->getColumns(), [
            RoomCategoryTranslationMapper::getFullColumnName('name'),
            RoomTypeTranslationMapper::getFullColumnName('description')
        ]);

        $db = $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::getFullColumnName('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::getFullColumnName('id') => RoomCategoryMapper::getRawColumn('id'),
                        ])
                        // Translation relation
                        ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::getFullColumnName(self::PARAM_COLUMN_ID) => RoomTypeTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID),
                            RoomTypeTranslationMapper::getFullColumnName('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::getFullColumnName('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::getFullColumnName('lang_id'), $langId)
                        ->orderBy($this->getPk())
                        ->desc();

        return $db->queryAll();
    }
}
