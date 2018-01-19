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
            self::getFullColumnName('start_price'),
            self::getFullColumnName('rate'),
            self::getFullColumnName('discount'),
            self::getFullColumnName('website'),
            self::getFullColumnName('email'),
            self::getFullColumnName('active'),
            self::getFullColumnName('lat'),
            self::getFullColumnName('lng'),
            HotelTranslationMapper::getFullColumnName('lang_id'),
            HotelTranslationMapper::getFullColumnName('name'),
            HotelTranslationMapper::getFullColumnName('address'),
            HotelTranslationMapper::getFullColumnName('description')
        ];
    }

    /**
     * Fetch hotel by its ID
     * 
     * @param int $id Hotel ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->findEntity($this->getColumns(), $id, $langId);
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
        $columns = array_merge($this->getColumns(), [
            PhotoMapper::getFullColumnName('id') => 'cover_id',
            PhotoMapper::getFullColumnName('file') => 'cover'
        ]);

        return $this->createEntitySelect($columns)
                    // Photo cover relation
                    ->leftJoin(PhotoCoverMapper::getTableName())
                    ->on()
                    ->equals(
                        self::getFullColumnName('id'),
                        PhotoCoverMapper::getRawColumn('master_id')
                    )
                    // Photo relation
                    ->leftJoin(PhotoMapper::getTableName())
                    ->on()
                    ->equals(
                        PhotoMapper::getFullColumnName('id'),
                        PhotoCoverMapper::getRawColumn('slave_id')
                    )
                    // Language ID filter
                    ->whereEquals(HotelTranslationMapper::getFullColumnName('lang_id'), $langId)
                    // Select only active ones
                    ->andWhereEquals(self::getFullColumnName('active'), '1')
                    ->orderBy($this->getPk())
                    ->desc()
                    ->queryAll();
    }
}
