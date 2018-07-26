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
            self::column('id'),
            RoomTypeTranslationMapper::column('lang_id'),
            self::column('hotel_id'),
            self::column('category_id'),
            self::column('persons'),
            self::column('children'),
            RoomTypeTranslationMapper::column('description'),
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
            FacilitiyItemMapper::column('id'),
            FacilitiyItemMapper::column('category_id'),
            FacilitiyItemMapper::column('icon'),
            FacilitiyItemMapper::column('front'),
            FacilitiyItemMapper::column('always_free'),
            FacilitiyItemMapper::column('roomable'),
            FacilitiyItemTranslationMapper::column('name'),
            FacilitiyItemTranslationMapper::column('lang_id'),
        ];

        // Append hotel ID relation if provided
        if ($typeId !== null) {
            // Columns to be selected
            $columns = array_merge($columns, [
                RoomTypeFacilityRelationMapper::column('type'),
                new RawSqlFragment(sprintf('(slave_id = %s.id) AS checked', FacilitiyItemMapper::getTableName()))
            ]);
        }

        $db = $this->db->select($columns)
                       ->from(FacilitiyItemMapper::getTableName())
                       // Translation relation
                       ->leftJoin(FacilitiyItemTranslationMapper::getTableName(), [
                            FacilitiyItemTranslationMapper::column('id') => FacilitiyItemMapper::getRawColumn('id')
                        ]);

        // Append hotel ID relation if provided
        if ($typeId !== null) {
            $joinType = $strict ? 'INNER' : 'LEFT';

            // Junction relation
            $db->join($joinType, RoomTypeFacilityRelationMapper::getTableName(), [
                RoomTypeFacilityRelationMapper::column('slave_id') => FacilitiyItemMapper::getRawColumn('id'),
                RoomTypeFacilityRelationMapper::column('master_id') => $typeId
            ]);
        }

        // Language ID filter
        $db->whereEquals(FacilitiyItemTranslationMapper::column('lang_id'), $langId)
           ->andWhereEquals(FacilitiyItemMapper::column('roomable'), new RawSqlFragment('1'));

        if ($categoryId !== null) {
            $db->andWhereEquals(FacilitiyItemMapper::column('category_id'), $categoryId);
        }

        if ($front === true) {
            $db->andWhereEquals(FacilitiyItemMapper::column('front'), '1');
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
            self::column('id'),
            RoomCategoryTranslationMapper::column('name'),
            self::column('persons'),
            RoomTypeTranslationMapper::column('description'),
            PriceGroupMapper::column('currency'),
            RoomTypeGalleryMapper::column('file') => 'cover',
            RoomTypeGalleryMapper::column('id') => 'cover_id'
        ];

        // To be selected
        $select = array_merge($columns, [
            new RawSqlFragment(sprintf('(COUNT(%s) - COUNT(%s)) AS free_count', 
                RoomMapper::column('type_id'), 
                ReservationMapper::column('id')
            ))
        ]);

        $db = $this->db->select($select)
                       ->from(RoomMapper::getTableName())
                       // Reservation relation
                       ->leftJoin(ReservationMapper::getTableName(), [
                            RoomMapper::column('id') => ReservationMapper::getRawColumn('room_id'),
                            ReservationMapper::column('hotel_id') => RoomMapper::getRawColumn('hotel_id')
                       ])
                       ->rawAnd()
                       ->openBracket()
                       ->compare('arrival', '<=', $arrival)
                       ->rawAnd()
                       ->compare('departure', ' >=', $departure)
                       ->closeBracket()
                       // Room type relation
                       ->leftJoin(self::getTableName(), [
                            RoomMapper::column('type_id') => self::getRawColumn('id')
                       ])
                       // Category relation
                       ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::column('id') => self::getRawColumn('category_id')
                       ])
                       // Category translation relation
                       ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id')
                       ])
                       // Room type translation
                       ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::column('id') => RoomTypeTranslationMapper::getRawColumn('id'),
                            RoomTypeTranslationMapper::column('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Room type price relation
                       ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::column('room_type_id') => RoomTypeMapper::getRawColumn('id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            RoomTypePriceMapper::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                       ])
                       // Room type cover
                       ->leftJoin(RoomTypeCoverMapper::getTableName(), [
                            RoomTypeCoverMapper::column('master_id') => RoomTypeMapper::getRawColumn('id')
                       ])
                       ->leftJoin(RoomTypeGalleryMapper::getTableName(), [
                            RoomTypeGalleryMapper::column('id') => RoomTypeCoverMapper::getRawColumn('slave_id'),
                            RoomTypeGalleryMapper::column('room_type_id') => RoomTypeCoverMapper::getRawColumn('master_id')
                       ])
                       // Constraints
                       ->whereEquals(RoomMapper::column('hotel_id'), $hotelId)
                       ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                       ->andWhereEquals(RoomTypePriceMapper::column('price_group_id'), $priceGroupId)
                       ->andWhereEquals(RoomMapper::column('type_id'), $typeId, true)
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
            RoomTypePriceMapper::column('price'),
            PriceGroupMapper::column('currency'),
            RoomCategoryTranslationMapper::column('name'),
            RoomTypeTranslationMapper::column('description'),
            RoomTypeGalleryMapper::column('file') => 'cover',
            RoomTypeGalleryMapper::column('id') => 'cover_id'
        ]);

        return $this->db->select($columns)
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::column('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id'),
                        ])
                        // Translation relation
                        ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::column(self::PARAM_COLUMN_ID) => RoomTypeTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID),
                            RoomTypeTranslationMapper::column('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                        ])
                        // Room type price relation
                        ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::column('room_type_id') => self::getRawColumn('id')
                        ])
                        
                        // Price group relation
                        ->leftJoin(PriceGroupMapper::getTableName(), [
                            RoomTypePriceMapper::column('price_group_id') => PriceGroupMapper::getRawColumn('id')
                        ])
                        // Room type cover
                        ->leftJoin(RoomTypeCoverMapper::getTableName(), [
                            RoomTypeCoverMapper::column('master_id') => RoomTypeMapper::getRawColumn('id')
                        ])
                        // Gallery relation
                        ->leftJoin(RoomTypeGalleryMapper::getTableName(), [
                            RoomTypeGalleryMapper::column('id') => RoomTypeCoverMapper::getRawColumn('slave_id'),
                            RoomTypeGalleryMapper::column('room_type_id') => RoomTypeCoverMapper::getRawColumn('master_id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        ->andWhereEquals(RoomTypePriceMapper::column('price_group_id'), $priceGroupId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->andWhereEquals(self::column('id'), $typeId)
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
            RoomCategoryTranslationMapper::column('name'),
            RoomTypeTranslationMapper::column('description')
        ]);

        $db = $this->db->select($columns)
                        ->count(RoomMapper::column('id'), 'room_count')
                        ->from(self::getTableName())
                        // Room category relation
                        ->leftJoin(RoomCategoryMapper::getTableName(), [
                            RoomCategoryMapper::column('id') => self::getRawColumn('category_id')
                        ])
                        // Room category translation relation
                        ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                            RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id'),
                        ])
                        // Translation relation
                        ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                            self::column(self::PARAM_COLUMN_ID) => RoomTypeTranslationMapper::getRawColumn(self::PARAM_COLUMN_ID),
                            RoomTypeTranslationMapper::column('lang_id') => RoomCategoryTranslationMapper::getRawColumn('lang_id')
                        ])
                        // Room relation (purely for couting)
                        ->leftJoin(RoomMapper::getTableName(), [
                            RoomMapper::column('type_id') => self::getRawColumn('id'),
                            RoomMapper::column('hotel_id') => self::getRawColumn('hotel_id')
                        ])
                        // Hotel ID constraint
                        ->whereEquals(self::column('hotel_id'), $hotelId)
                        // Language ID constraint
                        ->andWhereEquals(RoomCategoryTranslationMapper::column('lang_id'), $langId)
                        ->groupBy($columns)
                        ->orderBy($this->getPk())
                        ->desc();

        return $db->queryAll();
    }
}
