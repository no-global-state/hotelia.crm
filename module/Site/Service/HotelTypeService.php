<?php

namespace Site\Service;

use Site\Storage\MySQL\HotelTypeMapper;
use Krystal\Stdlib\ArrayUtils;

final class HotelTypeService
{
    /**
     * Hotel type mapper
     * 
     * @var \Site\Storage\MySQL\HotelTypeMapper
     */
    private $hotelTypeMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\HotelTypeMapper $hotelTypeMapper
     * @return void
     */
    public function __construct(HotelTypeMapper $hotelTypeMapper)
    {
        $this->hotelTypeMapper = $hotelTypeMapper;
    }

    /**
     * Delete hotel type service by its ID
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById(int $id)
    {
        return $this->hotelTypeMapper->deleteByPk($id);
    }

    /**
     * Fetch all hotel types with their corresponding hotel count
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAllWithCount(int $langId) : array
    {
        return $this->hotelTypeMapper->fetchAllWithCount($langId);
    }

    /**
     * Fetch hotel type services
     * 
     * @param int $langId
     * @return array
     */
    public function fetchList(int $langId) : array
    {
        return ArrayUtils::arrayList($this->fetchAll($langId), 'id', 'name');
    }

    /**
     * Fetch all hotel types
     * 
     * @param int $langId
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->hotelTypeMapper->fetchAll($langId);
    }

    /**
     * Fetch hotel type by its ID
     * 
     * @param int $id Hotel Type ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId)
    {
        return $this->hotelTypeMapper->fetchById($id, $langId);
    }

    /**
     * Save hotel type data
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->hotelTypeMapper->saveEntity($input['type'], $input['translation']);
    }
}
