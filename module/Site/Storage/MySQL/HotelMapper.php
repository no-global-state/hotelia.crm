<?php

namespace Site\Storage\MySQL;

use Krystal\Db\Filter\FilterableServiceInterface;
use Krystal\Db\Sql\RawSqlFragment;

final class HotelMapper extends AbstractMapper implements FilterableServiceInterface
{
    /**
     * {@inheritDoc}
     */
    protected static $translateable = [
        'name',
        'address',
        'lang_id'
    ];

    /**
     * {@inheritDoc}
     */
    public static function getTableName()
    {
        return self::getWithPrefix('velveto_hotels');
    }

    /**
     * {@inheritDoc}
     */
    public static function getTranslationTable()
    {
        return HotelTranslationMapper::getTableName();
    }

    /**
     * Update settings
     * 
     * @param array $settings
     * @return boolean
     */
    public function updateSettings(array $settings) : bool
    {
        return $this->updateColumns($settings, ['active']);
    }

    /**
     * Returns shared columns to be selected
     * 
     * @return array
     */
    private function getColumns() : array
    {
        return [
            self::getFullColumnName('id'),
            self::getFullColumnName('type_id'),
            self::getFullColumnName('region_id'),
            self::getFullColumnName('district_id'),
            self::getFullColumnName('phone'),
            self::getFullColumnName('rate'),
            self::getFullColumnName('discount'),
            self::getFullColumnName('website'),
            self::getFullColumnName('email'),
            self::getFullColumnName('active'),
            self::getFullColumnName('closed'),
            self::getFullColumnName('legal_address'),
            self::getFullColumnName('legal_name'),
            self::getFullColumnName('lat'),
            self::getFullColumnName('lng'),
            HotelTranslationMapper::getFullColumnName('lang_id'),
            HotelTranslationMapper::getFullColumnName('name'),
            HotelTranslationMapper::getFullColumnName('address'),
            HotelTranslationMapper::getFullColumnName('description')
        ];
    }

