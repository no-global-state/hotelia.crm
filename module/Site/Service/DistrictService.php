<?php

namespace Site\Service;

use Site\Storage\MySQL\DistrictMapper;
use Krystal\Stdlib\ArrayUtils;

final class DistrictService
{
    /**
     * Any compliant district mapper
     * 
     * @var \Site\Storage\MySQL\DistrictMapper
     */
    private $districtMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\DistrictMapper $districtMapper
     * @return void
     */
    public function __construct(DistrictMapper $districtMapper)
    {
        $this->districtMapper = $districtMapper;
    }

    /**
     * Fetch district by its ID
     * 
     * @param int $id District ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->districtMapper->fetchById($id, $langId);
    }

    /**
     * Fetch all districts
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->districtMapper->fetchAll($langId);
    }

    /**
     * Deletes a district by its associated ID
     * 
     * @param int $id District ID
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->districtMapper->deleteByPk($id);
    }

    /**
     * Saves a district
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->districtMapper->saveEntity($input['district'], $input['translation']);
    }
}
