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
     * Finds hotel name by its associated ID
     * 
     * @param int $hotelId
     * @param int $langId
     * @return string
     */
    public function findNameById(int $hotelId, int $langId)
    {
        $columns = [HotelTranslationMapper::column('name')];
        $row = $this->findEntity($columns, $hotelId, $langId);

        return $row['name'] ?? null;
    }

    /**
     * Finds hotel email by its associated ID
     * 
     * @param int $hotelId
     * @return mixed
     */
    public function findEmailById(int $hotelId)
    {
        return $this->findColumnByPk($hotelId, 'email');
    }

    /**
     * Checks whether wizard is finished
     * 
     * @param int $hotelId Hotel Id
     * @return boolean
     */
    public function isWizardFinished(int $hotelId) : bool
    {
        return (bool) $this->db->select()
                               ->count('id')
                               ->from(self::getTableName())
                               ->whereEquals('id', $hotelId)
                               ->andWhereEquals('wizard_finished', 1)
                               ->queryScalar();
    }

    /**
     * Makes wizard as finished
     * 
     * @param int $hotelId Hotel Id
     * @return boolean
     */
    public function markWizardAsFinished(int $hotelId)
    {
        return $this->db->update(self::getTableName(), ['wizard_finished' => 1])
                        ->whereEquals('id', $hotelId)
                        ->execute();
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
            self::column('id'),
            self::column('type_id'),
            self::column('region_id'),
            self::column('district_id'),
            self::column('phone'),
            self::column('fax'),
            self::column('zip'),
            self::column('rate'),
            self::column('discount'),
            self::column('website'),
            self::column('email'),
            self::column('active'),
            self::column('closed'),
            self::column('legal_address'),
            self::column('legal_name'),
            self::column('lat'),
            self::column('lng'),
            self::column('lng'),
            self::column('city_tax_include'),
            self::column('contact_full_name'),
            self::column('contact_position'),
            self::column('contact_email'),
            self::column('contact_first_phone'),
            self::column('contact_second_phone'),
            self::column('checkin_from'),
            self::column('checkin_to'),
            self::column('checkout_from'),
            self::column('checkout_to'),
            self::column('payment_time'),
            self::column('breakfast'),
            self::column('has_restaurant'),
            self::column('restaurant_opening'),
            self::column('restaurant_closing'),
            self::column('center_distance'),
            self::column('penality_enabled'),
            self::column('penality_not_taken_after'),
            self::column('penality_not_later_arrival'),
            self::column('penality_cancelation_item'),
            self::column('penality_cancelation_type'),
            self::column('penality_percentage'),
            self::column('penality_percentage_type'),
            self::column('card_required'),
            HotelTranslationMapper::column('lang_id'),
            HotelTranslationMapper::column('name'),
            HotelTranslationMapper::column('address'),
            HotelTranslationMapper::column('description')
        ];
    }

    /**
     * Find similar hotels excluding provided one
     * 
     * @param int $id Hotel ID to be excluded
     * @param int $langId Language ID filter
     * @param int $priceGroupId Active price group ID
     * @param int $regionId Region ID filter
     * @param int $limit Limit of hotels to be returned
     * @return array
     */
    public function findSimilar(int $id, int $langId, int $priceGroupId, int $regionId, int $limit = 5) : array
    {
        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('rate'),
            PhotoMapper::column('file') => 'cover',
            PhotoMapper::column('id') => 'cover_id',
            HotelTranslationMapper::column('name')
        ];

        $db = $this->db->select($columns)
                       ->min(RoomTypePriceMapper::column('price'), 'start_price')
                       ->from(self::getTableName())
                       // Hotel translation relation
                       ->innerJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::column('id') => self::getRawColumn('id')
                       ])
                       // Photo cover relation
                       ->innerJoin(PhotoCoverMapper::getTableName(), [
                            PhotoCoverMapper::column('master_id') => self::getRawColumn('id')
                       ])
                       // Photo relation
                       ->innerJoin(PhotoMapper::getTableName(), [
                            PhotoMapper::column('id') => PhotoCoverMapper::getRawColumn('slave_id')
                       ])
                       // Room relation
                       ->innerJoin(RoomMapper::getTableName(), [
                            RoomMapper::column('hotel_id') => self::getRawColumn('id')
                       ])
                       // Room type relation
                       ->innerJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::column('room_type_id') => RoomMapper::getRawColumn('type_id')
                       ])
                       // Constraints
                       ->whereEquals(self::column('region_id'), $regionId)
                       ->andWhereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                       ->andWhereEquals(RoomTypePriceMapper::column('price_group_id'), $priceGroupId)
                       ->andWhereNotEquals(self::column('id'), $id)
                       ->groupBy([
                            self::column('id'),
                            self::column('rate'),
                            PhotoMapper::column('file'),
                            PhotoMapper::column('id'),
                            HotelTranslationMapper::column('name')
                       ])
                       ->limit($limit);

        return $db->queryAll();
    }

    /**
     * Create shared query
     * 
     * @param \Krystal\Db\Sql\Db
     * @param int $priceGroupId
     * @param int $langId
     * @param array $filter
     * @return void
     */
    private function appendSharedRelations($db, $langId, $priceGroupId, array $filters)
    {
        // Hotel translation relation
        $db->leftJoin(HotelTranslationMapper::getTableName(), [
                HotelTranslationMapper::column('id') => self::getRawColumn('id')
            ])
            // Room relation
            ->leftJoin(RoomMapper::getTableName(), [
                RoomMapper::column('hotel_id') => self::getRawColumn('id')
            ])
            // Room type relation
            ->leftJoin(RoomTypePriceMapper::getTableName(), [
                RoomTypePriceMapper::column('room_type_id') => RoomMapper::getRawColumn('type_id')
            ])
            // Price group relation
            ->leftJoin(PriceGroupMapper::getTableName(), [
                PriceGroupMapper::column('id') => RoomTypePriceMapper::getRawColumn('price_group_id')
            ])
            // Hotel type relation
            ->leftJoin(HotelTypeMapper::getTableName(), [
                HotelTypeMapper::column('id') => self::getRawColumn('type_id')
            ])
            // Hotel type translation relation
            ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                HotelTypeTranslationMapper::column('id') => HotelTypeMapper::getRawColumn('id'),
                HotelTypeTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // Region relation
            ->leftJoin(RegionMapper::getTableName(), [
                RegionMapper::column('id') => self::getRawColumn('region_id')
            ])
            // Region translation relation
            ->leftJoin(RegionTranslationMapper::getTableName(), [
                RegionTranslationMapper::column('id') => RegionMapper::getRawColumn('id'),
                RegionTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // District relation
            ->leftJoin(DistrictMapper::getTableName(), [
                DistrictMapper::column('id') => self::getRawColumn('district_id')
            ])
            // District translation
            ->leftJoin(DistrictTranslationMapper::getTableName(), [
                DistrictTranslationMapper::column('id') => DistrictMapper::getRawColumn('id'),
                DistrictTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // Review relation
            ->leftJoin(ReviewMapper::getTableName(), [
                ReviewMapper::column('hotel_id') => self::getRawColumn('id'),
                ReviewMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // Photo cover relation
            ->leftJoin(PhotoCoverMapper::getTableName(), [
                PhotoCoverMapper::column('master_id') => self::getRawColumn('id')
            ])
            // Photo relation
            ->leftJoin(PhotoMapper::getTableName(), [
                PhotoMapper::column('id') => PhotoCoverMapper::getRawColumn('slave_id')
            ])
            // Facility relation
            ->leftJoin(FacilityRelationMapper::getTableName(), [
                FacilityRelationMapper::column('master_id') => self::getRawColumn('id')
            ])
            // Room type relation
            ->leftJoin(RoomTypeMapper::getTableName(), [
                RoomTypeMapper::column('hotel_id') => self::getRawColumn('id'),
                    #RoomTypeMapper::column('id') => RoomMapper::getRawColumn('type_id')
            ])
            // Room type translation relation
            ->leftJoin(RoomTypeTranslationMapper::getTableName(), [
                RoomTypeTranslationMapper::column('id') => RoomTypeMapper::getRawColumn('id'),
                RoomTypeTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // Room category relation
            ->leftJoin(RoomCategoryMapper::getTableName(), [
                RoomCategoryMapper::column('id') => RoomTypeMapper::getRawColumn('category_id'),
            ])
            // Room category translation mapper
            ->leftJoin(RoomCategoryTranslationMapper::getTableName(), [
                RoomCategoryTranslationMapper::column('id') => RoomCategoryMapper::getRawColumn('id'),
                RoomCategoryTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
            ])
            // Constraints
            ->whereEquals(HotelTranslationMapper::column('lang_id'), new RawSqlFragment($langId))
            ->andWhereEquals(self::column('active'), new RawSqlFragment(1))
            ->andWhereEquals(self::column('closed'), new RawSqlFragment(0))
            ->andWhereEquals(RoomTypePriceMapper::column('price_group_id'), new RawSqlFragment($priceGroupId));

        // Adults count
        if (isset($filters['adults'])) {
            //$db->andWhereEquals(RoomTypeMapper::column('persons'), (int) $filters['adults']);
        }

        // Type filter
        if (isset($filters['type']) && is_array($filters['type'])) {
            $db->andWhereIn(self::column('type_id'), $filters['type']);
        }

        // Facility filter
        if (isset($filters['facility']) && is_array($filters['facility'])) {
            $db->andWhereIn(FacilityRelationMapper::column('slave_id'), $filters['facility']);
        }

        // Hotel region filter
        if (!empty($filters['region_id'])) {
            $db->andWhereEquals(self::column('region_id'), $filters['region_id']);
        }

        // Stars rate filter
        if (!empty($filters['stars'])) {
            $db->andWhereIn(self::column('rate'), $filters['stars']);
        }

        // If free cancellation is supported
        if (!empty($filters['cancellation']) && $filters['cancellation'] == true) {
            $db->andWhereEquals(self::column('penality_enabled'), (string) $filters['cancellation']);
        }

        // Hotel price filter
        if (isset($filters['price-start'], $filters['price-stop'])) {
            $db->andWhereBetween(RoomTypePriceMapper::column('price'), $filters['price-start'], $filters['price-stop']);
        }
    }

    /**
     * Invoke relation count based on criteria
     * 
     * @param int $langId
     * @param int $priceGroupId
     * @param array $filters
     * @return int
     */
    private function countRelation(int $langId, int $priceGroupId, array $filters) : int
    {
        $db = $this->db->select()
                       ->count(new RawSqlFragment('DISTINCT ' . self::column('id')))
                       ->from(self::getTableName());

        $this->appendSharedRelations($db, $langId, $priceGroupId, $filters);

        return (int) $db->queryScalar();
    }

    /**
     * Finds all hotels
     * 
     * @param int $langId
     * @param int $priceGroupId
     * @param array $filters Optional filters
     * @param bool|string $sort Optional sorting column
     * @param mixed $limit Optional limit
     * @return array
     */
    public function findAll(int $langId, int $priceGroupId, array $filters = [], $sort = false, $limit = null) : array
    {
        // Count rows. Purely for pagination purpose
        $count = $this->countRelation($langId, $priceGroupId, $filters);

        // Columns to be selected
        $columns = [
            self::column('id'),
            self::column('phone'),
            self::column('rate'),
            self::column('discount'),
            self::column('center_distance'),
            self::column('penality_enabled') => 'cancellation',
            self::column('lat'),
            self::column('lng'),
            self::column('card_required'),
            HotelTranslationMapper::column('name'),
            HotelTranslationMapper::column('address'),
            HotelTranslationMapper::column('description'),
            PriceGroupMapper::column('currency'),
            HotelTypeTranslationMapper::column('name') => 'type',
            RegionTranslationMapper::column('name') => 'region',
            DistrictTranslationMapper::column('name') => 'district',
            PhotoMapper::column('file') => 'cover',
            PhotoMapper::column('id') => 'cover_id'
        ];

        // Adults count
        if (isset($filters['adults'])) {
            //$columns[RoomCategoryTranslationMapper::column('name')] = 'room';
            //$columns[RoomTypeMapper::column('id')] = 'type_id';
        }

        $db = $this->db->select($columns, true)
                       ->min(RoomTypePriceMapper::column('price'), 'start_price')
                       ->count(new RawSqlFragment('DISTINCT ' . ReviewMapper::column('id')) , 'review_count')
                       ->from(self::getTableName());

        $this->appendSharedRelations($db, $langId, $priceGroupId, $filters);

        // A list of supported sortable columns
        $sortableColumns = ['discount', 'price', 'reviews', 'distance'];

        // Start applying sorting
        if ($sort !== false && in_array($sort, $sortableColumns)) {
            switch ($sort) {
                case 'discount':
                    $sort = [
                        self::column('discount') => 'DESC'
                    ];
                break;

                case 'price':
                    $sort = [
                        'start_price' => 'ASC',
                    ];
                break;

                case 'reviews':
                    $sort = [
                        'review_count' => 'DESC',
                    ];
                break;

                case 'distance':
                    $sort = [
                        self::column('center_distance') => 'DESC'
                    ];
                break;
            }

            // Sort by id DESC, in case the collision occurs
            $sort = array_merge($sort, [self::column('id') => 'DESC']);

        } else {
            // By default
            $sort = self::column('id');
        }

        // Apply grouping
        $db->groupBy($columns);

        // Apply sorting
        $db->orderBy($sort);

        // Apply limit if provided
        if (is_int($limit)) {
            $db->limit($limit);
        }

        // Apply pagination
        $db->paginateRaw($count, $filters['page'] ?? 1, $filters['per_page'] ?? 8);

        //d($db);
        
        return $db->queryAll();
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
            PriceGroupMapper::column('currency'),
            HotelTypeTranslationMapper::column('name') => 'type',
            RegionTranslationMapper::column('name') => 'region',
            DistrictTranslationMapper::column('name') => 'district',
            PhotoMapper::column('id') => 'cover_id',
            PhotoMapper::column('file') => 'cover'
        ]);

        $db = $this->db->select($columns)
                       ->min(RoomTypePriceMapper::column('price'), 'start_price')
                       ->from(self::getTableName())
                       // Hotel translation relation
                       ->leftJoin(HotelTranslationMapper::getTableName(), [
                            HotelTranslationMapper::column('id') => self::getRawColumn('id')
                       ])
                       // Room relation
                       ->leftJoin(RoomMapper::getTableName(), [
                            RoomMapper::column('hotel_id') => self::getRawColumn('id')
                       ])
                       // Room type relation
                       ->leftJoin(RoomTypePriceMapper::getTableName(), [
                            RoomTypePriceMapper::column('room_type_id') => RoomMapper::getRawColumn('type_id')
                       ])
                       // Hotel type relation
                       ->leftJoin(HotelTypeMapper::getTableName(), [
                            HotelTypeMapper::column('id') => self::getRawColumn('type_id')
                       ])
                       // Hotel type translation relation
                       ->leftJoin(HotelTypeTranslationMapper::getTableName(), [
                            HotelTypeTranslationMapper::column('id') => HotelTypeMapper::getRawColumn('id'),
                            HotelTypeTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Price group relation
                       ->leftJoin(PriceGroupMapper::getTableName(), [
                            PriceGroupMapper::column('id') => RoomTypePriceMapper::getRawColumn('price_group_id')
                       ])
                       // Region relation
                       ->leftJoin(RegionMapper::getTableName(), [
                            RegionMapper::column('id') => self::getRawColumn('region_id')
                       ])
                       // Region translation relation
                       ->leftJoin(RegionTranslationMapper::getTableName(), [
                            RegionTranslationMapper::column('id') => RegionMapper::getRawColumn('id'),
                            RegionTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // District relation
                       ->leftJoin(DistrictMapper::getTableName(), [
                            DistrictMapper::column('id') => self::getRawColumn('district_id')
                       ])
                       // District translation
                       ->leftJoin(DistrictTranslationMapper::getTableName(), [
                            DistrictTranslationMapper::column('id') => DistrictMapper::getRawColumn('id'),
                            DistrictTranslationMapper::column('lang_id') => HotelTranslationMapper::getRawColumn('lang_id')
                       ])
                       // Photo cover relation
                       ->leftJoin(PhotoCoverMapper::getTableName(), [
                            self::column('id') => PhotoCoverMapper::getRawColumn('master_id')
                       ])
                       // Photo relation
                       ->leftJoin(PhotoMapper::getTableName(), [
                            PhotoMapper::column('id') => PhotoCoverMapper::getRawColumn('slave_id')
                       ])
                       // Constraints
                       ->whereEquals(self::column(self::PARAM_COLUMN_ID), $id)
                       ->andWhereEquals(RoomTypePriceMapper::column('price_group_id'), (string) $priceGroupId, true);

        if ($langId == 0) {
            return $db->groupBy($columns)
                      ->queryAll();
        } else {
            $db->andWhereEquals(HotelTranslationMapper::column(self::PARAM_COLUMN_LANG_ID), $langId)
               ->groupBy($columns);

            return $db->query();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function filter($input, $page, $itemsPerPage, $sortingColumn, $desc, array $parameters = array())
    {
        // Columns to be selected
        $columns = array_merge($this->getColumns(), [
            UserMapper::column('id') => 'user_id'
        ]);

        $db = $this->createEntitySelect($columns)
                   // Junction relation
                   ->leftJoin(HotelUserRelationMapper::getTableName(), [
                        HotelUserRelationMapper::column('slave_id') => self::getRawColumn('id')
                    ])
                    // User relation
                    ->leftJoin(UserMapper::getTableName(), [
                        UserMapper::column('id') => HotelUserRelationMapper::getRawColumn('master_id')
                    ])
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
            PhotoMapper::column('id') => 'cover_id',
            PhotoMapper::column('file') => 'cover'
        ]);

        return $this->createEntitySelect($columns)
                    // Photo cover relation
                    ->leftJoin(PhotoCoverMapper::getTableName(), [
                        self::column('id') => PhotoCoverMapper::getRawColumn('master_id')
                    ])
                    // Photo relation
                    ->leftJoin(PhotoMapper::getTableName(), [
                        PhotoMapper::column('id') => PhotoCoverMapper::getRawColumn('slave_id')
                    ])
                    // Language ID filter
                    ->whereEquals(HotelTranslationMapper::column('lang_id'), $langId)
                    // Select only active ones
                    ->andWhereEquals(self::column('active'), '1')
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}