    /**
     * Finds all hotels
     * 
     * @param int $langId
     * @param int $priceGroupId
     * @param array $filters Optional filters
     * @return array
     */
    public function findAll(int $langId, int $priceGroupId, array $filters = []) : array
    {
        // Columns to be selected
        $columns = [
            self::getFullColumnName('id'),
            self::getFullColumnName('phone'),
            self::getFullColumnName('rate'),
            self::getFullColumnName('discount'),
            HotelTranslationMapper::getFullColumnName('name'),
            HotelTranslationMapper::getFullColumnName('address'),
            HotelTranslationMapper::getFullColumnName('description'),
            PriceGroupMapper::getFullColumnName('currency'),
            HotelTypeTranslationMapper::getFullColumnName('name') => 'type',
            RegionTranslationMapper::getFullColumnName('name') => 'region',
            DistrictTranslationMapper::getFullColumnName('name') => 'district',
            PhotoMapper::getFullColumnName('file') => 'cover',
            PhotoMapper::getFullColumnName('id') => 'cover_id',
        ];

        $db = $this->db->select($columns)
                       ->min(RoomTypePriceMapper::getFullColumnName('price'), 'start_price')
                       ->count(ReviewMapper::getFullColumnName('id'), 'review_count')
                       ->from(self::getTableName())
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::getFullColumnName('id') => self::getRawColumn('id')
                       ])
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName(), [
                            RoomMapper::getFullColumnName('hotel_id') => self::getRawColumn('id')
                       ])
                       // Room type relation
                       ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('room_type_id') => RoomMapper::getRawColumn('type_id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::getFullColumnName('id') => RoomTypePriceMapper::getRawColumn('price_group_id')
                       ])
                       // Hotel type relation
                       ->leftJoin(HotelTypeMapper::getTableName(), [
                            HotelTypeMapper::getFullColumnName('id') => self::getRawColumn('type_id')
                       ])
                       // Hotel type translation relation
                       ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                            HotelTypeTranslationMapper::getFullColumnName('id') => HotelTypeMapper::getRawColumn('id'),
                            HotelTypeTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Region relation
                       ->leftJoin(RegionMapper::getTableName(), [
                            RegionMapper::getFullColumnName('id') => self::getRawColumn('region_id')
                       ])
                       // Region translation relation
                       ->leftJoin(RegionTranslationMapper::getTableName(), [
                            RegionTranslationMapper::getFullColumnName('id') => RegionMapper::getRawColumn('id'),
                            RegionTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // District relation
                       ->leftJoin(DistrictMapper::getTableName(), [
                            DistrictMapper::getFullColumnName('id') => self::getRawColumn('district_id')
                       ])
                       // District translation
                       ->leftJoin(DistrictTranslationMapper::getTableName(), [
                            DistrictTranslationMapper::getFullColumnName('id') => DistrictMapper::getRawColumn('id'),
                            DistrictTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Review relation
                       ->leftJoin(ReviewMapper::getTableName(), [
                            ReviewMapper::getFullColumnName('hotel_id') => self::getRawColumn('id'),
                            ReviewMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Photo cover relation
                       ->leftJoin(PhotoCoverMapper::getTableName(), [
                            PhotoCoverMapper::getFullColumnName('master_id') => self::getRawColumn('id')
                       ])
                       // Photo relation
                       ->leftJoin(PhotoMapper::getTableName(), [
                            PhotoMapper::getFullColumnName('id') => PhotoCoverMapper::getRawColumn('slave_id')
                       ])
                       // Facility relation
                       ->leftJoin(FacilityRelationMapper::getTableName(), [
                            FacilityRelationMapper::getFullColumnName('master_id') => self::getRawColumn('id')
                       ])
                       // Constraints
                       ->whereEquals(HotelTranslationMapper::getFullColumnName('lang_id'), new RawSqlFragment($langId))
                       ->andWhereEquals(self::getFullColumnName('active'), new RawSqlFragment(1))
                       ->andWhereEquals(self::getFullColumnName('closed'), new RawSqlFragment(0))
                       ->andWhereEquals(RoomTypePriceMapper::getFullColumnName('price_group_id'), new RawSqlFragment($priceGroupId));

        // Type filter
        if (isset($filters['type']) && is_array($filters['type'])) {
            $db->andWhereIn(self::getFullColumnName('type_id'), $filters['type']);
        }

        // Facility filter
        if (isset($filters['facility']) && is_array($filters['facility'])) {
            $db->andWhereIn(FacilityRelationMapper::getFullColumnName('slave_id'), $filters['facility']);
        }

        // Hotel region filter
        if (!empty($filters['region_id'])) {
            $db->andWhereEquals(self::getFullColumnName('region_id'), $filters['region_id']);
        }

        // Hotel rate filter
        if (!empty($filters['rate'])) {
            $db->andWhereEquals(self::getFullColumnName('rate'), $filters['rate']);
        }

        // Hotel price filter
        if (isset($filters['price-start'], $filters['price-stop'])) {
            $db->andWhereBetween(RoomTypePriceMapper::getFullColumnName('price'), $filters['price-start'], $filters['price-stop']);
        }

        return $db->groupBy($columns)
                  ->queryAll();
    }

    /**
     * Fetch hotel by its ID
     * 
     * @param int $id Hotel ID
     * @param int $langId Language ID filter
     * @param int|null $priceGroupId Optional price group ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0, $priceGroupId = null)
    {
        // Columns to be selected
        $columns = array_merge($this->getColumns(), [
            PriceGroupMapper::getFullColumnName('currency'),
            HotelTypeTranslationMapper::getFullColumnName('name') => 'type',
            RegionTranslationMapper::getFullColumnName('name') => 'region',
            DistrictTranslationMapper::getFullColumnName('name') => 'district',
            PhotoMapper::getFullColumnName('id') => 'cover_id',
            PhotoMapper::getFullColumnName('file') => 'cover'
        ]);

        $db = $this->db->select($columns)
                       ->min(RoomTypePriceMapper::getFullColumnName('price'), 'start_price')
                       ->from(self::getTableName())
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::getFullColumnName('id') => self::getRawColumn('id')
                       ])
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName(), [
                            RoomMapper::getFullColumnName('hotel_id') => self::getRawColumn('id')
                       ])
                       // Room type relation
                       ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::getFullColumnName('room_type_id') => RoomMapper::getRawColumn('type_id')
                       ])
                       // Hotel type relation
                       ->leftJoin(HotelTypeMapper::getTableName(), [
                            HotelTypeMapper::getFullColumnName('id') => self::getRawColumn('type_id')
                       ])
                       // Hotel type translation relation
                       ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                            HotelTypeTranslationMapper::getFullColumnName('id') => HotelTypeMapper::getRawColumn('id'),
                            HotelTypeTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::getFullColumnName('id') => RoomTypePriceMapper::getRawColumn('price_group_id')
                       ])
                       // Region relation
                       ->leftJoin(RegionMapper::getTableName(), [
                            RegionMapper::getFullColumnName('id') => self::getRawColumn('region_id')
                       ])
                       // Region translation relation
                       ->leftJoin(RegionTranslationMapper::getTableName(), [
                            RegionTranslationMapper::getFullColumnName('id') => RegionMapper::getRawColumn('id'),
                            RegionTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // District relation
                       ->leftJoin(DistrictMapper::getTableName(), [
                            DistrictMapper::getFullColumnName('id') => self::getRawColumn('district_id')
                       ])
                       // District translation
                       ->leftJoin(DistrictTranslationMapper::getTableName(), [
                            DistrictTranslationMapper::getFullColumnName('id') => DistrictMapper::getRawColumn('id'),
                            DistrictTranslationMapper::getFullColumnName('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Photo cover relation
                       ->leftJoin(PhotoCoverMapper::getTableName(), [
                            self::getFullColumnName('id') => PhotoCoverMapper::getRawColumn('master_id')
                       ])
                       // Photo relation
                       ->leftJoin(PhotoMapper::getTableName(), [
                            PhotoMapper::getFullColumnName('id') => PhotoCoverMapper::getRawColumn('slave_id')
                       ])
                       // Constraints
                       ->whereEquals(self::getFullColumnName(self::PARAM_COLUMN_ID), $id)
                       ->andWhereEquals(RoomTypePriceMapper::getFullColumnName('price_group_id'), $priceGroupId, true)
                       ->groupBy($columns);

        if ($langId == 0) {
            return $db->queryAll();
        } else {
            return $db->andWhereEquals(HotelTranslationMapper::getFullColumnName(self::PARAM_COLUMN_LANG_ID), $langId)
                      ->query();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        $db = $this->createEntitySelect($this->getColumns())
                   // Language ID constraint
                   ->whereEquals(self::getDynamicAttribute('lang_id'), $parameters['lang_id'])
                   ->andWhereLike(self::getDynamicAttribute('name'), '%'.$input['name'].'%', true)
                   ->andWhereLike(self::getDynamicAttribute('address'), '%'.$input['address'].'%', true)
                   ->andWhereLike(self::getDynamicAttribute('phone'), '%'.$input['phone'].'%', true)
                   ->andWhereLike(self::getDynamicAttribute('website'), '%'.$input['website'].'%', true)
                   ->andWhereEquals(self::getDynamicAttribute('rate'), $input['rate'], true)
                   ->andWhereEquals(self::getDynamicAttribute('active'), $input['active'], true);

        $db->orderBy($sortingColumn ? self::getDynamicAttribute($sortingColumn) : self::getDynamicAttribute('id'));

        if ($desc) {
            $db->desc();
        }

        return $db->paginate($page, $itemsPerPage)
                  ->queryAll();
    }

    /**
     * Fetch all hotels
     * 
     * @param int $langId
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        // Columns to be selected
        $columns = array_merge($this->getColumns(), [
            PhotoMapper::getFullColumnName('id') => 'cover_id',
            PhotoMapper::getFullColumnName('file') => 'cover'
        ]);

        return $this->createEntitySelect($columns)
                    // Photo cover relation
                    ->leftJoin(PhotoCoverMapper::getTableName(), [
                        self::getFullColumnName('id') => PhotoCoverMapper::getRawColumn('master_id')
                    ])
                    // Photo relation
                    ->leftJoin(PhotoMapper::getTableName(), [
                        PhotoMapper::getFullColumnName('id') => PhotoCoverMapper::getRawColumn('slave_id')
                    ])
                    // Language ID filter
                    ->whereEquals(HotelTranslationMapper::getFullColumnName('lang_id'), $langId)
                    // Select only active ones
                    ->andWhereEquals(self::getFullColumnName('active'), '1')
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
