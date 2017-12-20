<?php

namespace Site\Service;

use Site\Storage\MySQL\RegionMapper;

final class RegionService
{
    /**
     * Any compliant region mapper
     * 
     * @var \Site\Storage\MySQL\RegionMapper
     */
    private $regionMapper;

    /**
     * State initialization
     * 
     * @param \Site\Storage\MySQL\RegionMapper $regionMapper
     * @return void
     */
    public function __construct(RegionMapper $regionMapper)
    {
        $this->regionMapper = $regionMapper;
    }

    /**
     * Deletes a region by its associated ID
     * 
     * @param int $id Region ID
     * @return boolean
     */
    public function deleteById(int $id) : bool
    {
        return $this->regionMapper->deleteByPk($id);
    }

    /**
     * Fetch all region
     * 
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchAll(int $langId) : array
    {
        return $this->regionMapper->fetchAll($langId);
    }

    /**
     * Fetch region by its ID
     * 
     * @param int $id Region ID
     * @param int $langId Language ID filter
     * @return array
     */
    public function fetchById(int $id, int $langId = 0) : array
    {
        return $this->regionMapper->fetchById($id, $langId);
    }

    /**
     * Saves a region
     * 
     * @param array $input
     * @return boolean
     */
    public function save(array $input)
    {
        return $this->regionMapper->saveEntity($input['region'], $input['translation']);
    }
}
